<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with('company')->withCount('employees')->orderBy('name')->get();
        $companies   = Company::where('is_active', true)->orderBy('name')->get();

        return view('appraisal.department.index', compact('departments', 'companies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'code'       => 'required|string|max:20|unique:departments,code',
            'name'       => 'required|string|max:150',
            'is_active'  => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        Department::create($data);

        return back()->with('status', 'Departemen berhasil ditambahkan.');
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'code'       => 'required|string|max:20|unique:departments,code,' . $department->id,
            'name'       => 'required|string|max:150',
            'is_active'  => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $department->update($data);

        return back()->with('status', 'Departemen berhasil diperbarui.');
    }

    public function destroy(Department $department)
    {
        if ($department->employees()->exists()) {
            return back()->with('error', 'Departemen tidak bisa dihapus karena masih dipakai karyawan.');
        }

        $department->delete();

        return back()->with('status', 'Departemen berhasil dihapus.');
    }
}
