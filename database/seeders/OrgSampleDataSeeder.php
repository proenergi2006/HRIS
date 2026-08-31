<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\HR\EmployeeSalaryComponent;
use App\Models\HR\LeaveBalance;
use App\Models\HR\LeaveType;
use App\Models\HR\SalaryComponent;
use App\Models\Level;
use App\Models\Master\BloodType;
use App\Models\Master\City;
use App\Models\Master\EmployeeType;
use App\Models\Master\MaritalStatus;
use App\Models\Master\Religion;
use App\Models\Position;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Sample data organisasi lengkap: 6 departemen (Logistik, IT, HR & GA,
 * Procurement, Commercial, Finance) x jenjang Staff -> Senior Staff -> SPV ->
 * Manager, lengkap dgn reporting chain (manager_id), posisi + tunjangan
 * jabatan, gaji pokok, saldo cuti, dan login (email/password "password")
 * untuk level SPV ke atas supaya bisa dites sebagai approver "atasan langsung".
 *
 * Departemen "IT" reuse yang sudah ada dari ITDemoSeeder — 2 posisi SPV
 * existing (Andi Saputra, Candra Wijaya) ditautkan sebagai bawahan Manager IT
 * yang baru dibuat di sini, bukan diduplikasi.
 *
 * TIDAK didaftarkan di DatabaseSeeder default (data demo besar, opt-in) —
 * jalankan manual: php artisan db:seed --class=OrgSampleDataSeeder
 */
class OrgSampleDataSeeder extends Seeder
{
    private array $maleNames = [
        'Rian Hidayat', 'Bayu Kurniawan', 'Doni Saputra', 'Eko Prasetyo', 'Fajar Ramadhan',
        'Gilang Nugroho', 'Hadi Firmansyah', 'Irwan Setiawan', 'Joko Wibowo', 'Kurniadi Santoso',
        'Lukman Hakim', 'Made Wirawan', 'Nanda Pratama', 'Oscar Simanjuntak', 'Prima Yudha',
        'Rendra Gunawan', 'Satrio Wicaksono', 'Teguh Santoso', 'Umar Syarif', 'Vino Alamsyah',
    ];

    private array $femaleNames = [
        'Ayu Lestari', 'Bella Anggraini', 'Citra Dewanti', 'Dian Puspitasari', 'Erika Marlina',
        'Fitri Handayani', 'Gita Permata', 'Hana Salsabila', 'Intan Permatasari', 'Julia Kartika',
        'Kirana Dewi', 'Laras Ayuningtyas', 'Maya Sartika', 'Nia Ramadhani', 'Olivia Christy',
        'Putri Amelia', 'Ratna Sari', 'Sari Wulandari', 'Tania Oktaviani', 'Vera Susanti',
    ];

    private array $birthPlaces = [
        'Jakarta', 'Bandung', 'Surabaya', 'Medan', 'Semarang', 'Yogyakarta', 'Palembang',
        'Makassar', 'Denpasar', 'Malang', 'Solo', 'Bogor', 'Bekasi', 'Depok', 'Tangerang',
    ];

    private int $maleIdx = 0;
    private int $femaleIdx = 0;
    private int $placeIdx = 0;
    private int $usedNames = 0;

    private array $levelPay = [
        'Manager'      => ['tunjangan' => 5000000, 'gaji' => 15000000, 'birth' => [1975, 1985], 'tenure' => [3, 8]],
        'SPV'          => ['tunjangan' => 2500000, 'gaji' => 9000000,  'birth' => [1983, 1990], 'tenure' => [2, 6]],
        'Senior Staff' => ['tunjangan' => 1200000, 'gaji' => 6500000,  'birth' => [1988, 1994], 'tenure' => [1, 4]],
        'Staff'        => ['tunjangan' => 500000,  'gaji' => 4500000,  'birth' => [1993, 2001], 'tenure' => [0, 2]],
    ];

    public function run(): void
    {
        $company = Company::where('code', 'proenergi')->firstOrFail();
        $levels  = Level::pluck('id', 'name');
        $jaksel  = City::where('name', 'Jakarta Selatan')->first();
        $gajiPokokComp = SalaryComponent::where('name', 'Gaji Pokok')->first();
        $cutiTahunan   = LeaveType::where('name', 'Cuti Tahunan')->first();

        $departments = [
            'LOG'  => 'Logistik',
            'IT'   => 'IT',
            'HRGA' => 'HR & GA',
            'PROC' => 'Procurement',
            'COMM' => 'Commercial',
            'FIN'  => 'Finance',
        ];

        $created = 0;

        foreach ($departments as $code => $deptName) {
            $dept = Department::firstOrCreate(
                ['company_id' => $company->id, 'name' => $deptName],
                ['code' => $code, 'is_active' => true]
            );

            $isIt = $code === 'IT';

            // ── Posisi per level (IT skip SPV — sudah ada 2 dari ITDemoSeeder) ──
            $abbr = ['Manager' => 'MGR', 'SPV' => 'SPV', 'Senior Staff' => 'SST', 'Staff' => 'STF'];
            $pos = [];
            foreach (array_keys($this->levelPay) as $levelName) {
                if ($isIt && $levelName === 'SPV') {
                    continue;
                }
                $pos[$levelName] = Position::firstOrCreate(
                    ['company_id' => $company->id, 'department_id' => $dept->id, 'name' => $levelName . ' ' . $deptName],
                    [
                        'code'              => $code . '-' . $abbr[$levelName],
                        'level_id'          => $levels[$levelName],
                        'tunjangan_jabatan' => $this->levelPay[$levelName]['tunjangan'],
                        'is_active'         => true,
                    ]
                );
            }

            // IT sudah punya NIP IT-001/IT-003 dari ITDemoSeeder — mulai dari 101 supaya tidak bentrok.
            $seq = $isIt ? 101 : 1;

            // ── Manager (puncak departemen) ──
            $manager = $this->makeEmployee(
                $company, $dept, $pos['Manager'], $levels['Manager'], 'Manager', null,
                $code . '-' . str_pad($seq++, 3, '0', STR_PAD_LEFT), $jaksel, $gajiPokokComp, $cutiTahunan, withLogin: true
            );
            $dept->update(['head_employee_id' => $manager->id]);
            $created++;

            if ($isIt) {
                // IT punya 2 SPV existing (Andi Saputra IT-001, Candra Wijaya IT-003) — masing-masing
                // jadi kepala Section sendiri (Programmer / Infrastruktur), BUKAN digabung rata di
                // bawah Manager, supaya bagan organisasi menampilkan 2 cabang Section terpisah.
                $secProgrammer = Section::firstOrCreate(['department_id' => $dept->id, 'name' => 'Section Programmer'], ['code' => 'IT-SEC-PROG', 'is_active' => true]);
                $secInfra      = Section::firstOrCreate(['department_id' => $dept->id, 'name' => 'Section Infrastruktur'], ['code' => 'IT-SEC-INFRA', 'is_active' => true]);

                $spvProgrammer = Employee::where('department_id', $dept->id)->where('nip', 'IT-001')->first();
                $spvInfra      = Employee::where('department_id', $dept->id)->where('nip', 'IT-003')->first();
                $spvProgrammer->update(['manager_id' => $manager->id, 'section_id' => $secProgrammer->id]);
                $spvInfra->update(['manager_id' => $manager->id, 'section_id' => $secInfra->id]);
                Position::where('id', $spvProgrammer->position_id)->update(['section_id' => $secProgrammer->id]);
                Position::where('id', $spvInfra->position_id)->update(['section_id' => $secInfra->id]);

                foreach ([$secProgrammer->id => $spvProgrammer->id, $secInfra->id => $spvInfra->id] as $sectionId => $spvId) {
                    $srStaff = $this->makeEmployee(
                        $company, $dept, $pos['Senior Staff'], $levels['Senior Staff'], 'Senior Staff', $spvId,
                        $code . '-' . str_pad($seq++, 3, '0', STR_PAD_LEFT), $jaksel, $gajiPokokComp, $cutiTahunan
                    );
                    $srStaff->update(['section_id' => $sectionId]);
                    $created++;

                    $staff = $this->makeEmployee(
                        $company, $dept, $pos['Staff'], $levels['Staff'], 'Staff', $spvId,
                        $code . '-' . str_pad($seq++, 3, '0', STR_PAD_LEFT), $jaksel, $gajiPokokComp, $cutiTahunan
                    );
                    $staff->update(['section_id' => $sectionId]);
                    $created++;
                }
            } else {
                $spv = $this->makeEmployee(
                    $company, $dept, $pos['SPV'], $levels['SPV'], 'SPV', $manager->id,
                    $code . '-' . str_pad($seq++, 3, '0', STR_PAD_LEFT), $jaksel, $gajiPokokComp, $cutiTahunan, withLogin: true
                );
                $reportsTo = $spv->id;
                $created++;

                for ($i = 0; $i < 2; $i++) {
                    $this->makeEmployee(
                        $company, $dept, $pos['Senior Staff'], $levels['Senior Staff'], 'Senior Staff', $reportsTo,
                        $code . '-' . str_pad($seq++, 3, '0', STR_PAD_LEFT), $jaksel, $gajiPokokComp, $cutiTahunan
                    );
                    $created++;
                }
                for ($i = 0; $i < 2; $i++) {
                    $this->makeEmployee(
                        $company, $dept, $pos['Staff'], $levels['Staff'], 'Staff', $reportsTo,
                        $code . '-' . str_pad($seq++, 3, '0', STR_PAD_LEFT), $jaksel, $gajiPokokComp, $cutiTahunan
                    );
                    $created++;
                }
            }
        }

        $this->command?->info("OrgSampleDataSeeder selesai: {$created} karyawan baru di 6 departemen (Logistik, IT, HR & GA, Procurement, Commercial, Finance), jenjang Staff s/d Manager.");
    }

    private function makeEmployee(
        Company $company, Department $dept, Position $position, int $levelId, string $levelName,
        ?int $managerId, string $nip, ?City $jaksel, ?SalaryComponent $gajiPokokComp, ?LeaveType $cutiTahunan,
        bool $withLogin = false,
    ): Employee {
        $gender = ($this->usedNames % 2 === 0) ? 'L' : 'P';
        $this->usedNames++;
        if ($gender === 'L') {
            $name = $this->maleNames[$this->maleIdx % count($this->maleNames)];
            $this->maleIdx++;
        } else {
            $name = $this->femaleNames[$this->femaleIdx % count($this->femaleNames)];
            $this->femaleIdx++;
        }

        $birthPlace = $this->birthPlaces[$this->placeIdx % count($this->birthPlaces)];
        $this->placeIdx++;

        [$yFrom, $yTo] = $this->levelPay[$levelName]['birth'];
        [$tFrom, $tTo] = $this->levelPay[$levelName]['tenure'];
        $birthDate = sprintf('%d-%02d-%02d', rand($yFrom, $yTo), rand(1, 12), rand(1, 28));
        $startDate = now()->subYears(rand($tFrom, $tTo))->subDays(rand(0, 300));

        $email = Str::slug($name, '.') . '@proenergi.co.id';
        if (Employee::where('email', $email)->exists() || User::where('email', $email)->exists()) {
            $email = Str::slug($name, '.') . '.' . strtolower(str_replace('-', '', $nip)) . '@proenergi.co.id';
        }

        $user = null;
        if ($withLogin) {
            $user = User::firstOrCreate(['email' => $email], ['name' => $name, 'password' => Hash::make('password')]);
            $user->syncRoles(['karyawan']);
        }

        $employee = Employee::create([
            'company_id'    => $company->id,
            'user_id'       => $user?->id,
            'department_id' => $dept->id,
            'position_id'   => $position->id,
            'level_id'      => $levelId,
            'manager_id'    => $managerId,
            'name'          => $name,
            'nip'           => $nip,
            'gender'        => $gender,
            'birth_place'   => $birthPlace,
            'birth_date'    => $birthDate,
            'start_date'    => $startDate,
            'employment_status' => 'permanent',
            'is_active'     => true,
            'email'         => $email,
            'phone'         => '0812' . rand(10000000, 99999999),
            'employee_type'    => 'local',
            'employee_type_id' => EmployeeType::where('legacy_key', 'permanent')->value('id'),
            'religion_id'      => Religion::inRandomOrder()->value('id'),
            'marital_status_id' => MaritalStatus::inRandomOrder()->value('id'),
            'blood_type_id'    => BloodType::inRandomOrder()->value('id'),
            'domicile_city_id'     => $jaksel?->id,
            'domicile_province_id' => $jaksel?->province_id,
            'domicile_address'     => 'Jl. Contoh Sample No. ' . rand(1, 99),
            'ktp_city_id'          => $jaksel?->id,
            'ktp_province_id'      => $jaksel?->province_id,
            'ktp_address'          => 'Jl. Contoh Sample No. ' . rand(1, 99),
        ]);

        if ($gajiPokokComp) {
            EmployeeSalaryComponent::updateOrCreate(
                ['employee_id' => $employee->id, 'salary_component_id' => $gajiPokokComp->id],
                ['amount' => $this->levelPay[$levelName]['gaji']]
            );
        }

        if ($cutiTahunan) {
            LeaveBalance::forEmployee($employee->id, $cutiTahunan->id, now()->year);
        }

        return $employee;
    }
}
