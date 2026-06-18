<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Appraisal\Appraisal;
use App\Models\Appraisal\AppraisalAspect;
use App\Models\Appraisal\AppraisalItem;
use App\Models\Appraisal\AppraisalPeriod;
use App\Models\Appraisal\AppraisalTemplate;
use App\Models\Employee;
use App\Services\Appraisal\ApprovalStateMachine;
use App\Services\Appraisal\ScoreEngine;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class AppraisalController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function index(Request $request)
    {
        $user   = auth()->user();
        $periods = AppraisalPeriod::orderByDesc('year')->orderByDesc('id')->get();
        $selectedPeriod = $request->get('period_id');

        $query = Appraisal::with(['employee', 'period', 'template'])
            ->when($selectedPeriod, fn($q) => $q->where('appraisal_period_id', $selectedPeriod));

        if ($user->hasRole('admin')) {
            // Admin HRD: lihat semua
        } elseif ($user->hasRole('karyawan')) {
            // Karyawan: hanya lihat penilaian milik diri sendiri (via employee link)
            $query->whereHas('employee', fn($q) => $q->where('user_id', $user->id));
        } elseif ($user->hasAnyRole(['user_ii', 'cfo', 'ceo'])) {
            // Approver: filter by department jika user punya department
            if ($user->department) {
                $query->whereHas('employee', fn($q) => $q->where('department', $user->department));
            }
        } else {
            // Evaluator: lihat yang dibuat sendiri + seluruh appraisal dept yang sama
            $query->where(function ($q) use ($user) {
                $q->where('evaluator_id', $user->id);
                if ($user->department) {
                    $q->orWhereHas('employee', fn($q2) => $q2->where('department', $user->department));
                }
            });
        }

        $appraisals = $query->orderByDesc('id')->get();

        return view('appraisal.appraisal.index', compact('appraisals', 'periods', 'selectedPeriod'));
    }

    public function create()
    {
        $this->authorizeEvaluator();

        $user = auth()->user();

        if ($user->hasRole('karyawan')) {
            $myEmployee = $user->employee;
            if (! $myEmployee) {
                return redirect()->route('appraisal.appraisals.index')
                    ->with('error', 'Akun Anda belum dihubungkan ke data karyawan. Hubungi Admin HRD.');
            }
            $employees = collect([$myEmployee->load('level.appraisalTemplates')]);
        } else {
            $employees = Employee::with('level.appraisalTemplates')
                ->where('is_active', true)
                ->whereHas('level.appraisalTemplates')
                ->orderBy('name')
                ->get();
        }

        $periods   = AppraisalPeriod::where('status', 'open')->orderByDesc('year')->get();
        $templates = AppraisalTemplate::with('level')->orderBy('name')->get();

        return view('appraisal.appraisal.create', compact('employees', 'periods', 'templates'));
    }

    public function store(Request $request)
    {
        $this->authorizeEvaluator();

        $user = auth()->user();

        // Karyawan hanya boleh buat penilaian untuk dirinya sendiri
        if ($user->hasRole('karyawan')) {
            $myEmployee = $user->employee;
            if (! $myEmployee) {
                return redirect()->route('appraisal.appraisals.index')
                    ->with('error', 'Akun Anda belum dihubungkan ke data karyawan.');
            }
            $request->merge(['employee_id' => $myEmployee->id]);
        }

        $request->validate([
            'employee_id'           => 'required|exists:employees,id',
            'appraisal_period_id'   => 'required|exists:appraisal_periods,id',
            'appraisal_template_id' => 'required|exists:appraisal_templates,id',
        ]);

        $exists = Appraisal::where('employee_id', $request->employee_id)
            ->where('appraisal_period_id', $request->appraisal_period_id)
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['employee_id' => 'Penilaian untuk karyawan ini pada periode tersebut sudah ada.'])
                ->withInput();
        }

        $appraisal = DB::transaction(function () use ($request, $user) {
            $template  = AppraisalTemplate::findOrFail($request->appraisal_template_id);
            // Karyawan bukan evaluator — evaluator_id diisi nanti saat evaluator submit
            $evaluatorId = $user->hasRole('karyawan') ? null : $user->id;
            $appraisal = Appraisal::create([
                'employee_id'           => $request->employee_id,
                'appraisal_period_id'   => $request->appraisal_period_id,
                'appraisal_template_id' => $request->appraisal_template_id,
                'evaluator_id'          => $evaluatorId,
                'status'                => Appraisal::STATUS_DRAFT,
            ]);

            $aspects = AppraisalAspect::where('appraisal_template_id', $request->appraisal_template_id)
                ->orderBy('order')->get();

            if ($template->isWeightedScale()) {
                foreach (['self', 'atasan1', 'atasan2', 'ho'] as $evalType) {
                    foreach ($aspects as $aspect) {
                        AppraisalItem::create([
                            'appraisal_id'        => $appraisal->id,
                            'appraisal_aspect_id' => $aspect->id,
                            'evaluator_type'      => $evalType,
                            'rating'              => null,
                            'score'               => 0,
                        ]);
                    }
                }
            } else {
                foreach ($aspects as $aspect) {
                    AppraisalItem::create([
                        'appraisal_id'        => $appraisal->id,
                        'appraisal_aspect_id' => $aspect->id,
                        'evaluator_type'      => 'evaluator',
                        'rating'              => null,
                        'score'               => 0,
                    ]);
                }
            }

            return $appraisal;
        });

        return redirect()->route('appraisal.appraisals.edit', $appraisal)
            ->with('status', 'Penilaian berhasil dibuat. Silakan isi rating untuk setiap aspek.');
    }

    public function edit(Appraisal $appraisal)
    {
        $this->authorizeOwnerOrAdmin($appraisal);

        if (! $appraisal->isDraft()) {
            return redirect()->route('appraisal.appraisals.show', $appraisal)
                ->with('error', 'Penilaian ini tidak dapat diedit karena sudah disubmit.');
        }

        $appraisal->load([
            'employee.level', 'period',
            'template.aspects.weights', 'template.gradeBands', 'items',
        ]);

        $isKaryawan = auth()->user()->hasRole('karyawan');

        if ($appraisal->template->isWeightedScale()) {
            $itemsByEvaluator = $appraisal->items
                ->groupBy('evaluator_type')
                ->map(fn($g) => $g->keyBy('appraisal_aspect_id'));
            return view('appraisal.appraisal.edit', compact('appraisal', 'itemsByEvaluator', 'isKaryawan'));
        }

        $itemsByAspect = $appraisal->items->keyBy('appraisal_aspect_id');
        return view('appraisal.appraisal.edit', compact('appraisal', 'itemsByAspect', 'isKaryawan'));
    }

    public function update(Request $request, Appraisal $appraisal)
    {
        $this->authorizeOwnerOrAdmin($appraisal);

        if (! $appraisal->isDraft()) {
            return redirect()->route('appraisal.appraisals.show', $appraisal)
                ->with('error', 'Penilaian ini tidak dapat diedit.');
        }

        $appraisal->load('template');
        $isScale = $appraisal->template->isWeightedScale();

        if ($isScale) {
            $request->validate([
                'ratings'                      => 'nullable|array',
                'ratings.*.*'                  => 'nullable|integer|between:1,5',
                'notes'                        => 'nullable|string|max:2000',
                'strength_points'              => 'nullable|string|max:2000',
                'development_need'             => 'nullable|string|max:2000',
                'individual_development_plan'  => 'nullable|string|max:2000',
            ]);
        } else {
            $request->validate([
                'ratings'             => 'nullable|array',
                'ratings.*'           => 'nullable|in:BS,B,C,K',
                'avg_late_per_month'  => 'nullable|numeric|min:0|max:31',
                'avg_leave_per_month' => 'nullable|numeric|min:0|max:31',
                'warning_letter'      => 'nullable|boolean',
                'sp_level'            => 'nullable|in:none,sp1,sp2,sp3',
                'notes'               => 'nullable|string|max:2000',
            ]);
        }

        DB::transaction(function () use ($request, $appraisal, $isScale) {
            if ($isScale) {
                $allowedTypes = auth()->user()->hasRole('karyawan')
                    ? ['self']
                    : ['self', 'atasan1', 'atasan2', 'ho'];
                foreach ($allowedTypes as $evalType) {
                    foreach ($request->input("ratings.{$evalType}", []) as $aspectId => $rating) {
                        AppraisalItem::where('appraisal_id', $appraisal->id)
                            ->where('appraisal_aspect_id', $aspectId)
                            ->where('evaluator_type', $evalType)
                            ->update(['rating' => $rating ?: null]);
                    }
                }
                $appraisal->update([
                    'notes'                       => $request->input('notes'),
                    'strength_points'             => $request->input('strength_points'),
                    'development_need'            => $request->input('development_need'),
                    'individual_development_plan' => $request->input('individual_development_plan'),
                ]);
            } else {
                foreach ($request->input('ratings', []) as $aspectId => $rating) {
                    AppraisalItem::where('appraisal_id', $appraisal->id)
                        ->where('appraisal_aspect_id', $aspectId)
                        ->update(['rating' => $rating ?: null]);
                }
                $appraisal->update([
                    'avg_late_per_month'  => $request->input('avg_late_per_month', 0),
                    'avg_leave_per_month' => $request->input('avg_leave_per_month', 0),
                    'warning_letter'      => (bool) $request->input('warning_letter', false),
                    'sp_level'            => $request->input('sp_level', 'none'),
                    'notes'               => $request->input('notes'),
                ]);
            }

            ScoreEngine::calculate($appraisal);
        });

        return redirect()->route('appraisal.appraisals.edit', $appraisal)
            ->with('status', 'Draft penilaian berhasil disimpan.');
    }

    public function show(Appraisal $appraisal)
    {
        $appraisal->load([
            'employee.level', 'period',
            'template.aspects.weights', 'template.gradeBands',
            'items.aspect', 'evaluator', 'approvals.user',
        ]);

        $itemsByAspect    = $appraisal->items->keyBy('appraisal_aspect_id');
        $itemsByEvaluator = $appraisal->items
            ->groupBy('evaluator_type')
            ->map(fn($g) => $g->keyBy('appraisal_aspect_id'));

        $sm         = new ApprovalStateMachine();
        $authUser   = auth()->user();
        $canSubmit  = $sm->canSubmit($appraisal, $authUser);
        $canApprove = $sm->canApprove($appraisal, $authUser);
        $canReject  = $sm->canReject($appraisal, $authUser);
        $nextLabel  = $sm->nextApproverLabel($appraisal);
        $isFinalStep = $appraisal->status === Appraisal::STATUS_APPROVED_U2 && $canApprove;

        return view('appraisal.appraisal.show', compact(
            'appraisal', 'itemsByAspect', 'itemsByEvaluator',
            'canSubmit', 'canApprove', 'canReject', 'nextLabel', 'isFinalStep'
        ));
    }

    public function pdf(Appraisal $appraisal)
    {
        $appraisal->load([
            'employee.level', 'period',
            'template.aspects.weights', 'template.gradeBands',
            'items.aspect.weights', 'evaluator', 'approvals.user',
        ]);

        $itemsByAspect    = $appraisal->items->keyBy('appraisal_aspect_id');
        $itemsByEvaluator = $appraisal->items
            ->groupBy('evaluator_type')
            ->map(fn($g) => $g->keyBy('appraisal_aspect_id'));

        $pdf = Pdf::loadView('appraisal.pdf.form', compact('appraisal', 'itemsByAspect', 'itemsByEvaluator'))
            ->setPaper('a4', 'portrait');

        $filename = 'Penilaian_' . str_replace(' ', '_', $appraisal->employee->name)
            . '_' . $appraisal->period->year . '.pdf';

        return $pdf->download($filename);
    }

    public function destroy(Appraisal $appraisal)
    {
        $this->authorizeOwnerOrAdmin($appraisal);

        if (! $appraisal->isDraft()) {
            return back()->with('error', 'Hanya penilaian berstatus draft yang dapat dihapus.');
        }

        $appraisal->delete();

        return redirect()->route('appraisal.appraisals.index')
            ->with('status', 'Penilaian berhasil dihapus.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function authorizeEvaluator(): void
    {
        $user = auth()->user();
        if (! $user->hasAnyRole(['admin', 'evaluator', 'karyawan'])) {
            abort(403, 'Hanya evaluator, admin, atau karyawan yang dapat membuat penilaian.');
        }
    }

    private function authorizeOwnerOrAdmin(Appraisal $appraisal): void
    {
        $user = auth()->user();
        if ($user->hasRole('admin')) return;
        if ($appraisal->evaluator_id === $user->id) return;
        // Karyawan boleh akses appraisal milik dirinya sendiri
        if ($user->hasRole('karyawan') && $appraisal->employee->user_id === $user->id) return;
        // Evaluator boleh akses semua appraisal di departemen yang sama
        if ($user->hasRole('evaluator') && $user->department
            && $appraisal->employee->department === $user->department) return;
        abort(403, 'Anda tidak memiliki akses ke penilaian ini.');
    }

    public function isKaryawan(): bool
    {
        return auth()->user()->hasRole('karyawan');
    }
}
