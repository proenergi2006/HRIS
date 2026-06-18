<?php

namespace App\Http\Controllers;

use App\Models\Appraisal\Appraisal;
use App\Models\Appraisal\AppraisalPeriod;
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

        if ($user->hasRole('admin')) {
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
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        return view('dashboard.admin', compact('stats', 'gradeDistrib', 'openPeriods', 'recentAppraisals', 'statusDistrib', 'deptDistrib'));
    }

    private function step1ApproverDashboard($user)
    {
        $baseQuery = Appraisal::with(['employee', 'period'])
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
