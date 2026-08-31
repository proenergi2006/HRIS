<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Division;
use App\Models\Employee;
use App\Traits\LogsOrgChanges;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    use LogsOrgChanges;

    private array $logFields = ['company_id', 'name', 'code', 'head_employee_id', 'cost_center', 'is_active'];

    public function index()
    {
        $divisions = Division::with(['company', 'head'])
            ->withCount(['departments', 'employees'])
            ->orderBy('company_id')->orderBy('name')
            ->get();

        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $employees = Employee::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('appraisal.division.index', compact('divisions', 'companies', 'employees'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $division = Division::create($data);
        $this->logOrgChange('division', $division, 'created', [], $request->date('effective_date'));

        return back()->with('status', 'Divisi berhasil ditambahkan.');
    }

    public function update(Request $request, Division $division)
    {
        $data     = $this->validated($request, $division->id);
        $original = $division->getOriginal();

        $division->update($data);

        $diff = $this->diffOrgAttributes($division, $original, $this->logFields);
        if ($diff) {
            $action = array_key_exists('company_id', $diff) ? 'moved' : 'updated';
            $this->logOrgChange('division', $division, $action, $diff, $request->date('effective_date'));
        }

        return back()->with('status', 'Divisi berhasil diperbarui.');
    }

    public function destroy(Division $division)
    {
        if ($division->departments()->exists() || $division->employees()->exists()) {
            return back()->with('error', 'Divisi tidak bisa dihapus karena masih dipakai departemen/karyawan.');
        }

        $this->logOrgChange('division', $division, 'deleted');
        $division->delete();

        return back()->with('status', 'Divisi berhasil dihapus.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'company_id'       => 'required|exists:companies,id',
            'code'             => 'required|string|max:20|unique:divisions,code' . ($ignoreId ? ',' . $ignoreId : ''),
            'name'             => 'required|string|max:150',
            'head_employee_id' => 'nullable|exists:employees,id',
            'cost_center'      => 'nullable|string|max:50',
            'is_active'        => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
