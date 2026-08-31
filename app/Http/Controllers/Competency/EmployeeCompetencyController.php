<?php

namespace App\Http\Controllers\Competency;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Competency\Competency;
use App\Models\Competency\EmployeeCompetency;
use App\Models\Department;
use App\Models\Employee;
use App\Models\TrainingProgram;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

/** Penilaian kompetensi karyawan + analisis gap (wajib vs aktual). */
class EmployeeCompetencyController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    // ── Daftar penilaian ───────────────────────────────────────────────────

    public function index(Request $request)
    {
        $employeeId   = $request->integer('employee_id') ?: null;
        $competencyId = $request->integer('competency_id') ?: null;
        $departmentId = $request->integer('department_id') ?: null;

        $assessments = EmployeeCompetency::with(['employee.department', 'competency', 'assessor'])
            ->when($employeeId, fn ($q, $v) => $q->where('employee_id', $v))
            ->when($competencyId, fn ($q, $v) => $q->where('competency_id', $v))
            ->when($departmentId, fn ($q, $v) => $q->whereHas('employee', fn ($e) => $e->where('department_id', $v)))
            ->latest('assessed_on')
            ->latest('id')
            ->get();

        $employees    = Employee::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $competencies = Competency::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $departments  = Department::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('competency.assessments.index', compact(
            'assessments', 'employees', 'competencies', 'departments', 'employeeId', 'competencyId', 'departmentId'
        ));
    }

    // ── Nilai 1 karyawan ───────────────────────────────────────────────────

    public function employee(Employee $employee)
    {
        $employee->load(['position.competencyRequirements.competency', 'department', 'competencies.competency']);

        $required = $employee->position
            ? $employee->position->competencyRequirements->keyBy('competency_id')
            : collect();

        $actual = $employee->competencies->keyBy('competency_id');

        // Baris = gabungan kompetensi wajib + kompetensi yang sudah dinilai.
        $rowIds = $required->keys()->merge($actual->keys())->unique();
        $rows = Competency::whereIn('id', $rowIds)
            ->orderBy('category')->orderBy('name')
            ->get()
            ->map(fn ($c) => [
                'competency'     => $c,
                'required_level' => $required->get($c->id)?->required_level,
                'actual_level'   => $actual->get($c->id)?->actual_level,
                'assessed_on'    => $actual->get($c->id)?->assessed_on,
            ]);

        // Kompetensi lain (belum wajib & belum dinilai) untuk dropdown "tambah".
        $others = Competency::where('is_active', true)
            ->whereNotIn('id', $rowIds)
            ->orderBy('category')->orderBy('name')
            ->get();

        return view('competency.assessments.employee', compact('employee', 'rows', 'others'));
    }

    public function upsert(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'levels'        => 'nullable|array',
            'levels.*'      => 'nullable|integer|min:1|max:5',
            'notes'         => 'nullable|array',
            'notes.*'       => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data, $employee) {
            foreach ($data['levels'] ?? [] as $competencyId => $level) {
                $competencyId = (int) $competencyId;
                $note = $data['notes'][$competencyId] ?? null;

                if (empty($level)) {
                    // Kosong = hapus penilaian kompetensi ini (kalau ada).
                    $employee->competencies()->where('competency_id', $competencyId)->delete();
                    continue;
                }

                $employee->competencies()->updateOrCreate(
                    ['competency_id' => $competencyId],
                    [
                        'actual_level'     => (int) $level,
                        'assessed_on'      => now()->toDateString(),
                        'assessor_user_id' => auth()->id(),
                        'notes'            => $note,
                    ]
                );
            }
        });

        return redirect()->route('competency.assessments.employee', $employee)
            ->with('status', 'Penilaian kompetensi karyawan disimpan.');
    }

    // ── Analisis Gap ───────────────────────────────────────────────────────

    public function gap(Request $request)
    {
        [$companies, $companyId, $departmentId, $positionId, $data] = $this->buildGap($request);
        $departments = Department::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('competency.gap.index', compact(
            'companies', 'departments', 'companyId', 'departmentId', 'positionId', 'data'
        ));
    }

    public function gapPdf(Request $request)
    {
        [$companies, $companyId, $departmentId, $positionId, $data] = $this->buildGap($request);
        $company = $companies->firstWhere('id', $companyId);

        $pdf = Pdf::loadView('competency.gap.pdf', compact('company', 'data'))->setPaper('a4', 'portrait');

        return $pdf->download('analisis-gap-kompetensi-' . now()->format('Ymd') . '.pdf');
    }

    /**
     * @return array{0:\Illuminate\Support\Collection,1:?int,2:?int,3:?int,4:array}
     */
    private function buildGap(Request $request): array
    {
        $companies    = Company::where('is_active', true)->orderBy('name')->get();
        $companyId    = $request->integer('company_id') ?: $companies->first()?->id;
        $departmentId = $request->integer('department_id') ?: null;
        $positionId   = $request->integer('position_id') ?: null;

        $employees = Employee::with(['department', 'position.competencyRequirements.competency', 'competencies'])
            ->where('is_active', true)
            ->when($companyId, fn ($q, $v) => $q->where('company_id', $v))
            ->when($departmentId, fn ($q, $v) => $q->where('department_id', $v))
            ->when($positionId, fn ($q, $v) => $q->where('position_id', $v))
            ->whereHas('position.competencyRequirements')
            ->orderBy('name')
            ->get();

        // Rekomendasi training: program aktif, dikelompokkan per kategori kompetensi.
        $trainingByCategory = TrainingProgram::where('is_active', true)
            ->whereNotNull('category')
            ->get()
            ->groupBy(fn ($p) => mb_strtolower(trim($p->category)))
            ->map(fn ($g) => $g->pluck('title')->all());

        $rows = [];
        $gapCount = 0;
        $employeesWithGap = 0;
        $perCompetency = [];

        foreach ($employees as $emp) {
            $actual = $emp->competencies->keyBy('competency_id');
            $empHasGap = false;

            foreach ($emp->position->competencyRequirements as $req) {
                $comp    = $req->competency;
                if (! $comp) {
                    continue;
                }
                $actLvl  = $actual->get($comp->id)?->actual_level ?? 0;
                $gap     = max(0, $req->required_level - $actLvl);

                if ($gap > 0) {
                    $gapCount++;
                    $empHasGap = true;
                    $perCompetency[$comp->name] = ($perCompetency[$comp->name] ?? 0) + 1;
                }

                $rows[] = [
                    'employee'   => $emp->name,
                    'department' => $emp->department?->name ?? '-',
                    'position'   => $emp->position?->name ?? '-',
                    'competency' => $comp->name,
                    'category'   => $comp->category,
                    'required'   => $req->required_level,
                    'actual'     => $actLvl ?: null,
                    'gap'        => $gap,
                    'training'   => $gap > 0 ? ($trainingByCategory[mb_strtolower(trim((string) $comp->category))] ?? []) : [],
                ];
            }

            if ($empHasGap) {
                $employeesWithGap++;
            }
        }

        arsort($perCompetency);

        $data = [
            'rows'              => $rows,
            'gap_count'         => $gapCount,
            'employees_total'   => $employees->count(),
            'employees_w_gap'   => $employeesWithGap,
            'per_competency'    => $perCompetency,
            'level_labels'      => Competency::$levelLabels,
        ];

        return [$companies, $companyId, $departmentId, $positionId, $data];
    }
}
