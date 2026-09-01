<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\HR\BonusPayment;
use App\Models\HR\PayrollSlip;
use App\Models\HR\SalaryBenchmark;
use App\Models\HR\SalaryGrade;
use App\Models\HR\ThrPayment;
use App\Models\Level;
use App\Models\TrainingParticipant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

/**
 * Compensation: (1) benchmark gaji pasar per Level (entitas global lintas company) +
 * perbandingan gaji aktual karyawan thd benchmark, (2) Total Rewards Statement per
 * karyawan — dihitung dari data payroll/THR/bonus REAL yang sudah closed, bukan proyeksi.
 */
class CompensationController extends Controller
{
    public function benchmarks()
    {
        $levels = Level::with('benchmark')->orderBy('rank')->get();

        return view('hr.compensation.benchmarks', compact('levels'));
    }

    public function updateBenchmark(Request $request, Level $level)
    {
        $data = $request->validate([
            'market_min' => 'required|integer|min:0',
            'market_mid' => 'required|integer|min:0',
            'market_max' => 'required|integer|min:0',
            'source'     => 'nullable|string|max:150',
            'notes'      => 'nullable|string',
        ]);
        abort_unless($data['market_min'] <= $data['market_mid'] && $data['market_mid'] <= $data['market_max'], 422,
            'Urutan harus Min ≤ Tengah ≤ Maks.');

        SalaryBenchmark::updateOrCreate(
            ['level_id' => $level->id],
            $data + ['updated_by_user_id' => auth()->id()]
        );

        return back()->with('success', 'Benchmark gaji ' . $level->name . ' disimpan.');
    }

    public function comparison(Request $request)
    {
        $companyId = $request->filled('company_id') ? (int) $request->company_id : null;
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        $employees = Employee::where('is_active', true)
            ->when($companyId, fn ($q, $v) => $q->where('company_id', $v))
            ->with(['level', 'department', 'position', 'company'])
            ->get();

        // Gaji aktual per karyawan = gross_salary dari slip payroll CLOSED terakhir (data riil,
        // bukan hitung ulang komponen — menghindari duplikasi logika kalkulasi payroll).
        $latestSlips = PayrollSlip::whereIn('employee_id', $employees->pluck('id'))
            ->whereHas('period', fn ($q) => $q->where('status', 'closed'))
            ->with('period')
            ->get()
            ->groupBy('employee_id')
            ->map(fn ($slips) => $slips->sortByDesc(fn ($s) => [$s->period->year, $s->period->month])->first());

        $benchmarks = SalaryBenchmark::all()->keyBy('level_id');

        $rows = $employees->map(function ($e) use ($latestSlips, $benchmarks) {
            $slip = $latestSlips->get($e->id);
            $benchmark = $e->level_id ? $benchmarks->get($e->level_id) : null;
            $current = $slip?->gross_salary;
            $compa = ($current !== null && $benchmark) ? $benchmark->compaRatio((int) $current) : null;

            return [
                'employee'   => $e,
                'current'    => $current,
                'benchmark'  => $benchmark,
                'compa'      => $compa,
                'band'       => $this->band($compa),
            ];
        });

        $byLevel = $rows->groupBy(fn ($r) => $r['employee']->level?->name ?? 'Tanpa Level')
            ->map(function ($group) {
                $withData = $group->filter(fn ($r) => $r['current'] !== null);

                return [
                    'count'      => $group->count(),
                    'with_data'  => $withData->count(),
                    'avg_current' => $withData->isNotEmpty() ? round($withData->avg('current')) : null,
                    'benchmark'  => $group->first()['benchmark'],
                    'avg_compa'  => $withData->filter(fn ($r) => $r['compa'] !== null)->isNotEmpty()
                        ? round($withData->filter(fn ($r) => $r['compa'] !== null)->avg('compa'), 1)
                        : null,
                ];
            });

        $belowMarket = $rows->filter(fn ($r) => $r['compa'] !== null && $r['compa'] < 90)
            ->sortBy('compa');

        return view('hr.compensation.comparison', compact('rows', 'byLevel', 'belowMarket', 'companies', 'companyId'));
    }

    private function band(?float $compa): ?string
    {
        if ($compa === null) return null;
        if ($compa < 90) return 'below';
        if ($compa > 110) return 'above';
        return 'within';
    }

    // ── Struktur Gaji Internal (Salary Grade) — beda dari benchmark pasar ──────

    public function grades(Request $request)
    {
        $companyId = $request->filled('company_id') ? (int) $request->company_id : null;
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        $levels = Level::orderBy('rank')->get();
        $existing = SalaryGrade::where('company_id', $companyId)->get()->keyBy('level_id');

        return view('hr.compensation.grades', compact('levels', 'existing', 'companies', 'companyId'));
    }

    public function updateGrade(Request $request, Level $level)
    {
        $companyId = $request->filled('company_id') ? (int) $request->company_id : null;

        $data = $request->validate([
            'grade_min' => 'required|integer|min:0',
            'grade_mid' => 'required|integer|min:0',
            'grade_max' => 'required|integer|min:0',
            'notes'     => 'nullable|string',
        ]);
        abort_unless($data['grade_min'] <= $data['grade_mid'] && $data['grade_mid'] <= $data['grade_max'], 422,
            'Urutan harus Min ≤ Tengah ≤ Maks.');

        SalaryGrade::updateOrCreate(
            ['company_id' => $companyId, 'level_id' => $level->id],
            $data + ['updated_by_user_id' => auth()->id()]
        );

        return back()->with('success', 'Struktur gaji ' . $level->name . ' disimpan.');
    }

    /** Posisi tiap karyawan dalam band gaji INTERNAL-nya — kontrol ruang merit increase. */
    public function gradePosition(Request $request)
    {
        $companyId = $request->filled('company_id') ? (int) $request->company_id : null;
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        $employees = Employee::where('is_active', true)
            ->when($companyId, fn ($q, $v) => $q->where('company_id', $v))
            ->with(['level', 'department', 'position', 'company'])
            ->get();

        $latestSlips = PayrollSlip::whereIn('employee_id', $employees->pluck('id'))
            ->whereHas('period', fn ($q) => $q->where('status', 'closed'))
            ->with('period')->get()
            ->groupBy('employee_id')
            ->map(fn ($slips) => $slips->sortByDesc(fn ($s) => [$s->period->year, $s->period->month])->first());

        // Grade per company spesifik dulu, fallback ke grade global (company_id NULL).
        $gradesGlobal = SalaryGrade::whereNull('company_id')->get()->keyBy('level_id');
        $gradesByCompany = $companyId ? SalaryGrade::where('company_id', $companyId)->get()->keyBy('level_id') : collect();

        $rows = $employees->map(function ($e) use ($latestSlips, $gradesGlobal, $gradesByCompany) {
            $slip = $latestSlips->get($e->id);
            $grade = $e->level_id ? ($gradesByCompany->get($e->level_id) ?? $gradesGlobal->get($e->level_id)) : null;
            $current = $slip?->gross_salary;
            $position = ($current !== null && $grade) ? $grade->positionInBand((int) $current) : null;

            return [
                'employee' => $e, 'current' => $current, 'grade' => $grade, 'position' => $position,
                'at_ceiling' => $position !== null && $position >= 95,
            ];
        });

        $atCeiling = $rows->filter(fn ($r) => $r['at_ceiling'])->sortByDesc('position');

        return view('hr.compensation.grade-position', compact('rows', 'atCeiling', 'companies', 'companyId'));
    }

    // ── Total Rewards Statement ──────────────────────────────────────────────

    public function rewardsStatement(Request $request, Employee $employee)
    {
        $year = (int) $request->get('year', now()->year);
        $data = $this->buildRewardsData($employee, $year);

        return view('hr.compensation.rewards-statement', $data);
    }

    public function rewardsStatementPdf(Request $request, Employee $employee)
    {
        $year = (int) $request->get('year', now()->year);
        $data = $this->buildRewardsData($employee, $year);

        $pdf = Pdf::loadView('hr.compensation.rewards-statement-pdf', $data)->setPaper('a4', 'portrait');

        return $pdf->download('total-rewards-' . $employee->nip . '-' . $year . '.pdf');
    }

    public function myRewardsStatement(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun Anda belum terhubung ke data karyawan.');
        $year = (int) $request->get('year', now()->year);
        $data = $this->buildRewardsData($employee, $year);

        return view('hr.compensation.rewards-statement', $data + ['isMine' => true]);
    }

    public function myRewardsStatementPdf(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun Anda belum terhubung ke data karyawan.');
        $year = (int) $request->get('year', now()->year);
        $data = $this->buildRewardsData($employee, $year);

        $pdf = Pdf::loadView('hr.compensation.rewards-statement-pdf', $data)->setPaper('a4', 'portrait');

        return $pdf->download('total-rewards-' . $employee->nip . '-' . $year . '.pdf');
    }

    /**
     * Total Rewards dihitung dari data payroll/THR/bonus REAL periode closed tahun berjalan —
     * bukan proyeksi/kalkulasi ulang. Kalau data belum lengkap 12 bulan, ditampilkan apa adanya
     * (bukan diekstrapolasi) supaya tidak menyesatkan.
     */
    private function buildRewardsData(Employee $employee, int $year): array
    {
        $employee->loadMissing(['company', 'department', 'position', 'level']);

        $slips = PayrollSlip::where('employee_id', $employee->id)
            ->whereHas('period', fn ($q) => $q->where('status', 'closed')->where('year', $year))
            ->with('period')->get()->sortBy(fn ($s) => $s->period->month);

        $cashCompYtd = (int) $slips->sum('gross_salary');
        $monthsCounted = $slips->count();

        $thrYtd = (int) ThrPayment::where('employee_id', $employee->id)
            ->whereHas('period', fn ($q) => $q->where('year', $year)->where('status', 'closed'))
            ->sum('thr_amount');

        $bonusYtd = (int) BonusPayment::where('employee_id', $employee->id)
            ->whereHas('period', fn ($q) => $q->where('status', 'closed')
                ->whereYear('payment_date', $year))
            ->sum('net_amount');

        $trainingCount = TrainingParticipant::where('employee_id', $employee->id)
            ->where('status', 'completed')
            ->whereYear('end_date', $year)
            ->count();

        $totalCash = $cashCompYtd + $thrYtd + $bonusYtd;

        return compact('employee', 'year', 'slips', 'cashCompYtd', 'monthsCounted', 'thrYtd', 'bonusYtd', 'trainingCount', 'totalCash');
    }
}
