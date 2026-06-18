<?php

namespace App\Http\Controllers\Appraisal;

use App\Exports\AppraisalExport;
use App\Http\Controllers\Controller;
use App\Models\Appraisal\Appraisal;
use App\Models\Appraisal\AppraisalPeriod;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function index(Request $request)
    {
        $user    = auth()->user();
        $periods = AppraisalPeriod::orderByDesc('year')->orderByDesc('id')->get();

        $query = Appraisal::with(['employee.level', 'period', 'template'])
            ->when($request->period_id,  fn($q) => $q->where('appraisal_period_id', $request->period_id))
            ->when($request->department, fn($q) => $q->whereHas('employee', fn($eq) => $eq->where('department', $request->department)))
            ->when($request->status,     fn($q) => $q->where('status', $request->status))
            ->when($request->grade,      fn($q) => $q->where('grade', $request->grade));

        // Admin dan CEO lihat semua; user lain hanya departemen sendiri
        if (!$user->hasAnyRole(['admin', 'ceo']) && $user->department) {
            $query->whereHas('employee', fn($q) => $q->where('department', $user->department));
        }

        $appraisals = $query->orderByDesc('id')->get();

        // Dropdown departemen: admin & CEO tampil semua, lainnya hanya dept sendiri
        $deptQuery = Appraisal::join('employees', 'appraisals.employee_id', '=', 'employees.id')
            ->whereNotNull('employees.department')
            ->distinct()
            ->orderBy('employees.department');

        if (!$user->hasAnyRole(['admin', 'ceo']) && $user->department) {
            $deptQuery->where('employees.department', $user->department);
        }

        $departments = $deptQuery->pluck('employees.department');
        $grades      = Appraisal::whereNotNull('grade')->distinct()->orderBy('grade')->pluck('grade');

        return view('appraisal.report.index', compact(
            'appraisals', 'periods', 'departments', 'grades'
        ));
    }

    public function export(Request $request)
    {
        $user  = auth()->user();
        $query = Appraisal::with(['employee.level', 'period', 'template', 'evaluator'])
            ->when($request->period_id,  fn($q) => $q->where('appraisal_period_id', $request->period_id))
            ->when($request->department, fn($q) => $q->whereHas('employee', fn($eq) => $eq->where('department', $request->department)))
            ->when($request->status,     fn($q) => $q->where('status', $request->status))
            ->when($request->grade,      fn($q) => $q->where('grade', $request->grade));

        if (!$user->hasAnyRole(['admin', 'ceo']) && $user->department) {
            $query->whereHas('employee', fn($q) => $q->where('department', $user->department));
        }

        $appraisals = $query->orderByDesc('id')->get();
        $filename   = 'laporan-penilaian-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new AppraisalExport($appraisals), $filename);
    }
}
