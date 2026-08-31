<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDataChangeRequest;
use App\Services\ApprovalEngine;
use Illuminate\Http\Request;

/**
 * Employee Administration — pengajuan perubahan data pribadi (PRD Bab 3 modul #7).
 * Self-service: user cuma bisa ajukan/lihat punya karyawan yang terhubung ke akunnya
 * sendiri, kecuali user itu punya izin employee-master.edit (HR) yang bisa lihat semua.
 */
class EmployeeDataChangeController extends Controller
{
    public function __construct(private ApprovalEngine $engine) {}

    public function index(Request $request)
    {
        $isHr = $request->user()->can('employee-master.edit');
        $query = EmployeeDataChangeRequest::with(['employee', 'requestedBy']);

        if (! $isHr) {
            $employee = $request->user()->employee;
            abort_unless($employee, 403, 'Akun Anda belum terhubung ke data karyawan.');
            $query->where('employee_id', $employee->id);
        }

        $requests = $query->latest()->get();

        return view('appraisal.employee.data-change.index', compact('requests', 'isHr'));
    }

    public function create(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun Anda belum terhubung ke data karyawan.');

        return view('appraisal.employee.data-change.form', compact('employee'));
    }

    public function store(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun Anda belum terhubung ke data karyawan.');

        $data = $request->validate([
            'field_key' => 'required|in:' . implode(',', array_keys(EmployeeDataChangeRequest::$fieldLabels)),
            'new_value' => 'required|string|max:1000',
            'reason'    => 'nullable|string|max:1000',
        ]);
        $data['employee_id']           = $employee->id;
        $data['requested_by_user_id']  = $request->user()->id;
        $data['old_value']             = $employee->{$data['field_key']};
        $data['status']                = 'draft';

        $changeRequest = EmployeeDataChangeRequest::create($data);

        return redirect()->route('appraisal.employee-data-changes.show', $changeRequest)
            ->with('success', 'Pengajuan perubahan data disimpan sebagai draft. Klik "Ajukan" untuk memulai persetujuan.');
    }

    public function submit(Request $request, EmployeeDataChangeRequest $employeeDataChange)
    {
        $this->authorizeOwn($request, $employeeDataChange);
        abort_unless($employeeDataChange->isDraft(), 422);

        $employeeDataChange->update(['status' => 'pending']);
        $this->engine->start($employeeDataChange);

        return redirect()->route('appraisal.employee-data-changes.index')
            ->with('success', 'Pengajuan diajukan. Menunggu persetujuan.');
    }

    public function show(Request $request, EmployeeDataChangeRequest $employeeDataChange)
    {
        $this->authorizeOwn($request, $employeeDataChange);
        $employeeDataChange->load(['employee', 'requestedBy', 'approvalRequest.steps.approver', 'approvalRequest.steps.actedBy']);

        return view('appraisal.employee.data-change.show', ['changeRequest' => $employeeDataChange]);
    }

    public function destroy(Request $request, EmployeeDataChangeRequest $employeeDataChange)
    {
        $this->authorizeOwn($request, $employeeDataChange);
        abort_unless(in_array($employeeDataChange->status, ['draft', 'rejected', 'cancelled']), 422);
        $employeeDataChange->delete();

        return redirect()->route('appraisal.employee-data-changes.index')->with('success', 'Pengajuan dihapus.');
    }

    private function authorizeOwn(Request $request, EmployeeDataChangeRequest $changeRequest): void
    {
        if ($request->user()->can('employee-master.edit')) {
            return;
        }

        abort_unless($changeRequest->employee_id === $request->user()->employee?->id, 403);
    }
}
