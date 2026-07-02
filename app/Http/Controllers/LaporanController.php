<?php

namespace App\Http\Controllers;

use App\Exports\EmployeeExport;
use App\Exports\PerdinExport;
use App\Exports\ReimbursementExport;
use App\Models\Employee;
use App\Models\Perdin\PerdinRequest;
use App\Models\Reimbursement\ReimbursementRequest;
use App\Models\WhistleblowerReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        [$stats] = [$this->buildStats($bulan, $tahun)];

        return view('laporan.index', compact('bulan', 'tahun', 'stats'));
    }

    public function pdf(Request $request)
    {
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);
        $stats = $this->buildStats($bulan, $tahun);

        $pdf = Pdf::loadView('laporan.pdf', compact('bulan', 'tahun', 'stats'))
            ->setPaper('a4', 'portrait');

        $filename = 'laporan-rekap-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . $tahun . '.pdf';
        return $pdf->download($filename);
    }

    public function exportEmployees(Request $request)
    {
        $status = $request->get('status'); // 'active','inactive', or null = all

        $employees = Employee::with(['level', 'manager'])
            ->when($status === 'active',   fn($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->get();

        $filename = 'data-karyawan-' . now()->format('Ymd') . '.xlsx';
        return Excel::download(new EmployeeExport($employees), $filename);
    }

    public function exportReimbursements(Request $request)
    {
        $query = ReimbursementRequest::with(['user', 'approver']);

        if ($request->status) $query->where('status', $request->status);
        if ($request->year)   $query->whereYear('request_date', $request->year);
        if ($request->month)  $query->whereMonth('request_date', $request->month);

        $requests  = $query->latest('request_date')->get();
        $filename  = 'reimbursement-' . now()->format('Ymd') . '.xlsx';
        return Excel::download(new ReimbursementExport($requests), $filename);
    }

    public function exportPerdin(Request $request)
    {
        $query = PerdinRequest::with('user');

        if ($request->status) $query->where('status', $request->status);
        if ($request->year)   $query->whereYear('departure_date', $request->year);
        if ($request->month)  $query->whereMonth('departure_date', $request->month);

        $requests = $query->latest('departure_date')->get();
        $filename = 'perdin-' . now()->format('Ymd') . '.xlsx';
        return Excel::download(new PerdinExport($requests), $filename);
    }

    private function buildStats(int $bulan, int $tahun): array
    {
        // Reimbursement
        $reimb = ReimbursementRequest::whereYear('request_date', $tahun)
            ->whereMonth('request_date', $bulan)
            ->with('user')
            ->get();

        // Perdin
        $perdin = PerdinRequest::whereYear('departure_date', $tahun)
            ->whereMonth('departure_date', $bulan)
            ->with('user')
            ->get();

        // Whistleblower
        $wb = WhistleblowerReport::whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->get();

        // Kontrak karyawan
        $contractExpiring = Employee::where('employment_status', 'contract')
            ->where('is_active', true)
            ->whereNotNull('contract_end_date')
            ->whereBetween('contract_end_date', [now()->toDateString(), now()->addDays(60)->toDateString()])
            ->orderBy('contract_end_date')
            ->get();

        $contractExpired = Employee::where('employment_status', 'contract')
            ->where('is_active', true)
            ->whereNotNull('contract_end_date')
            ->where('contract_end_date', '<', now()->toDateString())
            ->orderBy('contract_end_date')
            ->get();

        // Reimbursement per karyawan (approved)
        $reimbPerUser = $reimb->where('status', 'approved')
            ->groupBy('user_id')
            ->map(fn($g) => [
                'name'  => $g->first()->user?->name ?? '-',
                'count' => $g->count(),
                'total' => $g->sum('total_claim'),
            ])->sortByDesc('total')->values();

        // Perdin per karyawan (approved)
        $perdinPerUser = $perdin->where('status', 'approved')
            ->groupBy('user_id')
            ->map(fn($g) => [
                'name'  => $g->first()->user?->name ?? '-',
                'count' => $g->count(),
                'total' => $g->sum('total_budget'),
            ])->sortByDesc('total')->values();

        return [
            'reimb'  => [
                'total'    => $reimb->count(),
                'approved' => $reimb->where('status', 'approved')->count(),
                'pending'  => $reimb->whereIn('status', ['draft','submitted'])->count(),
                'rejected' => $reimb->where('status', 'rejected')->count(),
                'amount'   => $reimb->where('status', 'approved')->sum('total_claim'),
                'per_user' => $reimbPerUser,
            ],
            'perdin' => [
                'total'    => $perdin->count(),
                'approved' => $perdin->where('status', 'approved')->count(),
                'pending'  => $perdin->whereIn('status', ['draft','submitted','reviewed_manager','reviewed_hr'])->count(),
                'rejected' => $perdin->where('status', 'rejected')->count(),
                'amount'   => $perdin->where('status', 'approved')->sum('total_budget'),
                'per_user' => $perdinPerUser,
            ],
            'wb' => [
                'total'     => $wb->count(),
                'new'       => $wb->where('status', 'new')->count(),
                'resolved'  => $wb->whereIn('status', ['resolved','closed'])->count(),
            ],
            'karyawan' => [
                'expiring' => $contractExpiring,
                'expired'  => $contractExpired,
            ],
        ];
    }
}
