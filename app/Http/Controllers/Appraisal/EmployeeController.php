<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Company;
use App\Models\Department;
use App\Models\Level;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function index(Request $request)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $query     = Employee::with(['level', 'company', 'department', 'position'])->orderBy('name');

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $employees = $query->get();
        return view('appraisal.employee.index', compact('employees', 'companies'));
    }

    public function create()
    {
        return view('appraisal.employee.edit', [
            'employee' => new Employee(),
        ] + $this->formOptions());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        unset($data['photo']);
        $data['photo'] = $this->handlePhoto($request);

        Employee::create($data);
        return redirect()->route('appraisal.employees.index')->with('status', 'Karyawan berhasil ditambahkan.');
    }

    public function edit(Employee $employee)
    {
        return view('appraisal.employee.edit', ['employee' => $employee] + $this->formOptions($employee));
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $this->validated($request, $employee->id);
        unset($data['photo']);

        $newPhoto = $this->handlePhoto($request);
        if ($newPhoto) {
            if ($employee->photo) {
                Storage::disk('local')->delete($employee->photo);
            }
            $data['photo'] = $newPhoto;
        }

        $employee->update($data);
        return redirect()->route('appraisal.employees.index')->with('status', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        if ($employee->appraisals()->count() > 0) {
            return back()->with('error', 'Karyawan tidak bisa dihapus karena memiliki data penilaian.');
        }

        if ($employee->photo) {
            Storage::disk('local')->delete($employee->photo);
        }

        $employee->delete();
        return redirect()->route('appraisal.employees.index')->with('status', 'Karyawan berhasil dihapus.');
    }

    public function photo(Employee $employee)
    {
        abort_unless($employee->photo && Storage::disk('local')->exists($employee->photo), 404);
        return Storage::disk('local')->response($employee->photo);
    }

    private function formOptions(?Employee $employee = null): array
    {
        return [
            'companies'   => Company::where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'positions'   => Position::where('is_active', true)->orderBy('name')->get(),
            'levels'      => Level::orderBy('name')->get(),
            'managers'    => Employee::when($employee?->id, fn ($q) => $q->where('id', '!=', $employee->id))
                ->orderBy('name')->get(),
        ];
    }

    private function handlePhoto(Request $request): ?string
    {
        if (! $request->hasFile('photo')) {
            return null;
        }

        return $request->file('photo')->store('employee-photos', 'local');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            // Employee Information
            'company_id'                 => 'nullable|exists:companies,id',
            'branch'                     => 'nullable|string|max:50',
            'name'                       => 'required|string|max:255',
            'nip'                        => 'nullable|string|max:50|unique:employees,nip,' . ($ignoreId ?? 'NULL'),
            'level_id'                   => 'nullable|exists:levels,id',
            'manager_id'                 => 'nullable|exists:employees,id',
            'department_id'              => 'nullable|exists:departments,id',
            'position_id'                => 'nullable|exists:positions,id',
            'lob'                        => 'nullable|string|max:100',
            'start_date'                 => 'nullable|date',
            'contract_end_date'          => 'nullable|date|required_if:employment_status,contract',
            'employment_status'          => 'required|in:permanent,contract,probation',
            'is_active'                  => 'boolean',
            'photo'                      => 'nullable|image|max:4096',

            // Personal
            'gender'                     => 'nullable|in:L,P',
            'birth_place'                => 'nullable|string|max:100',
            'birth_date'                 => 'nullable|date',
            'ktp_number'                 => 'nullable|string|max:30',
            'npwp_number'                => 'nullable|string|max:30',
            'npwp_city'                  => 'nullable|string|max:100',
            'npwp_date'                  => 'nullable|date',
            'marital_status'             => 'nullable|in:belum_kawin,kawin,cerai_hidup,cerai_mati',
            'religion'                   => 'nullable|string|max:30',
            'blood_type'                 => 'nullable|in:A,B,AB,O',
            'employee_type'              => 'required|in:local,expat',
            'finger_id'                  => 'nullable|string|max:30',

            // Email & Phone
            'email'                      => 'nullable|email|max:150',
            'phone'                      => 'nullable|string|max:30',
            'home_phone'                 => 'nullable|string|max:30',

            // Alamat Domisili
            'domicile_address'           => 'nullable|string|max:1000',
            'domicile_city'              => 'nullable|string|max:100',
            'domicile_district'          => 'nullable|string|max:100',
            'domicile_subdistrict'       => 'nullable|string|max:100',

            // Alamat KTP
            'ktp_address'                => 'nullable|string|max:1000',
            'ktp_city'                   => 'nullable|string|max:100',
            'ktp_district'               => 'nullable|string|max:100',
            'ktp_subdistrict'            => 'nullable|string|max:100',

            // Kontak Darurat
            'emergency_contact_name'     => 'nullable|string|max:150',
            'emergency_contact_relation' => 'nullable|string|max:100',
            'emergency_contact_phone'    => 'nullable|string|max:30',
        ]);
    }
}
