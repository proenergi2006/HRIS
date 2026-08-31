<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\JobRequisition;
use App\Models\RecruitmentCost;
use Illuminate\Http\Request;

/** Biaya rekrutmen — input untuk metrik cost-per-hire di HR Analytics (Fase 2 HRD). */
class RecruitmentCostController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $companyId = $request->get('company_id', $companies->first()?->id);

        $costs = RecruitmentCost::with(['jobRequisition', 'createdBy'])
            ->where('company_id', $companyId)->latest('incurred_on')->get();

        $requisitions = JobRequisition::where('company_id', $companyId)->orderByDesc('id')->get();

        return view('recruitment.costs.index', compact('companies', 'companyId', 'costs', 'requisitions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id'          => 'required|exists:companies,id',
            'job_requisition_id'  => 'nullable|exists:job_requisitions,id',
            'category'             => 'required|in:' . implode(',', array_keys(RecruitmentCost::$categoryLabels)),
            'amount'                => 'required|integer|min:0',
            'incurred_on'           => 'required|date',
            'notes'                 => 'nullable|string|max:255',
        ]);
        $data['created_by'] = auth()->id();

        RecruitmentCost::create($data);

        return back()->with('status', 'Biaya rekrutmen dicatat.');
    }

    public function destroy(RecruitmentCost $recruitmentCost)
    {
        $recruitmentCost->delete();

        return back()->with('status', 'Biaya rekrutmen dihapus.');
    }
}
