<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Traits\LogsOrgChanges;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    use LogsOrgChanges;

    private array $logFields = ['company_id', 'name', 'code', 'head_employee_id', 'is_active'];

    public function index()
    {
        $branches = Branch::with(['company', 'head'])
            ->withCount(['employees', 'positions'])
            ->orderBy('company_id')->orderBy('name')
            ->get();

        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $employees = Employee::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('appraisal.branch.index', compact('branches', 'companies', 'employees'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $branch = Branch::create($data);
        $this->logOrgChange('branch', $branch, 'created', [], $request->date('effective_date'));

        return back()->with('status', 'Cabang berhasil ditambahkan.');
    }

    public function update(Request $request, Branch $branch)
    {
        $data     = $this->validated($request, $branch->id);
        $original = $branch->getOriginal();

        $branch->update($data);

        $diff = $this->diffOrgAttributes($branch, $original, $this->logFields);
        if ($diff) {
            $action = array_key_exists('company_id', $diff) ? 'moved' : 'updated';
            $this->logOrgChange('branch', $branch, $action, $diff, $request->date('effective_date'));
        }

        return back()->with('status', 'Cabang berhasil diperbarui.');
    }

    public function destroy(Branch $branch)
    {
        if ($branch->employees()->exists() || $branch->positions()->exists()) {
            return back()->with('error', 'Cabang tidak bisa dihapus karena masih dipakai karyawan/jabatan.');
        }

        $this->logOrgChange('branch', $branch, 'deleted');
        $branch->delete();

        return back()->with('status', 'Cabang berhasil dihapus.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'company_id'       => 'required|exists:companies,id',
            'code'             => 'required|string|max:20',
            'name'             => 'required|string|max:100|unique:branches,name,'
                . ($ignoreId ?? 'NULL') . ',id,company_id,' . $request->input('company_id'),
            'head_employee_id' => 'nullable|exists:employees,id',
            'is_active'        => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
