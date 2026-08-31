<?php

namespace App\Http\Controllers;

use App\Exports\DynamicReportExport;
use App\Exports\EmployeeExport;
use App\Exports\PerdinExport;
use App\Exports\ReimbursementExport;
use App\Models\Company;
use App\Models\Employee;
use App\Models\HR\PayrollPeriod;
use App\Models\Perdin\PerdinRequest;
use App\Models\Reimbursement\ReimbursementRequest;
use App\Models\WhistleblowerReport;
use App\Services\HrReportBuilder;
use App\Services\ReportBuilderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function __construct(private HrReportBuilder $reports)
    {
    }

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

        $employees = Employee::with(['level', 'manager', 'department', 'position'])
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

        if ($request->get('by') === 'payment') {
            if ($request->year)  $query->where('payment_year', $request->year);
            if ($request->month) $query->where('payment_month', $request->month);
        } else {
            if ($request->year)   $query->whereYear('request_date', $request->year);
            if ($request->month)  $query->whereMonth('request_date', $request->month);
        }

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

    // ── Laporan Absensi & Cuti bulanan per company (PRD Bab 10) ──────────────

    public function attendanceLeave(Request $request)
    {
        [$companies, $companyId, $bulan, $tahun] = $this->companyPeriodFilter($request);
        $data = $this->reports->buildAttendanceLeave($companyId, $bulan, $tahun);

        return view('laporan.attendance-leave', compact('companies', 'companyId', 'bulan', 'tahun', 'data'));
    }

    public function attendanceLeavePdf(Request $request)
    {
        [$companies, $companyId, $bulan, $tahun] = $this->companyPeriodFilter($request);
        $data    = $this->reports->buildAttendanceLeave($companyId, $bulan, $tahun);
        $company = $companies->firstWhere('id', $companyId);

        $pdf = Pdf::loadView('laporan.attendance-leave-pdf', compact('company', 'bulan', 'tahun', 'data'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('laporan-absensi-cuti-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . $tahun . '.pdf');
    }

    // ── Laporan Payroll summary per periode per company (PRD Bab 10) ────────

    public function payrollSummary(Request $request)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $periods   = PayrollPeriod::with('company')->orderByDesc('year')->orderByDesc('month')->get();
        $periodId  = (int) $request->get('period_id', $periods->first()?->id);
        $period    = $periods->firstWhere('id', $periodId);
        $data      = $period ? $this->reports->buildPayrollSummary($period) : null;

        return view('laporan.payroll-summary', compact('companies', 'periods', 'periodId', 'period', 'data'));
    }

    public function payrollSummaryPdf(Request $request)
    {
        $period = PayrollPeriod::with('company')->findOrFail((int) $request->get('period_id'));
        $data   = $this->reports->buildPayrollSummary($period);

        $pdf = Pdf::loadView('laporan.payroll-summary-pdf', compact('period', 'data'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('laporan-payroll-' . $period->year . '-' . str_pad($period->month, 2, '0', STR_PAD_LEFT) . '.pdf');
    }

    // ── Dashboard Headcount per company / unit / status / tipe (PRD Bab 10) ─

    public function headcount(Request $request)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        // company_id kosong / 0 = tampilkan konsolidasi grup (semua company).
        $companyId = $request->filled('company_id') ? $request->integer('company_id') : null;
        $data      = $this->reports->buildHeadcount($companyId);

        return view('laporan.headcount', compact('companies', 'companyId', 'data'));
    }

    public function headcountPdf(Request $request)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $companyId = $request->filled('company_id') ? $request->integer('company_id') : null;
        $data      = $this->reports->buildHeadcount($companyId);
        $company   = $companyId ? $companies->firstWhere('id', $companyId) : null;

        $pdf = Pdf::loadView('laporan.headcount-pdf', compact('company', 'data'))->setPaper('a4', 'portrait');

        return $pdf->download('laporan-headcount-' . now()->format('Ymd') . '.pdf');
    }

    public function analytics(Request $request)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $companyId = $request->filled('company_id') ? $request->integer('company_id') : null;
        $year      = (int) $request->get('year', now()->year);
        $data      = $this->reports->buildAnalytics($companyId, $year);

        return view('laporan.analytics', compact('companies', 'companyId', 'year', 'data'));
    }

    public function analyticsPdf(Request $request)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $companyId = $request->filled('company_id') ? $request->integer('company_id') : null;
        $year      = (int) $request->get('year', now()->year);
        $data      = $this->reports->buildAnalytics($companyId, $year);
        $company   = $companyId ? $companies->firstWhere('id', $companyId) : null;

        $pdf = Pdf::loadView('laporan.analytics-pdf', compact('company', 'year', 'data'))->setPaper('a4', 'portrait');

        return $pdf->download('laporan-analytics-' . $year . '.pdf');
    }

    // ── Report Builder — pilih dataset + kolom + filter, preview / export Excel ──

    public function reportBuilder(Request $request)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $periods   = PayrollPeriod::orderByDesc('year')->orderByDesc('month')->get();
        $datasets  = ReportBuilderService::datasets();

        $dataset = $request->get('dataset');
        $rows = collect();
        $selectedColumns = [];

        if ($dataset && isset($datasets[$dataset])) {
            [$rows, $selectedColumns] = $this->runReportBuilder($request, $dataset);
        }

        return view('laporan.report-builder', compact('datasets', 'companies', 'periods', 'dataset', 'rows', 'selectedColumns'));
    }

    public function reportBuilderExport(Request $request)
    {
        $dataset = $request->get('dataset');
        $datasets = ReportBuilderService::datasets();
        abort_unless($dataset && isset($datasets[$dataset]), 422, 'Pilih dataset terlebih dahulu.');

        [$rows, $selectedColumns] = $this->runReportBuilder($request, $dataset, false);
        abort_if($rows->isEmpty(), 422, 'Tidak ada data untuk diekspor dengan filter ini.');

        $filename = 'report-' . $dataset . '-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new DynamicReportExport($rows, $selectedColumns), $filename);
    }

    /** @return array{0:\Illuminate\Support\Collection,1:array<string,string>} */
    private function runReportBuilder(Request $request, string $dataset, bool $limitPreview = true): array
    {
        $def = ReportBuilderService::dataset($dataset);
        $requestedColumns = array_values(array_intersect($request->input('columns', array_keys($def['columns'])), array_keys($def['columns'])));
        if (empty($requestedColumns)) {
            $requestedColumns = array_keys($def['columns']);
        }
        $selectedColumns = collect($def['columns'])->only($requestedColumns)->toArray();

        $filters = [
            'company_id'        => $request->filled('company_id') ? (int) $request->company_id : null,
            'date_from'         => $request->get('date_from'),
            'date_to'           => $request->get('date_to'),
            'payroll_period_id' => $request->get('payroll_period_id'),
        ];

        $allRows = app(ReportBuilderService::class)->rows($dataset, $filters);
        $rows = $allRows->map(fn ($r) => collect($r)->only($requestedColumns)->toArray());

        if ($limitPreview) {
            $rows = $rows->take(200);
        }

        return [$rows, $selectedColumns];
    }

    // ── Helpers laporan ────────────────────────────────────────────────────

    /** @return array{0:\Illuminate\Support\Collection,1:int,2:int,3:int} */
    private function companyPeriodFilter(Request $request): array
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $companyId = (int) $request->get('company_id', $companies->first()?->id);
        $bulan     = (int) $request->get('bulan', now()->month);
        $tahun     = (int) $request->get('tahun', now()->year);

        return [$companies, $companyId, $bulan, $tahun];
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

        // Reimbursement per karyawan (approved, berdasar tanggal pengajuan)
        $reimbPerUser = $reimb->where('status', 'approved')
            ->groupBy('user_id')
            ->map(fn($g) => [
                'name'  => $g->first()->user?->name ?? '-',
                'count' => $g->count(),
                'total' => $g->sum('total_claim'),
            ])->sortByDesc('total')->values();

        // Reimbursement yang akan dibayarkan pada periode gaji ini (berdasar payment_month/payment_year)
        $reimbByPayment = ReimbursementRequest::where('status', 'approved')
            ->where('payment_month', $bulan)
            ->where('payment_year', $tahun)
            ->with('user')
            ->get();

        $reimbByPaymentPerUser = $reimbByPayment
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
                'by_payment_period' => [
                    'count'    => $reimbByPayment->count(),
                    'amount'   => $reimbByPayment->sum('total_claim'),
                    'per_user' => $reimbByPaymentPerUser,
                ],
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
