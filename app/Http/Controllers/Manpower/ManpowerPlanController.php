<?php

namespace App\Http\Controllers\Manpower;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\ManpowerPlan;
use App\Models\Position;
use App\Models\Section;
use App\Services\ApprovalEngine;
use Illuminate\Http\Request;

/**
 * Manpower Planning (PRD Bab 3 modul #2) — rencana headcount vs actual per company/unit/
 * posisi per periode, lewat Approval Engine yang sama seperti modul lain.
 */
class ManpowerPlanController extends Controller
{
    public function __construct(private ApprovalEngine $engine) {}

    public function index(Request $request)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $companyId = $request->get('company_id', $companies->first()?->id);
        $year      = (int) $request->get('year', now()->year);

        $plans = ManpowerPlan::with(['company', 'department', 'section', 'position', 'requestedBy'])
            ->where('company_id', $companyId)
            ->where('year', $year)
            ->orderByDesc('id')
            ->get();

        // Total konsolidasi grup (semua company) untuk tahun yang sama — PRD Bab 10.
        $groupPlanned = ManpowerPlan::where('year', $year)->where('status', 'approved')->sum('planned_headcount');
        $groupActual  = \App\Models\Employee::where('is_active', true)->count();

        return view('manpower.plan.index', compact('plans', 'companies', 'companyId', 'year', 'groupPlanned', 'groupActual'));
    }

    public function create(Request $request)
    {
        return view('manpower.plan.form', $this->formData(new ManpowerPlan(), $request));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['requested_by_user_id'] = auth()->id();
        $data['status'] = 'draft';

        $plan = ManpowerPlan::create($data);

        return redirect()->route('manpower.plans.edit', $plan)
            ->with('success', 'Rencana manpower disimpan sebagai draft. Klik "Ajukan" untuk memulai persetujuan.');
    }

    public function edit(ManpowerPlan $plan)
    {
        return view('manpower.plan.form', $this->formData($plan, request()));
    }

    public function update(Request $request, ManpowerPlan $plan)
    {
        abort_unless($plan->isEditable(), 422, 'Rencana yang sedang diajukan/disetujui tidak bisa diubah.');

        $plan->update($this->validated($request));

        return back()->with('success', 'Rencana manpower diperbarui.');
    }

    public function submit(ManpowerPlan $plan)
    {
        abort_unless($plan->isDraft(), 422);

        $plan->update(['status' => 'pending']);
        $this->engine->start($plan);

        return redirect()->route('manpower.plans.index', ['company_id' => $plan->company_id, 'year' => $plan->year])
            ->with('success', 'Rencana manpower diajukan. Menunggu persetujuan.');
    }

    public function show(ManpowerPlan $plan)
    {
        $plan->load([
            'company', 'department', 'section', 'position', 'requestedBy',
            'approvalRequest.steps.approver', 'approvalRequest.steps.actedBy',
            'requisitions' => fn ($q) => $q->with('requestedBy')->orderByDesc('id'),
        ]);

        return view('manpower.plan.show', compact('plan'));
    }

    public function destroy(ManpowerPlan $plan)
    {
        abort_unless(in_array($plan->status, ['draft', 'rejected', 'cancelled']), 422);
        $plan->delete();

        return redirect()->route('manpower.plans.index')->with('success', 'Rencana manpower dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'company_id'        => 'required|exists:companies,id',
            'department_id'     => 'nullable|exists:departments,id',
            'section_id'        => 'nullable|exists:sections,id',
            'position_id'       => 'nullable|exists:positions,id',
            'year'              => 'required|integer|min:2020|max:2100',
            'month'             => 'nullable|integer|between:1,12',
            'planned_headcount' => 'required|integer|min:0',
            'notes'             => 'nullable|string|max:1000',
        ]);
    }

    private function formData(ManpowerPlan $plan, Request $request): array
    {
        return [
            'plan'        => $plan,
            'companies'   => Company::where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'sections'    => Section::with('department')->where('is_active', true)->orderBy('name')->get(),
            'positions'   => Position::where('is_active', true)->orderBy('name')->get(),
        ];
    }
}
