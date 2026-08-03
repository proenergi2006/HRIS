<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\HR\LeaveBalance;
use App\Models\HR\LeaveRequest;
use App\Models\HR\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeaveController extends Controller
{
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
            'status'        => 'submitted', // Admin input langsung submitted
        ]);

        return redirect()->route('hr.leave.show', $leave)
            ->with('success', 'Pengajuan cuti berhasil dibuat.');
    }

    public function show(LeaveRequest $leave)
    {
        $leave->load(['employee.company', 'leaveType', 'managerApprover', 'hrApprover']);
        $balance = LeaveBalance::where('employee_id', $leave->employee_id)
            ->where('leave_type_id', $leave->leave_type_id)
            ->where('year', $leave->start_date->year)
            ->first();

        return view('hr.leave.show', compact('leave', 'balance'));
    }

    public function approveManager(Request $request, LeaveRequest $leave)
    {
        abort_unless($leave->isSubmitted(), 422);
        $request->validate(['notes' => 'nullable|string|max:500']);

        $leave->update([
            'status'               => 'approved_manager',
            'manager_approved_by'  => auth()->id(),
            'manager_approved_at'  => now(),
            'manager_notes'        => $request->notes,
        ]);

        return back()->with('success', 'Cuti disetujui oleh atasan langsung.');
    }

    public function approveHR(Request $request, LeaveRequest $leave)
    {
        abort_unless($leave->isApprovedManager(), 422);
        $request->validate(['notes' => 'nullable|string|max:500']);

        // Potong saldo cuti
        $year    = $leave->start_date->year;
        $balance = LeaveBalance::forEmployee($leave->employee_id, $leave->leave_type_id, $year);
        $balance->increment('used', $leave->total_days);

        $leave->update([
            'status'          => 'approved_hr',
            'hr_approved_by'  => auth()->id(),
            'hr_approved_at'  => now(),
            'hr_notes'        => $request->notes,
        ]);

        return back()->with('success', 'Cuti disetujui. Saldo cuti karyawan telah dipotong.');
    }

    public function reject(Request $request, LeaveRequest $leave)
    {
        abort_unless(in_array($leave->status, ['submitted', 'approved_manager']), 422);
        $request->validate(['notes' => 'required|string|max:500']);

        // Jika sebelumnya sudah approved_manager lalu HR tolak, tidak ada saldo yang perlu dikembalikan
        $updateData = [
            'status'   => 'rejected',
            'hr_notes' => $request->notes,
        ];

        if ($leave->isSubmitted()) {
            $updateData['manager_notes'] = $request->notes;
            $updateData['manager_approved_by'] = auth()->id();
        } else {
            $updateData['hr_approved_by'] = auth()->id();
            $updateData['hr_approved_at'] = now();
        }

        $leave->update($updateData);

        return back()->with('success', 'Pengajuan cuti ditolak.');
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
}
