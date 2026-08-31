<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeNssfController extends Controller
{
    // NSSF (BPJS) — 1 baris per karyawan, jadi upsert.
    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'health_number'          => 'nullable|string|max:50',
            'health_join_date'       => 'nullable|date',
            'employment_number'      => 'nullable|string|max:50',
            'employment_join_date'   => 'nullable|date',
        ]);

        $data['health_registered']     = $request->boolean('health_registered');
        $data['employment_registered'] = $request->boolean('employment_registered');

        $employee->nssf()->updateOrCreate(['employee_id' => $employee->id], $data);

        return redirect(route('appraisal.employees.edit', $employee) . '#tab-nssf')
            ->with('success', 'Data BPJS berhasil disimpan.');
    }
}
