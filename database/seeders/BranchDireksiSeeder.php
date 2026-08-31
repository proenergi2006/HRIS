<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
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
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Tier Cabang (lokasi) + Direksi (CEO/CFO) untuk bagan organisasi:
 *  - 8 cabang (HO, Jakarta, Surabaya, Palembang, Samarinda, Banjarmasin,
 *    Pontianak, Sulawesi) dibuat untuk KETIGA perusahaan.
 *  - Semua 37 karyawan existing PT. Pro Energi ditandai cabang HO.
 *  - CEO "Vica" & CFO "Irwanti" (Level Direksi) HANYA untuk PT. Pro Energi;
 *    Manager Procurement/IT/Logistik/Commercial/HR & GA lapor ke CEO, Manager
 *    Finance lapor ke CFO, CFO lapor ke CEO.
 *  - Tim kecil (1 Kepala Cabang + 2 Staff Cabang) dibuat di 7 cabang non-HO
 *    utk KETIGA perusahaan; TDS & Pinnafore (0 karyawan sebelumnya) juga
 *    diberi tim HO supaya cabang HO mereka tidak kosong.
 *
 * TIDAK didaftarkan di DatabaseSeeder default — jalankan manual:
 * php artisan db:seed --class=BranchDireksiSeeder
 */
class BranchDireksiSeeder extends Seeder
{
    private array $cityBranches = ['Jakarta', 'Surabaya', 'Palembang', 'Samarinda', 'Banjarmasin', 'Pontianak', 'Sulawesi'];

    private array $maleNames = [
        'Wahyu Nugraha', 'Yusuf Ardiansyah', 'Zainal Abidin', 'Agus Salim', 'Bambang Sutrisno',
        'Chandra Kusuma', 'Dedi Kurniawan', 'Fauzan Ramli', 'Hendra Gunadi', 'Iqbal Maulana',
    ];

    private array $femaleNames = [
        'Winda Astuti', 'Yulia Rahmawati', 'Zahra Amelia', 'Anisa Fitriani', 'Bunga Citra',
        'Cynthia Wijaya', 'Devina Anggraeni', 'Farah Diba', 'Herlina Susanti', 'Indah Permata',
    ];

    private array $birthPlaces = ['Jakarta', 'Surabaya', 'Palembang', 'Samarinda', 'Banjarmasin', 'Pontianak', 'Makassar', 'Bandung'];

    private int $maleIdx = 0;
    private int $femaleIdx = 0;
    private int $placeIdx = 0;
    private int $usedNames = 0;

    public function run(): void
    {
        $levels = Level::pluck('id', 'name');
        $jaksel = City::where('name', 'Jakarta Selatan')->first();
        $gajiPokokComp = SalaryComponent::where('name', 'Gaji Pokok')->first();
        $cutiTahunan   = LeaveType::where('name', 'Cuti Tahunan')->first();

        $companies = Company::whereIn('code', ['proenergi', 'tds', 'pfr'])->get()->keyBy('code');

        // ── 1. 8 cabang x 3 perusahaan ─────────────────────────────────────
        $branches = [];
        foreach ($companies as $code => $company) {
            $branches[$code]['HO'] = Branch::firstOrCreate(
                ['company_id' => $company->id, 'name' => 'HO'],
                ['code' => 'HO', 'is_active' => true]
            );
            foreach ($this->cityBranches as $city) {
                $branches[$code][$city] = Branch::firstOrCreate(
                    ['company_id' => $company->id, 'name' => $city],
                    ['code' => Str::upper(Str::substr($city, 0, 3)), 'is_active' => true]
                );
            }
        }
        $this->command?->info('Cabang: ' . Branch::count() . ' baris (8 x 3 perusahaan).');

        // ── 2. Karyawan existing Pro Energi -> cabang HO ────────────────────
        // Dibatasi ke karyawan departemen (department_id terisi) supaya re-run
        // seeder ini TIDAK menimpa branch_id tim cabang yang sudah benar
        // dibuat di step 5 (department_id mereka NULL).
        $proenergi = $companies['proenergi'];
        Employee::where('company_id', $proenergi->id)->whereNotNull('department_id')->update([
            'branch'    => 'HO',
            'branch_id' => $branches['proenergi']['HO']->id,
        ]);

        // ── 3. Direksi PT. Pro Energi (CEO Vica, CFO Irwanti) ──────────────
        // branch_id ditautkan ke HO supaya perhitungan "jabatan kosong" per
        // Cabang di bagan tidak mencampur posisi CEO/CFO ke cabang lain
        // (lihat buildBranches() di OrgChartController — posisi tanpa
        // Departemen di-scope per Cabang lewat kolom ini).
        $posCeo = Position::firstOrCreate(
            ['company_id' => $proenergi->id, 'department_id' => null, 'name' => 'CEO'],
            ['code' => 'DIR-CEO', 'level_id' => $levels['Direksi'], 'branch_id' => $branches['proenergi']['HO']->id, 'tunjangan_jabatan' => 15000000, 'is_active' => true]
        );
        $posCfo = Position::firstOrCreate(
            ['company_id' => $proenergi->id, 'department_id' => null, 'name' => 'CFO'],
            ['code' => 'DIR-CFO', 'level_id' => $levels['Direksi'], 'branch_id' => $branches['proenergi']['HO']->id, 'tunjangan_jabatan' => 12000000, 'is_active' => true]
        );
        if (! $posCeo->branch_id) {
            $posCeo->update(['branch_id' => $branches['proenergi']['HO']->id]);
        }
        if (! $posCfo->branch_id) {
            $posCfo->update(['branch_id' => $branches['proenergi']['HO']->id]);
        }

        $ceo = Employee::where('company_id', $proenergi->id)->where('nip', 'DIR-001')->first()
            ?? $this->makeEmployee($proenergi, 'Vica', $posCeo, $levels['Direksi'], null, 'DIR-001', $jaksel, $gajiPokokComp, $cutiTahunan, $branches['proenergi']['HO'], true);
        $ceo->update(['position_id' => $posCeo->id, 'level_id' => $levels['Direksi'], 'manager_id' => null, 'department_id' => null, 'branch_id' => $branches['proenergi']['HO']->id]);

        $cfo = Employee::where('company_id', $proenergi->id)->where('nip', 'DIR-002')->first()
            ?? $this->makeEmployee($proenergi, 'Irwanti', $posCfo, $levels['Direksi'], $ceo->id, 'DIR-002', $jaksel, $gajiPokokComp, $cutiTahunan, $branches['proenergi']['HO'], true);
        $cfo->update(['position_id' => $posCfo->id, 'level_id' => $levels['Direksi'], 'manager_id' => $ceo->id, 'department_id' => null, 'branch_id' => $branches['proenergi']['HO']->id]);

        $this->command?->info("Direksi: CEO {$ceo->name} (#{$ceo->id}), CFO {$cfo->name} (#{$cfo->id}).");

        // ── 4. Rewire manager departemen -> Direksi ─────────────────────────
        $toCeo = ['Logistik', 'IT', 'HR & GA', 'Procurement', 'Commercial'];
        $managerLevelId = $levels['Manager'];
        foreach ($toCeo as $deptName) {
            $mgr = Employee::where('company_id', $proenergi->id)
                ->whereHas('department', fn ($q) => $q->where('name', $deptName))
                ->where('level_id', $managerLevelId)
                ->first();
            if ($mgr) {
                $mgr->update(['manager_id' => $ceo->id]);
                $this->command?->info("Manager {$deptName} ({$mgr->name}) -> lapor ke CEO.");
            }
        }
        $mgrFinance = Employee::where('company_id', $proenergi->id)
            ->whereHas('department', fn ($q) => $q->where('name', 'Finance'))
            ->where('level_id', $managerLevelId)
            ->first();
        if ($mgrFinance) {
            $mgrFinance->update(['manager_id' => $cfo->id]);
            $this->command?->info("Manager Finance ({$mgrFinance->name}) -> lapor ke CFO.");
        }

        // ── 5. Tim kecil per cabang non-HO (3 perusahaan) + tim HO (TDS/Pinnafore) ──
        $seq = 1;
        foreach ($companies as $code => $company) {
            $targets = $this->cityBranches;
            if ($code !== 'proenergi') {
                $targets = array_merge(['HO'], $targets); // TDS/Pinnafore belum punya karyawan sama sekali
            }

            foreach ($targets as $cityName) {
                $branch = $branches[$code][$cityName];

                $codePrefix = strtoupper($code) . '-BR-' . Str::upper(Str::substr($cityName, 0, 3));
                $posKepala = Position::firstOrCreate(
                    ['company_id' => $company->id, 'department_id' => null, 'name' => "Kepala Cabang {$cityName}"],
                    ['code' => $codePrefix . '-HD', 'level_id' => $levels['SPV'], 'branch_id' => $branch->id, 'tunjangan_jabatan' => 2000000, 'is_active' => true]
                );
                $posStaff = Position::firstOrCreate(
                    ['company_id' => $company->id, 'department_id' => null, 'name' => "Staff Cabang {$cityName}"],
                    ['code' => $codePrefix . '-STF', 'level_id' => $levels['Staff'], 'branch_id' => $branch->id, 'tunjangan_jabatan' => 400000, 'is_active' => true]
                );
                if (! $posKepala->branch_id) {
                    $posKepala->update(['branch_id' => $branch->id]);
                }
                if (! $posStaff->branch_id) {
                    $posStaff->update(['branch_id' => $branch->id]);
                }

                $nipPrefix = strtoupper($code) . '-' . strtoupper(substr($cityName, 0, 3));
                $nipKepala = $nipPrefix . '-001';

                $kepala = Employee::where('company_id', $company->id)->where('nip', $nipKepala)->first();
                if (! $kepala) {
                    $kepala = $this->makeEmployeeAuto($company, $posKepala, $levels['SPV'], 'SPV', null, $nipKepala, $jaksel, $gajiPokokComp, $cutiTahunan, $branch, true);
                }

                for ($i = 1; $i <= 2; $i++) {
                    $nipStaff = $nipPrefix . '-' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT);
                    if (Employee::where('company_id', $company->id)->where('nip', $nipStaff)->exists()) {
                        continue;
                    }
                    $this->makeEmployeeAuto($company, $posStaff, $levels['Staff'], 'Staff', $kepala->id, $nipStaff, $jaksel, $gajiPokokComp, $cutiTahunan, $branch, false);
                }

                $seq++;
            }
            $this->command?->info("Tim cabang {$code}: selesai (" . count($targets) . ' cabang).');
        }

        $this->command?->info('Total karyawan sekarang: proenergi=' . Employee::where('company_id', $proenergi->id)->count()
            . ', tds=' . Employee::where('company_id', $companies['tds']->id)->count()
            . ', pfr=' . Employee::where('company_id', $companies['pfr']->id)->count());
    }

    private function makeEmployeeAuto(Company $company, Position $position, int $levelId, string $levelName, ?int $managerId, string $nip, ?City $jaksel, ?SalaryComponent $gajiPokokComp, ?LeaveType $cutiTahunan, Branch $branch, bool $withLogin): Employee
    {
        $pay = match ($levelName) {
            'SPV'   => ['tunjangan' => 2000000, 'gaji' => 8000000, 'birth' => [1983, 1990], 'tenure' => [2, 6]],
            default => ['tunjangan' => 400000, 'gaji' => 4200000, 'birth' => [1993, 2001], 'tenure' => [0, 2]],
        };

        return $this->makeEmployee($company, null, $position, $levelId, $managerId, $nip, $jaksel, $gajiPokokComp, $cutiTahunan, $branch, $withLogin, $pay);
    }

    private function makeEmployee(
        Company $company, ?string $forcedName, Position $position, int $levelId, ?int $managerId, string $nip,
        ?City $jaksel, ?SalaryComponent $gajiPokokComp, ?LeaveType $cutiTahunan, Branch $branch, bool $withLogin,
        ?array $pay = null,
    ): Employee {
        if ($forcedName) {
            $name = $forcedName;
            $gender = 'P';
        } else {
            $gender = ($this->usedNames % 2 === 0) ? 'L' : 'P';
            $this->usedNames++;
            if ($gender === 'L') {
                $name = $this->maleNames[$this->maleIdx % count($this->maleNames)];
                $this->maleIdx++;
            } else {
                $name = $this->femaleNames[$this->femaleIdx % count($this->femaleNames)];
                $this->femaleIdx++;
            }
        }

        $birthPlace = $this->birthPlaces[$this->placeIdx % count($this->birthPlaces)];
        $this->placeIdx++;

        $pay ??= ['tunjangan' => 5000000, 'gaji' => 20000000, 'birth' => [1975, 1983], 'tenure' => [5, 10]];
        [$yFrom, $yTo] = $pay['birth'];
        [$tFrom, $tTo] = $pay['tenure'];
        $birthDate = sprintf('%d-%02d-%02d', rand($yFrom, $yTo), rand(1, 12), rand(1, 28));
        $startDate = now()->subYears(rand($tFrom, $tTo))->subDays(rand(0, 300));

        $emailDomain = $company->code . '.co.id';
        $email = Str::slug($name, '.') . '@' . $emailDomain;
        if (Employee::where('email', $email)->exists() || User::where('email', $email)->exists()) {
            $email = Str::slug($name, '.') . '.' . strtolower(str_replace('-', '', $nip)) . '@' . $emailDomain;
        }

        $user = null;
        if ($withLogin) {
            $user = User::firstOrCreate(['email' => $email], ['name' => $name, 'password' => Hash::make('password')]);
            $user->syncRoles(['karyawan']);
        }

        $employee = Employee::create([
            'company_id'    => $company->id,
            'user_id'       => $user?->id,
            'department_id' => null,
            'position_id'   => $position->id,
            'level_id'      => $levelId,
            'manager_id'    => $managerId,
            'branch'        => $branch->name,
            'branch_id'     => $branch->id,
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
            'domicile_address'     => 'Jl. Cabang Sample No. ' . rand(1, 99),
            'ktp_city_id'          => $jaksel?->id,
            'ktp_province_id'      => $jaksel?->province_id,
            'ktp_address'          => 'Jl. Cabang Sample No. ' . rand(1, 99),
        ]);

        if ($gajiPokokComp) {
            EmployeeSalaryComponent::updateOrCreate(
                ['employee_id' => $employee->id, 'salary_component_id' => $gajiPokokComp->id],
                ['amount' => $pay['gaji']]
            );
        }

        if ($cutiTahunan) {
            LeaveBalance::forEmployee($employee->id, $cutiTahunan->id, now()->year);
        }

        return $employee;
    }
}
