<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Appraisal\CompanyObjective;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;

/** OKR — Sasaran Perusahaan/Departemen berjenjang (PRD gap: performance maturity / goal cascading). */
class CompanyObjectiveController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $companyId = (int) $request->get('company_id', $companies->first()?->id);
        $year      = (int) $request->get('year', now()->year);

        $objectives = CompanyObjective::with(['department', 'owner', 'children.department', 'children.owner'])
            ->where('company_id', $companyId)->where('year', $year)
            ->whereNull('parent_objective_id')
            ->orderBy('title')->get();

        return view('appraisal.okr.index', compact('companies', 'companyId', 'year', 'objectives'));
    }

    public function create(Request $request)
    {
        return view('appraisal.okr.form', $this->formData(new CompanyObjective(), $request));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by_user_id'] = $request->user()->id;
        $objective = CompanyObjective::create($data);

        return redirect()->route('appraisal.okr.index', ['company_id' => $objective->company_id, 'year' => $objective->year])
            ->with('success', 'Sasaran ditambahkan.');
    }

    public function edit(CompanyObjective $okr)
    {
        return view('appraisal.okr.form', $this->formData($okr, request()));
    }

    public function update(Request $request, CompanyObjective $okr)
    {
        $data = $this->validated($request, $okr);
        $okr->update($data);

        return redirect()->route('appraisal.okr.index', ['company_id' => $okr->company_id, 'year' => $okr->year])
            ->with('success', 'Sasaran diperbarui.');
    }

    public function destroy(CompanyObjective $okr)
    {
        $companyId = $okr->company_id; $year = $okr->year;
        $okr->delete();

        return redirect()->route('appraisal.okr.index', ['company_id' => $companyId, 'year' => $year])
            ->with('success', 'Sasaran dihapus.');
    }

    private function validated(Request $request, ?CompanyObjective $okr = null): array
    {
        return $request->validate([
            'company_id'          => 'required|exists:companies,id',
            'department_id'       => 'nullable|exists:departments,id',
            'parent_objective_id' => ['nullable', 'exists:company_objectives,id', function ($attr, $value, $fail) use ($okr) {
                if ($okr && $value == $okr->id) {
                    $fail('Sasaran tidak bisa jadi induk dari dirinya sendiri.');
                }
            }],
            'title'             => 'required|string|max:200',
            'description'       => 'nullable|string|max:2000',
            'year'              => 'required|integer|min:2020|max:2100',
            'quarter'           => 'nullable|integer|between:1,4',
            'owner_employee_id' => 'nullable|exists:employees,id',
            'status'            => 'required|in:active,completed,cancelled',
        ]);
    }

    private function formData(CompanyObjective $okr, Request $request): array
    {
        $companyId = $okr->company_id ?? (int) $request->get('company_id');
        $year      = $okr->year ?? (int) $request->get('year', now()->year);

        return [
            'okr'         => $okr,
            'companies'   => Company::where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'employees'   => Employee::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'parents'     => CompanyObjective::where('company_id', $companyId)->where('year', $year)
                                ->when($okr->id, fn ($q, $id) => $q->where('id', '!=', $id))
                                ->orderBy('title')->get(),
            'defaultCompanyId' => $companyId,
            'defaultYear'      => $year,
        ];
    }
}
