<?php

namespace App\Http\Controllers;

use App\Models\Appraisal\Appraisal;
use App\Models\Appraisal\AppraisalPeriod;
use App\Models\Employee;
use App\Models\GA\Vehicle;
use App\Models\GA\VehicleUsage;
use App\Models\Perdin\PerdinRequest;
use App\Models\Reimbursement\ReimbursementRequest;
use App\Models\WhistleblowerReport;
use App\Services\ApprovalEngine;
use App\Services\HrReportBuilder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function show(Request $request)
    {
        $user = auth()->user();

        if ($user->hasRole('admin_ga')) {
            return $this->gaDashboard();
        }

        if ($user->hasAnyRole(['admin', 'hr_manager'])) {
            return $this->adminDashboard($request);
        }

        if ($user->hasRole(['cfo', 'ceo'])) {
            return $this->finalApproverDashboard($user);
        }

        if ($user->hasRole('user_ii')) {
            return $this->step1ApproverDashboard($user);
        }

        return $this->evaluatorDashboard($user);
    }

    private function gaDashboard()
    {
        $totalVehicles   = Vehicle::where('is_active', true)->count();
        $inUseVehicles   = VehicleUsage::where('status', 'checked_in')->count();
        $availVehicles   = $totalVehicles - $inUseVehicles;
        $todayUsages     = VehicleUsage::whereDate('check_in_at', today())->count();
        $activeUsages    = VehicleUsage::with('vehicle')->where('status', 'checked_in')->latest('check_in_at')->get();
        $recentUsages    = VehicleUsage::with('vehicle')->where('status', 'checked_out')
                            ->latest('check_out_at')->limit(10)->get();

        return view('ga.admin.dashboard', compact(
            'totalVehicles', 'inUseVehicles', 'availVehicles', 'todayUsages', 'activeUsages', 'recentUsages'
        ));
    }

    private function adminDashboard(Request $request)
    {
        $user      = auth()->user();
        $reports   = app(HrReportBuilder::class);
        $companies = \App\Models\Company::where('is_active', true)->orderBy('name')->get();
        $companyId = $request->filled('company_id') ? (int) $request->company_id : null; // null = konsolidasi grup
        if ($companyId && ! $companies->contains('id', $companyId)) {
            $companyId = null;
        }

        // Data SDM (baru — tab "SDM")
        $hc        = $reports->buildHeadcount($companyId);
        $hcx       = $reports->hrHeadcountExtra($companyId);
        $analytics = $reports->buildAnalytics($companyId, now()->year);
        $ops       = $reports->hrOperational($companyId, $user);
        $cal       = $reports->hrCalendar($companyId);

        $scopeAppraisalEmp = fn ($q) => $q->when($companyId, fn ($qq, $v) => $qq->whereHas('employee', fn ($e) => $e->where('company_id', $v)));

        $stats = [
            'total'   => $scopeAppraisalEmp(Appraisal::query())->count(),
            'draft'   => $scopeAppraisalEmp(Appraisal::whereIn('status', ['draft', 'rejected']))->count(),
            'pending' => $scopeAppraisalEmp(Appraisal::where('status', 'pending'))->count(),
            'final'   => $scopeAppraisalEmp(Appraisal::where('status', 'approved'))->count(),
        ];

        $gradeDistrib = $scopeAppraisalEmp(Appraisal::where('status', 'approved')->whereNotNull('grade'))
            ->select('grade', DB::raw('count(*) as total'))
            ->groupBy('grade')
            ->orderBy('total', 'desc')
            ->pluck('total', 'grade');

        $statusDistrib = $scopeAppraisalEmp(Appraisal::query())
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $deptDistrib = Appraisal::join('employees', 'appraisals.employee_id', '=', 'employees.id')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->whereNotNull('departments.name')
            ->when($companyId, fn ($q, $v) => $q->where('employees.company_id', $v))
            ->select('departments.name', DB::raw('count(*) as total'))
            ->groupBy('departments.name')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'departments.name');

        $openPeriods = AppraisalPeriod::where('status', 'open')
            ->withCount('appraisals')
            ->orderByDesc('year')
            ->get();

        $recentAppraisals = Appraisal::with(['employee', 'period'])
            ->whereHas('employee')
            ->whereHas('period')
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        $reimb = [
            'pending'      => ReimbursementRequest::where('status', 'submitted')->count(),
            'approved'     => ReimbursementRequest::where('status', 'approved')->whereYear('approved_at', now()->year)->count(),
            'total_claim'  => ReimbursementRequest::where('status', 'approved')->whereYear('approved_at', now()->year)->sum('total_claim'),
            'recent'       => ReimbursementRequest::with('user')->whereHas('user')
                                ->whereIn('status', ['submitted', 'approved', 'rejected'])
                                ->latest('updated_at')->limit(5)->get(),
        ];

        $perdinPendingStatuses = ['submitted', 'reviewed_manager', 'reviewed_hr'];
        $perdin = [
            'pending'      => PerdinRequest::whereIn('status', $perdinPendingStatuses)->count(),
            'approved'     => PerdinRequest::where('status', 'approved')->whereYear('updated_at', now()->year)->count(),
            'total_budget' => PerdinRequest::where('status', 'approved')->whereYear('updated_at', now()->year)->sum('total_budget'),
            'recent'       => PerdinRequest::with('user')->whereHas('user')
                                ->whereIn('status', array_merge($perdinPendingStatuses, ['approved', 'rejected']))
                                ->latest('updated_at')->limit(5)->get(),
        ];

        $wb = [
            'new'       => WhistleblowerReport::where('status', 'new')->count(),
            'in_review' => WhistleblowerReport::where('status', 'in_review')->count(),
            'resolved'  => WhistleblowerReport::where('status', 'resolved')->count(),
            'closed'    => WhistleblowerReport::where('status', 'closed')->count(),
            'total'     => WhistleblowerReport::count(),
            'by_category' => WhistleblowerReport::select('category', DB::raw('count(*) as total'))
                                ->groupBy('category')->orderByDesc('total')->get(),
            'by_branch'   => WhistleblowerReport::whereNotNull('branch_location')
                                ->select('branch_location', DB::raw('count(*) as total'))
                                ->groupBy('branch_location')->orderByDesc('total')->get(),
            'recent'    => WhistleblowerReport::latest()->limit(8)->get(),
        ];

        $contractExpiring = Employee::where('employment_status', 'contract')
            ->where('is_active', true)
            ->when($companyId, fn ($q, $v) => $q->where('company_id', $v))
            ->whereNotNull('contract_end_date')
            ->whereBetween('contract_end_date', [now()->toDateString(), now()->addMonths(2)->toDateString()])
            ->with(['position', 'department'])
            ->orderBy('contract_end_date')
            ->get();

        $contractExpired = Employee::where('employment_status', 'contract')
            ->where('is_active', true)
            ->when($companyId, fn ($q, $v) => $q->where('company_id', $v))
            ->whereNotNull('contract_end_date')
            ->where('contract_end_date', '<', now()->toDateString())
            ->with(['position', 'department'])
            ->orderBy('contract_end_date')
            ->get();

        return view('dashboard.admin', compact(
            'stats', 'gradeDistrib', 'openPeriods', 'recentAppraisals', 'statusDistrib', 'deptDistrib',
            'reimb', 'perdin', 'wb', 'contractExpiring', 'contractExpired',
            'companies', 'companyId', 'hc', 'hcx', 'analytics', 'ops', 'cal'
        ));
    }

    /**
     * Dipakai role user_ii/cfo/ceo — "pending" sekarang dihitung lewat Approval Engine
     * (step appraisal yang benar-benar bisa ditindak user ini), bukan lagi lewat status
     * mentah + department string (state machine 2-step sudah dihapus).
     */
    private function step1ApproverDashboard($user)
    {
        return $this->engineApproverDashboard($user, 'Menunggu Persetujuan Anda', 'Tidak ada penilaian yang menunggu persetujuan Anda.');
    }

    private function finalApproverDashboard($user)
    {
        return $this->engineApproverDashboard($user, 'Menunggu Persetujuan Final Anda', 'Tidak ada penilaian yang menunggu persetujuan final.');
    }

    private function engineApproverDashboard($user, string $label, string $emptyMsg)
    {
        $engine = app(ApprovalEngine::class);

        $pendingSteps = $engine->pendingStepsFor($user)
            ->whereHas('request', fn ($q) => $q->where('transaction_type', 'appraisal'))
            ->with('request.approvable.employee', 'request.approvable.period')
            ->get()
            ->filter(fn ($s) => $engine->canActOn($s, $user));

        $pending = $pendingSteps->map(fn ($s) => $s->request->approvable)->filter();

        $stats = [
            'pending' => $pending->count(),
            'done'    => Appraisal::where('evaluator_id', $user->id)->where('status', 'approved')->count(),
        ];

        return view('dashboard.approver', [
            'pending'       => $pending,
            'stats'         => $stats,
            'approverLabel' => $label,
            'emptyMsg'      => $emptyMsg,
        ]);
    }

    private function evaluatorDashboard($user)
    {
        $myAppraisals = Appraisal::with(['employee', 'period'])
            ->whereHas('employee')
            ->whereHas('period')
            ->where('evaluator_id', $user->id)
            ->orderByDesc('updated_at')
            ->get();

        $stats = [
            'draft'   => $myAppraisals->whereIn('status', ['draft', 'rejected'])->count(),
            'pending' => $myAppraisals->where('status', 'pending')->count(),
            'final'   => $myAppraisals->where('status', 'approved')->count(),
            'total'   => $myAppraisals->count(),
        ];

        return view('dashboard.evaluator', compact('myAppraisals', 'stats'));
    }
}
