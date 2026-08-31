<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Candidate;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeOffboardingTask;
use App\Models\HR\AttendanceRecord;
use App\Models\HR\EmployeeLoan;
use App\Models\HR\LeaveRequest;
use App\Models\HR\LoanInstallment;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\PayrollSlip;
use App\Models\HR\PayrollSlipDetail;
use App\Models\JobRequisition;
use App\Models\ManpowerPlan;
use App\Models\RecruitmentCost;
use App\Models\TerminationRequest;
use App\Models\TrainingParticipant;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Services\ApprovalEngine;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Agregasi data SDM untuk halaman Laporan (admin/laporan/*) DAN dashboard admin HRD.
 * Semua builder menerima $companyId nullable — null berarti konsolidasi lintas PT.
 *
 * Sebelumnya method-method ini private di LaporanController; dipindah ke sini supaya
 * dashboard bisa memakainya tanpa duplikasi logika.
 */
class HrReportBuilder
{
    // ── Headcount (dipakai laporan.headcount + dashboard) ───────────────────

    public function buildHeadcount(?int $companyId): array
    {
        $employees = Employee::where('is_active', true)
            ->when($companyId, fn ($q, $v) => $q->where('company_id', $v))
            ->with(['company', 'department', 'division', 'section', 'level'])
            ->get();

        $groupBy = fn ($keyFn) => $employees->groupBy($keyFn)->map->count()->sortDesc();

        $empStatusLabels = ['permanent' => 'Tetap', 'contract' => 'Kontrak', 'probation' => 'Probation'];

        $byStatus = collect($empStatusLabels)->mapWithKeys(fn ($label, $key) => [
            $label => $employees->where('employment_status', $key)->count(),
        ])->filter();
        $other = $employees->whereNotIn('employment_status', array_keys($empStatusLabels))->count();
        if ($other > 0) {
            $byStatus->put('Lainnya', $other);
        }

        return [
            'total'              => $employees->count(),
            'group_total'        => Employee::where('is_active', true)->count(),
            'new_this_month'     => $employees->filter(fn ($e) => $e->start_date && $e->start_date->isCurrentMonth())->count(),
            'new_this_year'      => $employees->filter(fn ($e) => $e->start_date && $e->start_date->isCurrentYear())->count(),
            'contract_ending'    => Employee::where('is_active', true)
                ->when($companyId, fn ($q, $v) => $q->where('company_id', $v))
                ->where('employment_status', 'contract')
                ->whereNotNull('contract_end_date')
                ->whereBetween('contract_end_date', [now()->toDateString(), now()->addMonths(2)->toDateString()])
                ->count(),
            'by_company'         => $companyId ? collect() : $groupBy(fn ($e) => $e->company?->name ?? 'Tanpa PT'),
            'by_department'      => $groupBy(fn ($e) => $e->department?->name ?? 'Tanpa Departemen'),
            'by_division'        => $groupBy(fn ($e) => $e->division?->name ?? 'Tanpa Divisi'),
            'by_section'         => $groupBy(fn ($e) => $e->section?->name ?? 'Tanpa Section'),
            'by_level'           => $groupBy(fn ($e) => $e->level?->name ?? 'Tanpa Level'),
            'by_status'          => $byStatus,
            'by_type'            => collect([
                'Local' => $employees->where('employee_type', '!=', 'expat')->count(),
                'Expat' => $employees->where('employee_type', 'expat')->count(),
            ])->filter(),
            'by_gender'          => collect([
                'Laki-laki' => $employees->where('gender', 'L')->count(),
                'Perempuan' => $employees->where('gender', 'P')->count(),
            ])->filter(fn ($v) => $v > 0),
        ];
    }

    // ── Analytics (turnover / absenteeism / cost-per-hire / training) ───────

    public function buildAnalytics(?int $companyId, int $year): array
    {
        return [
            'turnover'      => $this->buildTurnover($companyId, $year),
            'absenteeism'   => $this->buildAbsenteeism($companyId, $year),
            'cost_per_hire' => $this->buildCostPerHire($companyId, $year),
            'training'      => $this->buildTrainingEffectiveness($companyId, $year),
        ];
    }

    public function buildTurnover(?int $companyId, int $year): array
    {
        $separations = TerminationRequest::where('status', 'approved')
            ->whereYear('effective_date', $year)
            ->when($companyId, fn ($q, $v) => $q->where('company_id', $v))
            ->with('employee.department')
            ->get();

        $voluntaryTypes = ['resign', 'retirement'];
        $voluntary   = $separations->whereIn('termination_type', $voluntaryTypes)->count();
        $involuntary = $separations->count() - $voluntary;

        $avgHeadcount = Employee::where('is_active', true)
            ->when($companyId, fn ($q, $v) => $q->where('company_id', $v))->count();
        $rate = $avgHeadcount > 0 ? round($separations->count() / $avgHeadcount * 100, 2) : null;

        $monthly = $separations->groupBy(fn ($t) => $t->effective_date->month)->map->count();
        $byDept  = $separations->groupBy(fn ($t) => $t->employee?->department?->name ?? 'Tanpa Departemen')->map->count()->sortDesc();

        return [
            'total' => $separations->count(), 'voluntary' => $voluntary, 'involuntary' => $involuntary,
            'avg_headcount' => $avgHeadcount, 'rate' => $rate, 'monthly' => $monthly, 'by_department' => $byDept,
        ];
    }

    public function buildAbsenteeism(?int $companyId, int $year): array
    {
        $records = AttendanceRecord::whereYear('date', $year)
            ->when($companyId, fn ($q, $v) => $q->where('company_id', $v))
            ->with('employee.department')->get();

        $total = $records->count();
        $alpha = $records->where('status', 'alpha')->count();
        $rate  = $total > 0 ? round($alpha / $total * 100, 2) : null;

        $monthly = $records->groupBy(fn ($r) => $r->date->month)
            ->map(fn ($rows) => $rows->count() > 0 ? round($rows->where('status', 'alpha')->count() / $rows->count() * 100, 2) : 0);

        $byDept = $records->groupBy(fn ($r) => $r->employee?->department?->name ?? 'Tanpa Departemen')
            ->map(fn ($rows) => $rows->count() > 0 ? round($rows->where('status', 'alpha')->count() / $rows->count() * 100, 2) : 0)
            ->sortDesc();

        return ['total_records' => $total, 'alpha_days' => $alpha, 'rate' => $rate, 'monthly' => $monthly, 'by_department' => $byDept];
    }

    public function buildCostPerHire(?int $companyId, int $year): array
    {
        $totalCost = RecruitmentCost::whereYear('incurred_on', $year)
            ->when($companyId, fn ($q, $v) => $q->where('company_id', $v))->sum('amount');

        $hires = Employee::whereYear('start_date', $year)
            ->when($companyId, fn ($q, $v) => $q->where('company_id', $v))->count();

        return [
            'total_cost' => (int) $totalCost, 'hires' => $hires,
            'cost_per_hire' => $hires > 0 ? round($totalCost / $hires) : null,
        ];
    }

    public function buildTrainingEffectiveness(?int $companyId, int $year): array
    {
        $participants = TrainingParticipant::whereYear('start_date', $year)
            ->when($companyId, fn ($q, $v) => $q->whereHas('employee', fn ($e) => $e->where('company_id', $v)))
            ->with('program')->get();

        $total     = $participants->count();
        $completed = $participants->where('status', 'completed')->count();
        $rate      = $total > 0 ? round($completed / $total * 100, 2) : null;

        $numericScores = $participants->pluck('score')->filter(fn ($s) => is_numeric($s))->map(fn ($s) => (float) $s);
        $avgScore = $numericScores->isNotEmpty() ? round($numericScores->avg(), 2) : null;

        $byProgram = $participants->groupBy(fn ($p) => $p->program?->title ?? 'Tanpa Program')
            ->map(fn ($rows) => [
                'total' => $rows->count(),
                'completed' => $rows->where('status', 'completed')->count(),
            ]);

        return ['total' => $total, 'completed' => $completed, 'rate' => $rate, 'avg_score' => $avgScore, 'by_program' => $byProgram];
    }

    // ── Absensi & Cuti bulanan (companyId nullable = konsolidasi) ───────────

    public function buildAttendanceLeave(?int $companyId, int $bulan, int $tahun): array
    {
        $records = AttendanceRecord::whereYear('date', $tahun)
            ->whereMonth('date', $bulan)
            ->when($companyId, fn ($q, $v) => $q->where('company_id', $v))
            ->with('employee')
            ->get();

        $statusKeys = array_keys(AttendanceRecord::$statusLabels);

        $perEmployee = $records->groupBy('employee_id')->map(function ($rows) use ($statusKeys) {
            $counts = array_fill_keys($statusKeys, 0);
            foreach ($rows as $r) {
                if (isset($counts[$r->status])) {
                    $counts[$r->status]++;
                }
            }

            return [
                'name'             => $rows->first()->employee?->name ?? '-',
                'counts'           => $counts,
                'late_minutes'     => (int) $rows->sum('late_minutes'),
                'overtime_minutes' => (int) $rows->sum('overtime_minutes'),
            ];
        })->sortBy('name')->values();

        $totals = array_fill_keys($statusKeys, 0);
        foreach ($perEmployee as $emp) {
            foreach ($emp['counts'] as $k => $v) {
                $totals[$k] += $v;
            }
        }

        $leaves = LeaveRequest::where('status', 'approved')
            ->whereYear('start_date', $tahun)
            ->whereMonth('start_date', $bulan)
            ->when($companyId, fn ($q, $v) => $q->whereHas('employee', fn ($e) => $e->where('company_id', $v)))
            ->with(['employee', 'leaveType'])
            ->get();

        $leaveByType = $leaves->groupBy('leave_type_id')->map(fn ($g) => [
            'type'  => $g->first()->leaveType?->name ?? '(tanpa tipe)',
            'count' => $g->count(),
            'days'  => (float) $g->sum('total_days'),
        ])->sortByDesc('days')->values();

        return [
            'statusLabels'  => AttendanceRecord::$statusLabels,
            'per_employee'  => $perEmployee,
            'totals'        => $totals,
            'total_records' => $records->count(),
            'leave_by_type' => $leaveByType,
            'leave_total_days' => (float) $leaves->sum('total_days'),
            'leave_total_count' => $leaves->count(),
        ];
    }

    public function buildPayrollSummary(PayrollPeriod $period): array
    {
        $slips = PayrollSlip::where('payroll_period_id', $period->id)
            ->with('employee')
            ->get();

        $componentBreakdown = PayrollSlipDetail::whereIn('payroll_slip_id', $slips->pluck('id'))
            ->select('component_name', 'type', DB::raw('COUNT(*) as n'), DB::raw('SUM(amount) as total'))
            ->groupBy('component_name', 'type')
            ->orderByDesc('total')
            ->get()
            ->groupBy('type');

        return [
            'slip_count'        => $slips->count(),
            'total_gross'       => (float) $slips->sum('gross_salary'),
            'total_net'         => (float) $slips->sum('net_salary'),
            'total_allowances'  => (float) $slips->sum('total_allowances'),
            'total_deductions'  => (float) $slips->sum('total_deductions'),
            'allowances'        => $componentBreakdown->get('allowance', collect()),
            'deductions'        => $componentBreakdown->get('deduction', collect()),
            'slips'             => $slips->sortBy(fn ($s) => $s->employee?->name ?? '')->values(),
        ];
    }

    // ── BARU: khusus dashboard admin HRD ───────────────────────────────────

    /**
     * Angka headcount tambahan yang belum ada di buildHeadcount:
     * distribusi masa kerja, tren masuk vs keluar 12 bulan, per cabang,
     * dokumen yang akan kadaluarsa.
     */
    public function hrHeadcountExtra(?int $companyId): array
    {
        $active = Employee::where('is_active', true)
            ->when($companyId, fn ($q, $v) => $q->where('company_id', $v));

        // Distribusi masa kerja
        $buckets = ['< 1 th' => 0, '1–3 th' => 0, '3–5 th' => 0, '5–10 th' => 0, '10+ th' => 0];
        (clone $active)->whereNotNull('start_date')->pluck('start_date')->each(function ($d) use (&$buckets) {
            $y = Carbon::parse($d)->diffInYears(now());
            $key = $y < 1 ? '< 1 th' : ($y < 3 ? '1–3 th' : ($y < 5 ? '3–5 th' : ($y < 10 ? '5–10 th' : '10+ th')));
            $buckets[$key]++;
        });

        // Per cabang (branch_id → Branch, fallback string branch)
        $byBranch = (clone $active)->with('branchLocation')->get()
            ->groupBy(fn ($e) => $e->branchLocation?->name ?? $e->branch ?? 'Tanpa Cabang')
            ->map->count()->sortDesc();

        // Tren masuk vs keluar 12 bulan terakhir
        $from = now()->copy()->subMonths(11)->startOfMonth();
        $months = [];
        for ($m = $from->copy(); $m <= now()->endOfMonth(); $m->addMonth()) {
            $months[$m->format('Y-m')] = 0;
        }

        $hires = Employee::whereBetween('start_date', [$from->toDateString(), now()->toDateString()])
            ->when($companyId, fn ($q, $v) => $q->where('company_id', $v))
            ->pluck('start_date')
            ->groupBy(fn ($d) => Carbon::parse($d)->format('Y-m'))->map->count();

        $exits = TerminationRequest::where('status', 'approved')
            ->whereBetween('effective_date', [$from->toDateString(), now()->toDateString()])
            ->when($companyId, fn ($q, $v) => $q->where('company_id', $v))
            ->pluck('effective_date')
            ->groupBy(fn ($d) => Carbon::parse($d)->format('Y-m'))->map->count();

        $movement = [
            'labels' => array_keys($months),
            'hires'  => collect($months)->map(fn ($_, $k) => (int) ($hires[$k] ?? 0))->values()->all(),
            'exits'  => collect($months)->map(fn ($_, $k) => (int) ($exits[$k] ?? 0))->values()->all(),
        ];

        // Dokumen akan kadaluarsa (60 hari)
        $docsExpiring = EmployeeDocument::whereNotNull('expires_at')
            ->whereBetween('expires_at', [now()->toDateString(), now()->addDays(60)->toDateString()])
            ->whereHas('employee', fn ($q) => $q->where('is_active', true)
                ->when($companyId, fn ($qq, $v) => $qq->where('company_id', $v)))
            ->with('employee')
            ->orderBy('expires_at')
            ->get()
            ->map(fn ($d) => [
                'employee' => $d->employee?->name ?? '-',
                'doc_type' => $d->doc_type,
                'title'    => $d->title,
                'expires'  => Carbon::parse($d->expires_at),
            ]);

        return [
            'tenure_buckets'     => collect($buckets)->filter(),
            'by_branch'          => $byBranch,
            'movement_12m'       => $movement,
            'documents_expiring' => $docsExpiring,
        ];
    }

    /**
     * Snapshot operasional HR: cuti, absensi bulan ini, rekrutmen, training,
     * kasbon, offboarding, payroll terakhir, approval menunggu.
     */
    public function hrOperational(?int $companyId, ?User $user = null): array
    {
        $viaEmployee = fn ($q) => $q->when($companyId, fn ($qq, $v) => $qq->whereHas('employee', fn ($e) => $e->where('company_id', $v)));
        $viaCompany  = fn ($q) => $q->when($companyId, fn ($qq, $v) => $qq->where('company_id', $v));

        // ── Cuti ──
        $onLeaveToday = LeaveRequest::whereIn('status', ['approved', 'approved_hr'])
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->when($companyId, fn ($q, $v) => $q->whereHas('employee', fn ($e) => $e->where('company_id', $v)))
            ->with(['employee.department', 'leaveType'])
            ->get();

        $leavePending = LeaveRequest::whereIn('status', ['pending', 'submitted', 'approved_manager'])
            ->when($companyId, fn ($q, $v) => $q->whereHas('employee', fn ($e) => $e->where('company_id', $v)))
            ->with(['employee', 'leaveType'])
            ->latest()
            ->get();

        $leave = [
            'pending'        => $leavePending->count(),
            'recent_pending' => $leavePending->take(6),
            'on_leave_today' => $onLeaveToday,
        ];

        // ── Absensi bulan ini ──
        $att = AttendanceRecord::whereYear('date', now()->year)->whereMonth('date', now()->month)
            ->when($companyId, fn ($q, $v) => $q->where('company_id', $v))
            ->get();
        $attTotal = $att->count();
        $attByStatus = collect(AttendanceRecord::$statusLabels)
            ->mapWithKeys(fn ($label, $key) => [$label => $att->where('status', $key)->count()])
            ->filter();
        $present = $att->whereIn('status', ['hadir', 'telat'])->count();

        $attendanceMonth = [
            'total'            => $attTotal,
            'by_status'        => $attByStatus,
            'present'          => $present,
            'late'             => $att->where('status', 'telat')->count(),
            'alpha'            => $att->where('status', 'alpha')->count(),
            'late_minutes'     => (int) $att->sum('late_minutes'),
            'overtime_hours'   => round($att->sum('overtime_minutes') / 60, 1),
            'rate'             => $attTotal > 0 ? round($present / $attTotal * 100, 1) : null,
        ];

        // ── Rekrutmen ──
        $funnel = [];
        foreach (Candidate::$statusFlow as $stage) {
            $funnel[Candidate::$statusLabels[$stage] ?? $stage] = Candidate::where('status', $stage)
                ->when($companyId, fn ($q, $v) => $q->whereHas('jobRequisition', fn ($jr) => $jr->where('company_id', $v)))
                ->count();
        }

        $recruitment = [
            'mpp_open'         => $viaCompany(ManpowerPlan::where('status', 'approved'))->count(),
            'jobreq_open'      => $viaCompany(JobRequisition::where('status', 'approved'))->count(),
            'candidate_funnel' => collect($funnel),
            'candidates_active' => Candidate::whereIn('status', ['applied', 'screening', 'interview', 'offer'])
                ->when($companyId, fn ($q, $v) => $q->whereHas('jobRequisition', fn ($jr) => $jr->where('company_id', $v)))
                ->count(),
        ];

        // ── Training ──
        $tpYear = $viaEmployee(TrainingParticipant::whereYear('start_date', now()->year))->get();
        $training = [
            'programs_active' => $viaCompany(TrainingProgram::where('is_active', true))->count(),
            'ongoing'         => $viaEmployee(TrainingParticipant::where('status', 'ongoing'))->count(),
            'planned'         => $viaEmployee(TrainingParticipant::where('status', 'planned'))->count(),
            'completion_ytd'  => $tpYear->count() > 0
                ? round($tpYear->where('status', 'completed')->count() / $tpYear->count() * 100)
                : null,
        ];

        // ── Kasbon / Pinjaman ──
        $loanOutstanding = LoanInstallment::where('status', 'pending')
            ->whereHas('loan', fn ($q) => $q->where('status', 'active')
                ->when($companyId, fn ($qq, $v) => $qq->where('company_id', $v)))
            ->sum('amount');
        $loans = [
            'active'            => $viaCompany(EmployeeLoan::where('status', 'active'))->count(),
            'outstanding_total' => (int) $loanOutstanding,
        ];

        // ── Offboarding ──
        $offboarding = [
            'in_progress'   => $viaCompany(TerminationRequest::where('status', 'approved')
                ->whereDate('effective_date', '>=', now()))->count(),
            'tasks_pending' => EmployeeOffboardingTask::where('is_done', false)
                ->when($companyId, fn ($q, $v) => $q->whereHas('employee', fn ($e) => $e->where('company_id', $v)))
                ->count(),
        ];

        // ── Payroll periode terakhir ──
        $lastPeriod = PayrollPeriod::when($companyId, fn ($q, $v) => $q->where('company_id', $v))
            ->orderByDesc('year')->orderByDesc('month')->with('company')->first();
        $payrollLast = null;
        if ($lastPeriod) {
            $payrollLast = [
                'label'      => $lastPeriod->period_label,
                'company'    => $lastPeriod->company?->short_name ?? $lastPeriod->company?->name,
                'status'     => $lastPeriod->status,
                'slip_count' => PayrollSlip::where('payroll_period_id', $lastPeriod->id)->count(),
                'total_net'  => (int) PayrollSlip::where('payroll_period_id', $lastPeriod->id)->sum('net_salary'),
            ];
        }

        // ── Pengumuman aktif ──
        $announcements = Announcement::published()
            ->when($companyId, fn ($q, $v) => $q->where(fn ($qq) => $qq->whereNull('company_id')->orWhere('company_id', $v)))
            ->latest('published_at')
            ->take(5)
            ->get();

        return [
            'leave'            => $leave,
            'attendance_month' => $attendanceMonth,
            'recruitment'      => $recruitment,
            'training'         => $training,
            'loans'            => $loans,
            'offboarding'      => $offboarding,
            'payroll_last'     => $payrollLast,
            'announcements'    => $announcements,
            'approvals_pending' => $user ? app(ApprovalEngine::class)->pendingStepsFor($user)->count() : 0,
        ];
    }

    /**
     * Ulang tahun & hari jadi kerja karyawan pada bulan berjalan.
     */
    public function hrCalendar(?int $companyId): array
    {
        $base = Employee::where('is_active', true)
            ->when($companyId, fn ($q, $v) => $q->where('company_id', $v))
            ->with('department');

        $birthdays = (clone $base)->whereMonth('birth_date', now()->month)->get()
            ->map(fn ($e) => [
                'name'       => $e->name,
                'department' => $e->department?->name ?? '—',
                'day'        => (int) $e->birth_date?->day,
                'date'       => $e->birth_date,
            ])
            ->sortBy('day')->values();

        $anniversaries = (clone $base)->whereMonth('start_date', now()->month)
            ->whereYear('start_date', '<', now()->year)->get()
            ->map(fn ($e) => [
                'name'       => $e->name,
                'department' => $e->department?->name ?? '—',
                'day'        => (int) $e->start_date?->day,
                'years'      => now()->year - (int) $e->start_date?->year,
                'date'       => $e->start_date,
            ])
            ->sortBy('day')->values();

        return ['birthdays' => $birthdays, 'anniversaries' => $anniversaries];
    }
}
