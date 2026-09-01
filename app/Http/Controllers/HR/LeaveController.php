<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\HR\LeaveBalance;
use App\Models\HR\LeavePolicy;
use App\Models\HR\LeaveRequest;
use App\Models\HR\LeaveType;
use App\Models\Level;
use App\Services\ApprovalEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeaveController extends Controller
{
    public function __construct(private ApprovalEngine $engine) {}

    /**
     * Kalender cuti tim — HR (leave-admin.view) lihat seluruh PT, manager (punya
     * bawahan langsung) lihat timnya sendiri. Read-only, dari data leave_requests
     * yang sudah ada — tidak butuh skema baru.
     */
    public function teamCalendar(Request $request)
    {
        $user = $request->user();
        $isHr = $user->can('leave-admin.view');
        $employee = $user->employee;

        abort_unless($isHr || $employee?->subordinates()->exists(), 403, 'Anda tidak punya tim untuk dilihat kalendernya.');

        $companyId = $request->filled('company_id') ? (int) $request->company_id : null;
        $companies = $isHr ? Company::where('is_active', true)->orderBy('name')->get() : collect();

        $scopeIds = $isHr
            ? Employee::where('is_active', true)->when($companyId, fn ($q, $v) => $q->where('company_id', $v))->pluck('id')
            : $employee->subordinates()->where('is_active', true)->pluck('id');

        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);
        $start = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $leaves = LeaveRequest::whereIn('employee_id', $scopeIds)
            ->where('status', 'approved')
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->with(['employee', 'leaveType'])
            ->orderBy('start_date')->get();

        $gridStart = $start->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $gridEnd   = $end->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
        $days = [];
        for ($d = $gridStart->copy(); $d->lte($gridEnd); $d->addDay()) {
            $days[] = $d->copy();
        }

        // Kelompokkan per tanggal (satu leave request bisa mencakup beberapa hari).
        $byDate = collect();
        foreach ($leaves as $l) {
            for ($d = $l->start_date->copy(); $d->lte($l->end_date); $d->addDay()) {
                $key = $d->format('Y-m-d');
                $byDate[$key] = ($byDate[$key] ?? collect())->push($l);
            }
        }

        return view('hr.leave.team-calendar', compact('days', 'byDate', 'month', 'year', 'start', 'leaves', 'companies', 'companyId', 'isHr'));
    }

    public function index(Request $request)
    {
        $companies = Company::where('is_active', true)->get();
        $companyId = $request->get('company_id');
        $status    = $request->get('status');
        $year      = (int) $request->get('year', now()->year);

        $query = LeaveRequest::with(['employee.company', 'leaveType'])
            ->when($companyId, fn($q) => $q->whereHas('employee', fn($e) => $e->where('company_id', $companyId)))
            ->when($status,    fn($q) => $q->where('status', $status))
            ->whereYear('start_date', $year)
            ->latest();

        $requests = $query->paginate(20)->withQueryString();

        return view('hr.leave.index', compact('companies', 'companyId', 'status', 'year', 'requests'));
    }

    public function create(Request $request)
    {
        $companies  = Company::where('is_active', true)->get();
        $companyId  = $request->get('company_id', $companies->first()?->id);
        $employees  = Employee::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $leaveTypes = LeaveType::where('is_active', true)->get();
        return view('hr.leave.create', compact('companies', 'companyId', 'employees', 'leaveTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id'    => 'required|exists:companies,id',
            'employee_id'   => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'nullable|string|max:1000',
            'attachment'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Hitung hari kerja (exclude Sabtu/Minggu)
        $start     = \Carbon\Carbon::parse($data['start_date']);
        $end       = \Carbon\Carbon::parse($data['end_date']);
        $totalDays = 0;
        $current   = $start->copy();
        while ($current->lte($end)) {
            if (! $current->isWeekend()) $totalDays++;
            $current->addDay();
        }

        $attachPath = null;
        if ($request->hasFile('attachment')) {
            $attachPath = $request->file('attachment')->store('leave-attachments', 'local');
        }

        $leave = LeaveRequest::create([
            'employee_id'   => $data['employee_id'],
            'leave_type_id' => $data['leave_type_id'],
            'start_date'    => $data['start_date'],
            'end_date'      => $data['end_date'],
            'total_days'    => $totalDays,
            'reason'        => $data['reason'] ?? null,
            'attachment_path' => $attachPath,
            'status'        => 'pending',
        ]);

        // Jalankan lewat Approval Engine — alur diambil dari workflow "Cuti"
        // yang dikonfigurasi HR per perusahaan.
        $this->engine->start($leave->load('employee', 'leaveType'));
        $leave->refresh();
        if ($leave->approvalRequest?->status === 'approved' && ! $leave->isApproved()) {
            $leave->forceFill(['status' => 'approved'])->save();
        }

        return redirect()->route('hr.leave.show', $leave)
            ->with('success', 'Pengajuan cuti dibuat. Menunggu persetujuan.');
    }

    /** Batalkan pengajuan yang masih menunggu persetujuan. */
    public function cancel(LeaveRequest $leave)
    {
        abort_unless($leave->isPending(), 422);

        if ($leave->approvalRequest) {
            $this->engine->cancel($leave->approvalRequest);
        }
        $leave->forceFill(['status' => 'cancelled'])->save();

        return back()->with('success', 'Pengajuan cuti dibatalkan.');
    }

    public function show(LeaveRequest $leave)
    {
        $leave->load([
            'employee.company', 'leaveType',
            'approvalRequest.steps.approver', 'approvalRequest.steps.actedBy',
        ]);
        $balance = LeaveBalance::where('employee_id', $leave->employee_id)
            ->where('leave_type_id', $leave->leave_type_id)
            ->where('year', $leave->start_date->year)
            ->first();

        return view('hr.leave.show', compact('leave', 'balance'));
    }

    public function balances(Request $request)
    {
        $companies  = Company::where('is_active', true)->get();
        $companyId  = $request->get('company_id', $companies->first()?->id);
        $year       = (int) $request->get('year', now()->year);
        $leaveTypes = LeaveType::where('is_active', true)->get();

        $employees = Employee::where('company_id', $companyId)
            ->where('is_active', true)
            ->with(['leaveBalances' => fn($q) => $q->where('year', $year)->with('leaveType')])
            ->orderBy('name')
            ->get();

        return view('hr.leave.balances', compact('companies', 'companyId', 'year', 'employees', 'leaveTypes'));
    }

    public function upsertBalance(Request $request)
    {
        $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'year'          => 'required|integer|min:2020',
            'allocated'     => 'required|numeric|min:0',
        ]);

        LeaveBalance::updateOrCreate(
            [
                'employee_id'   => $request->employee_id,
                'leave_type_id' => $request->leave_type_id,
                'year'          => $request->year,
            ],
            ['allocated' => $request->allocated]
        );

        return back()->with('success', 'Saldo cuti berhasil diperbarui.');
    }

    public function attachment(LeaveRequest $leave)
    {
        abort_unless($leave->attachment_path && Storage::disk('local')->exists($leave->attachment_path), 404);
        return Storage::disk('local')->response($leave->attachment_path);
    }

    // ── Kebijakan cuti: carry-forward / kuota per golongan ─────────────────

    public function policies(Request $request)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $policies  = LeavePolicy::with(['company', 'leaveType', 'level'])
            ->orderBy('leave_type_id')->get();
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $levels     = Level::orderBy('rank')->get();

        return view('hr.leave.policies', compact('companies', 'policies', 'leaveTypes', 'levels'));
    }

    public function storePolicy(Request $request)
    {
        LeavePolicy::create($this->validatedPolicy($request));

        return back()->with('success', 'Kebijakan cuti ditambahkan.');
    }

    public function updatePolicy(Request $request, LeavePolicy $policy)
    {
        $policy->update($this->validatedPolicy($request));

        return back()->with('success', 'Kebijakan cuti diperbarui.');
    }

    public function destroyPolicy(LeavePolicy $policy)
    {
        $policy->delete();

        return back()->with('success', 'Kebijakan cuti dihapus.');
    }

    /** Alokasi ulang saldo cuti semua karyawan aktif untuk 1 tahun sesuai kebijakan. */
    public function generateBalances(Request $request)
    {
        $data = $request->validate(['year' => 'required|integer|min:2020']);
        \Illuminate\Support\Facades\Artisan::call('leave:year-end', ['year' => $data['year']]);

        $leaveTypes = LeaveType::where('is_active', true)->get();
        $employees  = Employee::where('is_active', true)->get();
        foreach ($employees as $employee) {
            foreach ($leaveTypes as $leaveType) {
                LeaveBalance::forEmployee($employee->id, $leaveType->id, (int) $data['year']);
            }
        }

        return back()->with('success', 'Saldo cuti tahun ' . $data['year'] . ' berhasil di-generate untuk semua karyawan aktif.');
    }

    private function validatedPolicy(Request $request): array
    {
        $data = $request->validate([
            'company_id'                  => 'nullable|exists:companies,id',
            'leave_type_id'               => 'required|exists:leave_types,id',
            'level_id'                     => 'nullable|exists:levels,id',
            'min_years_service'           => 'required|integer|min:0|max:50',
            'quota_days'                   => 'required|numeric|min:0',
            'carry_forward_max_days'      => 'nullable|numeric|min:0',
            'carry_forward_expire_month'  => 'required|integer|between:1,12',
            'is_active'                    => 'boolean',
        ]);
        $data['carry_forward_max_days'] = $data['carry_forward_max_days'] ?? 0;
        $data['is_active']              = $request->boolean('is_active', true);

        return $data;
    }
}
