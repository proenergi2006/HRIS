<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Division;
use App\Models\Level;
use App\Models\Position;
use App\Models\Section;
use App\Models\Master\BloodType;
use App\Models\Master\EmployeeType;
use App\Models\Master\MaritalStatus;
use App\Models\Master\Province;
use App\Models\Master\Religion;
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
            'branches'    => Branch::where('is_active', true)->orderBy('name')->get(['id', 'name', 'company_id']),
            'divisions'   => Division::where('is_active', true)->orderBy('name')->get(['id', 'name', 'company_id']),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(['id', 'name', 'company_id', 'division_id']),
            'sections'    => Section::where('is_active', true)->orderBy('name')->get(['id', 'name', 'department_id']),
            'positions'   => Position::where('is_active', true)->orderBy('name')->get(),
            'levels'      => Level::orderBy('rank')->orderBy('name')->get(),
            'managers'    => Employee::when($employee?->id, fn ($q) => $q->where('id', '!=', $employee->id))
                ->orderBy('name')->get(),
            'religions'       => Religion::active()->ordered()->get(),
            'maritalStatuses' => MaritalStatus::active()->ordered()->get(),
            'bloodTypes'      => BloodType::active()->ordered()->get(),
            'employeeTypes'   => EmployeeType::active()->ordered()->get(),
            'provinces'       => Province::orderBy('name')->get(['id', 'name']),
            'cities'          => \App\Models\Master\City::orderBy('name')->get(['id', 'name', 'province_id']),
            'educationLevels' => \App\Models\Master\EducationLevel::active()->ordered()->get(),
            'educationMajors' => \App\Models\Master\EducationMajor::active()->ordered()->get(),
            'banks'           => \App\Models\Master\Bank::active()->ordered()->get(),
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
        $data = $request->validate([
            // Employee Information
            'company_id'                 => 'nullable|exists:companies,id',
            'branch_id'                  => 'nullable|exists:branches,id',
            'name'                       => 'required|string|max:255',
            'nip'                        => 'nullable|string|max:50|unique:employees,nip,' . ($ignoreId ?? 'NULL'),
            'level_id'                   => 'nullable|exists:levels,id',
            'manager_id'                 => 'nullable|exists:employees,id',
            'division_id'                => 'nullable|exists:divisions,id',
            'department_id'              => 'nullable|exists:departments,id',
            'section_id'                 => 'nullable|exists:sections,id',
            'position_id'                => 'nullable|exists:positions,id',
            'lob'                        => 'nullable|string|max:100',
            'start_date'                 => 'nullable|date',
            'contract_end_date'          => 'nullable|date|required_if:employment_status,contract',
            'employment_status'          => 'required|in:permanent,contract,probation,mitra',
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
            'marital_status_id'          => 'nullable|exists:marital_statuses,id',
            'religion_id'                => 'nullable|exists:religions,id',
            'blood_type_id'              => 'nullable|exists:blood_types,id',
            'employee_type_id'           => 'nullable|exists:employee_types,id',
            'employee_type'              => 'required|in:local,expat',
            'finger_id'                  => 'nullable|string|max:30',

            // Email & Phone
            'email'                      => 'nullable|email|max:150',
            'phone'                      => 'nullable|string|max:30',
            'home_phone'                 => 'nullable|string|max:30',

            // Alamat Domisili
            'domicile_address'           => 'nullable|string|max:1000',
            'domicile_province_id'       => 'nullable|exists:provinces,id',
            'domicile_city_id'           => 'nullable|exists:cities,id',
            'domicile_district_id'       => 'nullable|exists:districts,id',
            'domicile_district'          => 'nullable|string|max:100',
            'domicile_village_id'        => 'nullable|exists:villages,id',
            'domicile_subdistrict'       => 'nullable|string|max:100',

            // Alamat KTP
            'ktp_address'                => 'nullable|string|max:1000',
            'ktp_province_id'            => 'nullable|exists:provinces,id',
            'ktp_city_id'                => 'nullable|exists:cities,id',
            'ktp_district_id'            => 'nullable|exists:districts,id',
            'ktp_district'               => 'nullable|string|max:100',
            'ktp_village_id'             => 'nullable|exists:villages,id',
            'ktp_subdistrict'            => 'nullable|string|max:100',

            // Kontak Darurat
            'emergency_contact_name'     => 'nullable|string|max:150',
            'emergency_contact_relation' => 'nullable|string|max:100',
            'emergency_contact_phone'    => 'nullable|string|max:30',
        ]);

        // Sinkronkan kolom teks &lt;-&gt; FK master kecamatan/kelurahan supaya laporan/PDF
        // yang baca string tetap benar, dan sebaliknya (isian manual dicoba di-match).
        $this->syncRegionText($data, 'domicile');
        $this->syncRegionText($data, 'ktp');

        // employees.branch (string) masih dibaca slip gaji/THR/bonus (lihat
        // resources/views/hr/*/slip-pdf.blade.php) — sinkronkan dari branch_id
        // (dropdown master, sumber kebenaran baru) supaya slip tetap tampil benar.
        if (array_key_exists('branch_id', $data)) {
            $data['branch'] = $data['branch_id']
                ? \App\Models\Branch::find($data['branch_id'])?->name
                : null;
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    private function syncRegionText(array &$data, string $prefix): void
    {
        if (! empty($data["{$prefix}_district_id"])) {
            $data["{$prefix}_district"] = \App\Models\Master\District::whereKey($data["{$prefix}_district_id"])->value('name');
        } elseif (! empty($data["{$prefix}_district"])) {
            $data["{$prefix}_district_id"] = \App\Models\Master\District::when(
                ! empty($data["{$prefix}_city_id"]),
                fn ($q) => $q->where('city_id', $data["{$prefix}_city_id"])
            )->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($data["{$prefix}_district"]))])->value('id');
        }

        if (! empty($data["{$prefix}_village_id"])) {
            $data["{$prefix}_subdistrict"] = \App\Models\Master\Village::whereKey($data["{$prefix}_village_id"])->value('name');
        } elseif (! empty($data["{$prefix}_subdistrict"])) {
            $data["{$prefix}_village_id"] = \App\Models\Master\Village::when(
                ! empty($data["{$prefix}_district_id"]),
                fn ($q) => $q->where('district_id', $data["{$prefix}_district_id"])
            )->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($data["{$prefix}_subdistrict"]))])->value('id');
        }
    }
}
