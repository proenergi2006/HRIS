<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class EmployeeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function index()
    {
        $employees = Employee::with('level')->orderBy('name')->get();
        return view('appraisal.employee.index', compact('employees'));
    }

    public function create()
    {
        $levels    = Level::orderBy('name')->get();
        $managers  = Employee::orderBy('name')->get();
        return view('appraisal.employee.edit', ['employee' => new Employee(), 'levels' => $levels, 'managers' => $managers]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Employee::create($data);
        return redirect()->route('appraisal.employees.index')->with('status', 'Karyawan berhasil ditambahkan.');
    }

    public function edit(Employee $employee)
    {
        $levels   = Level::orderBy('name')->get();
        $managers = Employee::where('id', '!=', $employee->id)->orderBy('name')->get();
        return view('appraisal.employee.edit', compact('employee', 'levels', 'managers'));
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $this->validated($request, $employee->id);
        $employee->update($data);
        return redirect()->route('appraisal.employees.index')->with('status', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        if ($employee->appraisals()->count() > 0) {
            return back()->with('error', 'Karyawan tidak bisa dihapus karena memiliki data penilaian.');
        }

        $employee->delete();
        return redirect()->route('appraisal.employees.index')->with('status', 'Karyawan berhasil dihapus.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'              => 'required|string|max:255',
            'nip'               => 'nullable|string|max:50|unique:employees,nip,' . ($ignoreId ?? 'NULL'),
            'level_id'          => 'nullable|exists:levels,id',
            'manager_id'        => 'nullable|exists:employees,id',
            'lob'               => 'nullable|string|max:100',
            'department'        => 'nullable|string|max:100',
            'position'          => 'nullable|string|max:100',
            'start_date'        => 'nullable|date',
            'contract_end_date' => 'nullable|date|required_if:employment_status,contract',
            'employment_status' => 'required|in:permanent,contract,probation',
            'is_active'         => 'boolean',
        ]);
    }
}
