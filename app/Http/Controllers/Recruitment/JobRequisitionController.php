<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobRequisition;
use App\Models\ManpowerPlan;
use App\Models\Master\EmployeeType;
use App\Models\Position;
use App\Models\Section;
use App\Services\ApprovalEngine;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Job Requisition — awal alur Recruitment (PRD Bab 3 modul #3). Lewat Approval Engine.
 */
class JobRequisitionController extends Controller
{
    public function __construct(private ApprovalEngine $engine) {}

    public function index(Request $request)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $companyId = $request->get('company_id', $companies->first()?->id);

        $requisitions = JobRequisition::with(['company', 'department', 'position', 'requestedBy', 'manpowerPlan'])
            ->withCount('candidates')
            ->where('company_id', $companyId)
            ->orderByDesc('id')->get();

        return view('recruitment.requisition.index', compact('requisitions', 'companies', 'companyId'));
    }

    public function create()
    {
        return view('recruitment.requisition.form', $this->formData(new JobRequisition()));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['requested_by_user_id'] = auth()->id();
        $data['status'] = 'draft';

        $requisition = JobRequisition::create($data);

        return redirect()->route('recruitment.requisitions.edit', $requisition)
            ->with('success', 'Requisition disimpan sebagai draft. Klik "Ajukan" untuk memulai persetujuan.');
    }

    public function edit(JobRequisition $requisition)
    {
        return view('recruitment.requisition.form', $this->formData($requisition));
    }

    public function update(Request $request, JobRequisition $requisition)
    {
        abort_unless($requisition->isEditable(), 422, 'Requisition yang sedang diajukan/disetujui tidak bisa diubah.');

        $requisition->update($this->validated($request));

        return back()->with('success', 'Requisition diperbarui.');
    }

    public function submit(JobRequisition $requisition)
    {
        abort_unless($requisition->isDraft(), 422);

        // Budget Control — permintaan penambahan headcount wajib muat di kuota MPP disetujui.
        if ($violation = $requisition->budgetViolation()) {
            return back()->with('error', 'Budget Control: ' . $violation);
        }

        $requisition->update(['status' => 'pending']);
        $this->engine->start($requisition);

        return redirect()->route('recruitment.requisitions.index', ['company_id' => $requisition->company_id])
            ->with('success', 'Requisition diajukan. Menunggu persetujuan.');
    }

    public function show(JobRequisition $requisition)
    {
        $requisition->load([
            'company', 'department', 'section', 'position', 'employmentType', 'requestedBy',
            'candidates', 'approvalRequest.steps.approver', 'approvalRequest.steps.actedBy',
        ]);

        return view('recruitment.requisition.show', compact('requisition'));
    }

    public function destroy(JobRequisition $requisition)
    {
        abort_unless(in_array($requisition->status, ['draft', 'rejected', 'cancelled']), 422);
        $requisition->delete();

        return redirect()->route('recruitment.requisitions.index')->with('success', 'Requisition dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'company_id'          => 'required|exists:companies,id',
            'department_id'       => 'nullable|exists:departments,id',
            'section_id'          => 'nullable|exists:sections,id',
            'position_id'         => 'nullable|exists:positions,id',
            'title'               => 'required|string|max:150',
            'request_type'        => 'required|in:replacement,additional,new_position',
            'manpower_plan_id'    => 'nullable|exists:manpower_plans,id|required_if:request_type,additional,new_position',
            'replaces_employee_id'=> 'nullable|exists:employees,id',
            'reason'              => 'nullable|string|max:2000',
            'headcount_requested' => 'required|integer|min:1',
            'employment_type_id'  => 'nullable|exists:employee_types,id',
            'target_join_date'    => 'nullable|date',
        ], [
            'manpower_plan_id.required_if' => 'Permintaan Tambahan / Posisi Baru wajib ditautkan ke Rencana Manpower.',
        ]);

        if ($data['request_type'] === 'replacement') {
            $data['manpower_plan_id'] = null;
        } else {
            $data['replaces_employee_id'] = null;

            // MPP harus milik company yang sama.
            $plan = ManpowerPlan::find($data['manpower_plan_id']);
            if ($plan && $plan->company_id != $data['company_id']) {
                throw ValidationException::withMessages([
                    'manpower_plan_id' => 'Rencana Manpower yang dipilih bukan milik perusahaan ini.',
                ]);
            }
        }

        return $data;
    }

    private function formData(JobRequisition $requisition): array
    {
        $companyId = $requisition->company_id ?? Company::where('is_active', true)->orderBy('name')->value('id');

        return [
            'requisition'    => $requisition,
            'companies'      => Company::where('is_active', true)->orderBy('name')->get(),
            'departments'    => Department::where('is_active', true)->orderBy('name')->get(),
            'sections'       => Section::with('department')->where('is_active', true)->orderBy('name')->get(),
            'positions'      => Position::where('is_active', true)->orderBy('name')->get(),
            'employmentTypes'=> EmployeeType::where('is_active', true)->orderBy('name')->get(),
            'manpowerPlans'  => ManpowerPlan::with(['department', 'section', 'position'])
                                   ->where('status', 'approved')
                                   ->orderByDesc('year')->orderByDesc('id')->get(),
            'replaceableEmployees' => Employee::where('is_active', true)
                                   ->when($companyId, fn ($q, $id) => $q->where('company_id', $id))
                                   ->orderBy('name')->get(['id', 'name', 'company_id']),
        ];
    }
}
