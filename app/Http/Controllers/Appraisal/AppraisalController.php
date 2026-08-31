<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Appraisal\Appraisal;
use App\Models\Appraisal\AppraisalObjective;
use App\Models\Appraisal\AppraisalPeriod;
use App\Models\Appraisal\AppraisalTemplate;
use App\Models\Employee;
use App\Services\ApprovalEngine;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

/**
 * Penilaian Kinerja — KPI/objective-based, approval lewat App\Services\ApprovalEngine
 * (bukan lagi state machine 2-step). Pola sama HR\LeaveController: store()/submit()
 * panggil $engine->start(), approve/reject lewat Kotak Persetujuan terpadu
 * (App\Http\Controllers\Approval\ApprovalInboxController) — tidak ada approve/reject
 * di sini lagi.
 */
class AppraisalController extends Controller implements HasMiddleware
{
    public function __construct(private ApprovalEngine $engine) {}

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
            ->when($selectedPeriod, fn ($q) => $q->where('appraisal_period_id', $selectedPeriod));

        if ($user->hasRole('admin')) {
            // Admin HRD: lihat semua
        } elseif ($user->hasRole('karyawan')) {
            $query->whereHas('employee', fn ($q) => $q->where('user_id', $user->id));
        } else {
            // Evaluator: lihat yang dibuat sendiri
            $query->where('evaluator_id', $user->id);
        }

        if ($search = $request->get('search')) {
            $query->whereHas('employee', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $appraisals = $query->orderByDesc('id')->paginate(25)->withQueryString();

        return view('appraisal.appraisal.index', compact('appraisals', 'periods', 'selectedPeriod', 'search'));
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
            $employees = collect([$myEmployee]);
        } else {
            $employees = Employee::where('is_active', true)->orderBy('name')->get();
        }

        $periods   = AppraisalPeriod::where('status', 'open')->orderByDesc('year')->get();
        $templates = AppraisalTemplate::with('level')->orderBy('name')->get();

        return view('appraisal.appraisal.create', compact('employees', 'periods', 'templates'));
    }

    public function store(Request $request)
    {
        $this->authorizeEvaluator();

        $user = auth()->user();

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
            'appraisal_template_id' => 'nullable|exists:appraisal_templates,id',
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
            $employee = Employee::findOrFail($request->employee_id);

            // Evaluator default: atasan langsung dari org chart. Kalau yang mengajukan
            // bukan karyawan yang dinilai sendiri, dia jadi evaluator (mis. admin buat
            // atas nama evaluator, atau evaluator langsung isi).
            $evaluatorId = $user->hasRole('karyawan')
                ? $employee->manager?->user_id
                : $user->id;

            $appraisal = Appraisal::create([
                'employee_id'           => $request->employee_id,
                'appraisal_period_id'   => $request->appraisal_period_id,
                'appraisal_template_id' => $request->appraisal_template_id ?: null,
                'evaluator_id'          => $evaluatorId,
                'status'                => 'draft',
            ]);

            if ($request->filled('appraisal_template_id')) {
                $template = AppraisalTemplate::with('objectives')->find($request->appraisal_template_id);
                foreach ($template?->objectives ?? [] as $obj) {
                    AppraisalObjective::create([
                        'appraisal_id' => $appraisal->id,
                        'title'        => $obj->title,
                        'description'  => $obj->description,
                        'category'     => $obj->category,
                        'weight_pct'   => $obj->weight_pct,
                        'order'        => $obj->order,
                    ]);
                }
            }

            return $appraisal;
        });

        return redirect()->route('appraisal.appraisals.edit', $appraisal)
            ->with('status', 'Penilaian berhasil dibuat. Silakan isi KPI/objective.');
    }

    public function edit(Appraisal $appraisal)
    {
        $this->authorizeOwnerOrAdmin($appraisal);

        if (! $appraisal->isDraft()) {
            return redirect()->route('appraisal.appraisals.show', $appraisal)
                ->with('error', 'Penilaian ini tidak dapat diedit karena sudah disubmit.');
        }

        $appraisal->load(['employee.level', 'employee.position', 'employee.department', 'period', 'template.gradeBands', 'objectives']);

        return view('appraisal.appraisal.edit', compact('appraisal'));
    }

    public function update(Request $request, Appraisal $appraisal)
    {
        $this->authorizeOwnerOrAdmin($appraisal);

        if (! $appraisal->isDraft()) {
            return redirect()->route('appraisal.appraisals.show', $appraisal)
                ->with('error', 'Penilaian ini tidak dapat diedit.');
        }

        $request->validate([
            'objectives'                    => 'nullable|array',
            'objectives.*.id'               => 'nullable|integer',
            'objectives.*.title'            => 'required_with:objectives.*.weight_pct|nullable|string|max:200',
            'objectives.*.description'      => 'nullable|string|max:1000',
            'objectives.*.category'         => 'nullable|string|max:100',
            'objectives.*.weight_pct'       => 'nullable|integer|min:0|max:100',
            'objectives.*.target'           => 'nullable|string|max:1000',
            'objectives.*.actual'           => 'nullable|string|max:1000',
            'objectives.*.achievement_pct'  => 'nullable|numeric|min:0|max:999',
            'strengths'                     => 'nullable|string|max:2000',
            'development_notes'             => 'nullable|string|max:2000',
            'notes'                         => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($request, $appraisal) {
            $submitted = $request->input('objectives', []);
            $keptIds   = [];

            foreach ($submitted as $order => $row) {
                if (empty(trim($row['title'] ?? ''))) {
                    continue;
                }

                $data = [
                    'appraisal_id'     => $appraisal->id,
                    'title'            => $row['title'],
                    'description'      => $row['description'] ?? null,
                    'category'         => $row['category'] ?? null,
                    'weight_pct'       => (int) ($row['weight_pct'] ?? 0),
                    'target'           => $row['target'] ?? null,
                    'actual'           => $row['actual'] ?? null,
                    'achievement_pct'  => $row['achievement_pct'] !== '' && isset($row['achievement_pct']) ? (float) $row['achievement_pct'] : null,
                    'order'            => $order + 1,
                ];

                $objective = ! empty($row['id'])
                    ? AppraisalObjective::where('appraisal_id', $appraisal->id)->find($row['id'])
                    : null;

                if ($objective) {
                    $objective->fill($data);
                } else {
                    $objective = new AppraisalObjective($data);
                }

                $objective->recalculateScore();
                $objective->save();
                $keptIds[] = $objective->id;
            }

            $appraisal->objectives()->whereNotIn('id', $keptIds)->delete();

            $appraisal->load('objectives');
            $totalScore = (float) $appraisal->objectives->sum('score');

            // Grade bands ikut template. Kalau appraisal tidak pakai template
            // (isi KPI dari nol), fallback ke template default supaya grade tetap
            // bisa dihitung — bukan cuma appraisal yang lewat pilih template yang
            // dapat predikat.
            $gradeSource = $appraisal->template ?? AppraisalTemplate::where('is_default', true)->first();
            $grade = $gradeSource?->gradeBands->first(fn ($b) => $totalScore >= $b->min_score)?->grade_label;

            $appraisal->update([
                'total_score'       => $totalScore,
                'grade'             => $grade,
                'strengths'         => $request->input('strengths'),
                'development_notes' => $request->input('development_notes'),
                'notes'             => $request->input('notes'),
            ]);
        });

        return redirect()->route('appraisal.appraisals.edit', $appraisal)
            ->with('status', 'Draft penilaian berhasil disimpan.');
    }

    public function submit(Appraisal $appraisal)
    {
        $this->authorizeOwnerOrAdmin($appraisal);
        abort_unless($appraisal->isDraft(), 422);

        $appraisal->load('objectives');
        if (! $appraisal->isReadyToSubmit()) {
            return back()->with('error', 'Total bobot KPI harus 100% dan semua KPI harus diisi capaian sebelum disubmit.');
        }

        $appraisal->update(['status' => 'pending', 'submitted_at' => now()]);
        $this->engine->start($appraisal->load('employee'));
        $appraisal->refresh();
        if ($appraisal->approvalRequest?->status === 'approved' && ! $appraisal->isApproved()) {
            $appraisal->forceFill(['status' => 'approved', 'finalized_at' => now()])->save();
        }

        return redirect()->route('appraisal.appraisals.show', $appraisal)
            ->with('status', 'Penilaian berhasil disubmit dan menunggu persetujuan.');
    }

    public function show(Appraisal $appraisal)
    {
        $appraisal->load([
            'employee.level', 'employee.position', 'employee.department', 'period', 'template',
            'objectives', 'evaluator',
            'approvalRequest.steps.approver', 'approvalRequest.steps.actedBy',
        ]);

        return view('appraisal.appraisal.show', compact('appraisal'));
    }

    public function pdf(Appraisal $appraisal)
    {
        $appraisal->load([
            'employee.level', 'employee.position', 'employee.department', 'period', 'template',
            'objectives', 'evaluator',
            'approvalRequest.steps.approver',
        ]);

        $pdf = Pdf::loadView('appraisal.pdf.form', compact('appraisal'))->setPaper('a4', 'portrait');

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
        if ($user->hasRole('karyawan') && $appraisal->employee->user_id === $user->id) return;
        abort(403, 'Anda tidak memiliki akses ke penilaian ini.');
    }
}
