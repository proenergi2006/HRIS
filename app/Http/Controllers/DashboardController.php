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
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function show()
    {
        $user = auth()->user();

        if ($user->hasRole('admin_ga')) {
            return $this->gaDashboard();
        }

        if ($user->hasAnyRole(['admin', 'hr_manager'])) {
            return $this->adminDashboard();
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

    private function adminDashboard()
    {
        $stats = [
            'total'   => Appraisal::count(),
            'draft'   => Appraisal::whereIn('status', ['draft', 'rejected'])->count(),
            'pending' => Appraisal::whereIn('status', ['submitted', 'approved_user2'])->count(),
            'final'   => Appraisal::where('status', 'approved_cfo')->count(),
        ];

        $gradeDistrib = Appraisal::where('status', 'approved_cfo')
            ->whereNotNull('grade')
            ->select('grade', DB::raw('count(*) as total'))
            ->groupBy('grade')
            ->orderBy('total', 'desc')
            ->pluck('total', 'grade');

        $statusDistrib = Appraisal::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $deptDistrib = Appraisal::join('employees', 'appraisals.employee_id', '=', 'employees.id')
            ->whereNotNull('employees.department')
            ->select('employees.department', DB::raw('count(*) as total'))
            ->groupBy('employees.department')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'employees.department');

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
            ->whereNotNull('contract_end_date')
            ->whereBetween('contract_end_date', [now()->toDateString(), now()->addMonths(2)->toDateString()])
            ->orderBy('contract_end_date')
            ->get();

        $contractExpired = Employee::where('employment_status', 'contract')
            ->where('is_active', true)
            ->whereNotNull('contract_end_date')
            ->where('contract_end_date', '<', now()->toDateString())
            ->orderBy('contract_end_date')
            ->get();

        return view('dashboard.admin', compact(
            'stats', 'gradeDistrib', 'openPeriods', 'recentAppraisals', 'statusDistrib', 'deptDistrib',
            'reimb', 'perdin', 'wb', 'contractExpiring', 'contractExpired'
        ));
    }

    private function step1ApproverDashboard($user)
    {
        $baseQuery = Appraisal::with(['employee', 'period'])
            ->whereHas('employee')
            ->whereHas('period')
            ->where('status', 'submitted');

        if ($user->department) {
            $baseQuery->whereHas('employee', fn($q) => $q->where('department', $user->department));
        }

        $pending = $baseQuery->orderByDesc('submitted_at')->get();

        $doneQuery = Appraisal::whereIn('status', ['approved_user2', 'approved_cfo']);
        if ($user->department) {
            $doneQuery->whereHas('employee', fn($q) => $q->where('department', $user->department));
        }

        $stats = [
            'pending' => $pending->count(),
            'done'    => $doneQuery->count(),
        ];

        return view('dashboard.approver', [
            'pending'       => $pending,
            'stats'         => $stats,
            'approverLabel' => 'Menunggu Persetujuan Anda',
            'emptyMsg'      => 'Tidak ada penilaian yang menunggu persetujuan Anda.',
        ]);
    }

    private function finalApproverDashboard($user)
    {
        $baseQuery = Appraisal::with(['employee', 'period'])
            ->whereHas('employee')
            ->whereHas('period')
            ->where('status', 'approved_user2');

        if ($user->department) {
            $baseQuery->whereHas('employee', fn($q) => $q->where('department', $user->department));
        }

        $pending = $baseQuery->orderByDesc('updated_at')->get();

        $doneQuery = Appraisal::where('status', 'approved_cfo');
        if ($user->department) {
            $doneQuery->whereHas('employee', fn($q) => $q->where('department', $user->department));
        }

        $stats = [
            'pending' => $pending->count(),
            'done'    => $doneQuery->count(),
        ];

        return view('dashboard.approver', [
            'pending'       => $pending,
            'stats'         => $stats,
            'approverLabel' => 'Menunggu Persetujuan Final Anda',
            'emptyMsg'      => 'Tidak ada penilaian yang menunggu persetujuan final.',
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
            'pending' => $myAppraisals->whereIn('status', ['submitted', 'approved_user2'])->count(),
            'final'   => $myAppraisals->where('status', 'approved_cfo')->count(),
            'total'   => $myAppraisals->count(),
        ];

        return view('dashboard.evaluator', compact('myAppraisals', 'stats'));
    }
}
