<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\OvertimeRequest;
use App\Services\ApprovalEngine;
use Illuminate\Http\Request;

/**
 * Pengajuan Lembur self-service lewat Approval Engine — mengisi gap transaction
 * type 'overtime_request' yang sudah ada di ApprovalWorkflowSeeder sejak awal
 * tapi belum ada modelnya (lihat migrasi 2026_08_28_101101).
 */
class OvertimeRequestController extends Controller
{
    public function __construct(private ApprovalEngine $engine) {}

    public function index(Request $request)
    {
        $isHr = $request->user()->can('overtime.view');
        $query = OvertimeRequest::with(['employee', 'requestedBy']);

        if (! $isHr) {
            $employee = $request->user()->employee;
            abort_unless($employee, 403, 'Akun Anda belum terhubung ke data karyawan.');
            $query->where('employee_id', $employee->id);
        }

        $requests = $query->latest('date')->get();

        return view('hr.overtime-request.index', compact('requests', 'isHr'));
    }

    public function create(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun Anda belum terhubung ke data karyawan.');

        return view('hr.overtime-request.form', compact('employee'));
    }

    public function store(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun Anda belum terhubung ke data karyawan.');

        $data = $request->validate([
            'date'          => 'required|date',
            'planned_hours' => 'required|numeric|min:0.5|max:24',
            'reason'        => 'nullable|string|max:1000',
        ]);
        $data['employee_id']          = $employee->id;
        $data['company_id']           = $employee->company_id;
        $data['requested_by_user_id'] = $request->user()->id;
        $data['status']               = 'draft';

        $overtimeRequest = OvertimeRequest::create($data);

        return redirect()->route('hr.overtime-requests.show', $overtimeRequest)
            ->with('success', 'Pengajuan lembur disimpan sebagai draft. Klik "Ajukan" untuk memulai persetujuan.');
    }

    public function submit(Request $request, OvertimeRequest $overtimeRequest)
    {
        $this->authorizeOwn($request, $overtimeRequest);
        abort_unless($overtimeRequest->isDraft(), 422);

        $overtimeRequest->update(['status' => 'pending']);
        $this->engine->start($overtimeRequest);

        return redirect()->route('hr.overtime-requests.index')
            ->with('success', 'Pengajuan lembur diajukan. Menunggu persetujuan.');
    }

    public function show(Request $request, OvertimeRequest $overtimeRequest)
    {
        $this->authorizeOwn($request, $overtimeRequest);
        $overtimeRequest->load(['employee', 'requestedBy', 'approvalRequest.steps.approver', 'approvalRequest.steps.actedBy']);

        return view('hr.overtime-request.show', compact('overtimeRequest'));
    }

    public function destroy(Request $request, OvertimeRequest $overtimeRequest)
    {
        $this->authorizeOwn($request, $overtimeRequest);
        abort_unless(in_array($overtimeRequest->status, ['draft', 'rejected', 'cancelled']), 422);
        $overtimeRequest->delete();

        return redirect()->route('hr.overtime-requests.index')->with('success', 'Pengajuan lembur dihapus.');
    }

    private function authorizeOwn(Request $request, OvertimeRequest $overtimeRequest): void
    {
        if ($request->user()->can('overtime.view')) {
            return;
        }

        abort_unless($overtimeRequest->employee_id === $request->user()->employee?->id, 403);
    }
}
