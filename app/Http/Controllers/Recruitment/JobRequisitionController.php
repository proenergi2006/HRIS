<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\JobRequisition;
use App\Models\Master\EmployeeType;
use App\Models\Position;
use App\Models\Section;
use App\Services\ApprovalEngine;
use Illuminate\Http\Request;

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

        $requisitions = JobRequisition::with(['company', 'department', 'position', 'requestedBy'])
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
        return $request->validate([
            'company_id'          => 'required|exists:companies,id',
            'department_id'       => 'nullable|exists:departments,id',
            'section_id'          => 'nullable|exists:sections,id',
            'position_id'         => 'nullable|exists:positions,id',
            'title'               => 'required|string|max:150',
            'reason'              => 'nullable|string|max:2000',
            'headcount_requested' => 'required|integer|min:1',
            'employment_type_id'  => 'nullable|exists:employee_types,id',
            'target_join_date'    => 'nullable|date',
        ]);
    }

    private function formData(JobRequisition $requisition): array
    {
        return [
            'requisition'    => $requisition,
            'companies'      => Company::where('is_active', true)->orderBy('name')->get(),
            'departments'    => Department::where('is_active', true)->orderBy('name')->get(),
            'sections'       => Section::with('department')->where('is_active', true)->orderBy('name')->get(),
            'positions'      => Position::where('is_active', true)->orderBy('name')->get(),
            'employmentTypes'=> EmployeeType::where('is_active', true)->orderBy('name')->get(),
        ];
    }
}
