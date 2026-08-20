<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeFamilyMember;
use Illuminate\Http\Request;

class EmployeeFamilyMemberController extends Controller
{
    public function store(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'relation'   => 'required|in:' . implode(',', array_keys(EmployeeFamilyMember::$relationLabels)),
            'birth_date' => 'nullable|date|before_or_equal:' . now()->format('Y-m-d'),
        ]);

        $employee->familyMembers()->create($data);

        return redirect()->route('appraisal.employees.edit', $employee)
            ->with('success', 'Anggota keluarga berhasil ditambahkan.');
    }

    public function destroy(Employee $employee, EmployeeFamilyMember $familyMember)
    {
        abort_if($familyMember->employee_id !== $employee->id, 404);

        $familyMember->delete();

        return redirect()->route('appraisal.employees.edit', $employee)
            ->with('success', 'Anggota keluarga berhasil dihapus.');
    }
}
