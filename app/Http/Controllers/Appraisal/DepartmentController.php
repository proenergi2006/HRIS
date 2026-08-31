<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Traits\LogsOrgChanges;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    use LogsOrgChanges;

    private array $logFields = ['company_id', 'division_id', 'name', 'code', 'head_employee_id', 'cost_center', 'is_active'];

    public function index()
    {
        $departments = Department::with(['company', 'division', 'head'])
            ->withCount('employees')->orderBy('name')->get();
        $companies   = Company::where('is_active', true)->orderBy('name')->get();
        $divisions   = Division::with('company')->where('is_active', true)->orderBy('name')->get();
        $employees   = Employee::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('appraisal.department.index', compact('departments', 'companies', 'divisions', 'employees'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $department = Department::create($data);
        $this->logOrgChange('department', $department, 'created', [], $request->date('effective_date'));

        return back()->with('status', 'Departemen berhasil ditambahkan.');
    }

    public function update(Request $request, Department $department)
    {
        $data     = $this->validated($request, $department->id);
        $original = $department->getOriginal();

        $department->update($data);

        $diff = $this->diffOrgAttributes($department, $original, $this->logFields);
        if ($diff) {
            $action = array_intersect(['company_id', 'division_id'], array_keys($diff)) ? 'moved' : 'updated';
            $this->logOrgChange('department', $department, $action, $diff, $request->date('effective_date'));
        }

        return back()->with('status', 'Departemen berhasil diperbarui.');
    }

    /**
     * Pindah departemen ke divisi lain (dipakai drag & drop di bagan organisasi).
     * Payload minimal — cuma division_id — dan tetap tercatat 'moved' di org_change_logs.
     */
    public function reparent(Request $request, Department $department)
    {
        $data = $request->validate([
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        $original = $department->getOriginal();
        $department->update(['division_id' => $data['division_id'] ?: null]);

        $diff = $this->diffOrgAttributes($department, $original, $this->logFields);
        if ($diff) {
            $this->logOrgChange('department', $department, 'moved', $diff);
        }

        return response()->json(['ok' => true]);
    }

    public function destroy(Department $department)
    {
        if ($department->employees()->exists()) {
            return back()->with('error', 'Departemen tidak bisa dihapus karena masih dipakai karyawan.');
        }

        if ($department->sections()->exists()) {
            return back()->with('error', 'Departemen tidak bisa dihapus karena masih punya section.');
        }

        $this->logOrgChange('department', $department, 'deleted');
        $department->delete();

        return back()->with('status', 'Departemen berhasil dihapus.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'company_id'       => 'nullable|exists:companies,id',
            'division_id'      => 'nullable|exists:divisions,id',
            'code'             => 'required|string|max:20|unique:departments,code' . ($ignoreId ? ',' . $ignoreId : ''),
            'name'             => 'required|string|max:150',
            'head_employee_id' => 'nullable|exists:employees,id',
            'cost_center'      => 'nullable|string|max:50',
            'is_active'        => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
