<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeeBankAccount;
use App\Models\EmployeeContract;
use App\Models\EmployeeEducation;
use App\Models\EmployeeFacility;
use App\Models\EmployeeFamilyMember;
use App\Models\EmployeeNssf;
use App\Models\EmployeeSkill;
use App\Models\EmployeeWorkExperience;
use App\Models\HR\LeaveBalance;
use App\Models\HR\LeaveRequest;
use App\Models\HR\LeaveType;
use App\Models\Master\Bank;
use App\Models\Master\BloodType;
use App\Models\Master\City;
use App\Models\Master\EmployeeType;
use App\Models\Master\MaritalStatus;
use App\Models\Master\Religion;
use App\Models\Perdin\PerdinBudgetItem;
use App\Models\Perdin\PerdinRequest;
use App\Models\Position;
use App\Models\PromotionRotationRequest;
use App\Models\PunishmentRequest;
use App\Models\Reimbursement\ReimbursementBalance;
use App\Models\Reimbursement\ReimbursementItem;
use App\Models\Reimbursement\ReimbursementRequest;
use App\Models\RewardRequest;
use App\Models\Section;
use App\Models\TerminationRequest;
use App\Models\User;
use App\Services\ApprovalEngine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Data sample menyeluruh untuk uji coba semua modul v1.2: Struktur Organisasi
 * (Divisi/Section/jabatan kosong), Master Data (agama/wilayah/bank/dll di tiap
 * karyawan), Employee Database (tab lengkap), dan Approval Engine (Cuti,
 * Perdin, Reimbursement, Reward, Punishment, Promosi & Rotasi, Termination)
 * dalam berbagai status (draft/pending di step berbeda/approved).
 *
 * Dipakai SETELAH data transaksi & karyawan lama dikosongkan — asumsikan
 * master data (companies, departments, positions, levels, master referensi,
 * approval_workflows) sudah ada.
 */
class SampleDataSeeder extends Seeder
{
    private ApprovalEngine $engine;

    public function run(): void
    {
        $this->engine = app(ApprovalEngine::class);

        $companies = Company::whereIn('code', ['proenergi', 'tds', 'pfr'])->get()->keyBy('code');
        $departments = Department::all()->keyBy('name');
        $positions   = Position::all()->keyBy('name');
        $levels      = \App\Models\Level::all()->keyBy('name');
        // Force-approve helper — pakai user admin, yang berhak menindak step manapun
        // (lihat ApprovalEngine::canActOn -> admin selalu boleh), supaya tidak perlu
        // menebak approver tiap step satu-satu saat menyiapkan contoh "sudah disetujui".
        $adminUser = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first();

        // ── Struktur Organisasi baru: Divisi + Section + 1 jabatan kosong ──
        $divTi = Division::create([
            'company_id' => $companies['proenergi']->id, 'code' => 'DIV-TI',
            'name' => 'Divisi Teknologi Informasi', 'is_active' => true,
        ]);
        $divKeu = Division::create([
            'company_id' => $companies['proenergi']->id, 'code' => 'DIV-KEU',
            'name' => 'Divisi Keuangan & Umum', 'is_active' => true,
        ]);
        $divOpsTds = Division::create([
            'company_id' => $companies['tds']->id, 'code' => 'DIV-OPS-TDS',
            'name' => 'Divisi Operasional', 'is_active' => true,
        ]);

        $departments['IT']->update(['division_id' => $divTi->id, 'cost_center' => 'CC-IT-01']);
        $departments['Finance']->update(['division_id' => $divKeu->id, 'cost_center' => 'CC-FIN-01']);
        $departments['HR & GA']->update(['division_id' => $divKeu->id]);
        // Logistik & Procurement (Pro Energi) sengaja dibiarkan tanpa Divisi —
        // supaya bagan menunjukkan campuran: sebagian Divisi terisi, sebagian "Tanpa Divisi".

        $secBackend = Section::create(['department_id' => $departments['IT']->id, 'code' => 'SEC-BE', 'name' => 'Section Backend', 'is_active' => true]);
        $secInfra   = Section::create(['department_id' => $departments['IT']->id, 'code' => 'SEC-INFRA', 'name' => 'Section Infrastructure', 'is_active' => true]);

        // Jabatan kosong (vacant) — sengaja tidak diisi karyawan, utk uji tampilan "Jabatan Kosong".
        $vacantPosition = Position::create([
            'company_id' => $companies['proenergi']->id, 'department_id' => $departments['IT']->id,
            'section_id' => $secBackend->id, 'level_id' => $levels['Staff']->id,
            'code' => 'QAENG', 'name' => 'QA Engineer', 'is_active' => true,
        ]);

        // ── Users (password sama semua: "password") ──
        $u = fn (string $email, string $name, string $role) => tap(
            User::firstOrCreate(['email' => $email], ['name' => $name, 'password' => Hash::make('password')]),
            fn ($user) => $user->syncRoles([$role])
        );

        $userManagerIt      = $u('manager.it@proenergi.co.id', 'Denny Prasetyo', 'user_ii');
        $userProgrammer     = $u('budi.programmer@proenergi.co.id', 'Budi Santoso', 'karyawan');
        $userManagerFinance = $u('manager.finance@proenergi.co.id', 'Sri Wulandari', 'user_ii');
        $userHrManager      = $u('hrmanager@proenergi.co.id', 'Nadia Kusuma', 'hr_manager');
        $userCeo            = $u('ceo@proenergi.co.id', 'Bambang Wijaya', 'ceo');
        $u('cfo@proenergi.co.id', 'Melati Handayani', 'cfo');
        $u('ga@proenergi.co.id', 'Fajar Nugroho', 'admin_ga');
        $userSpvLogistikTds = $u('spv.logistik.tds@proenergi.co.id', 'Rangga Saputra', 'user_ii');

        // ── Karyawan ──
        $jakartaSelatan = City::where('name', 'Jakarta Selatan')->first();
        $jakartaId      = $jakartaSelatan?->province_id;

        $mk = function (array $overrides) use ($jakartaSelatan, $jakartaId) {
            $defaults = [
                'is_active'            => true,
                'employment_status'    => 'permanent',
                'employee_type'        => 'local',
                'employee_type_id'     => EmployeeType::where('legacy_key', 'permanent')->value('id'),
                'religion_id'          => Religion::inRandomOrder()->value('id'),
                'marital_status_id'    => MaritalStatus::where('code', 'KAWIN')->value('id'),
                'blood_type_id'        => BloodType::inRandomOrder()->value('id'),
                'domicile_city_id'     => $jakartaSelatan?->id,
                'domicile_province_id' => $jakartaId,
                'ktp_city_id'          => $jakartaSelatan?->id,
                'ktp_province_id'      => $jakartaId,
                'domicile_address'     => 'Jl. Contoh Sample No. 1',
                'ktp_address'          => 'Jl. Contoh Sample No. 1',
                'start_date'           => now()->subYears(2),
            ];

            return Employee::create($overrides + $defaults);
        };

        // — Pro Energi > Divisi TI > Section Backend —
        $managerIt = $mk([
            'company_id' => $companies['proenergi']->id, 'user_id' => $userManagerIt->id,
            'division_id' => $divTi->id, 'department_id' => $departments['IT']->id, 'section_id' => $secBackend->id,
            'position_id' => $positions['Manager IT']->id, 'level_id' => $levels['Manager']->id,
            'name' => 'Denny Prasetyo', 'nip' => 'PE-1001', 'gender' => 'L', 'birth_place' => 'Bandung',
            'birth_date' => '1985-03-12', 'email' => 'manager.it@proenergi.co.id', 'phone' => '081200000001',
        ]);
        $programmer = $mk([
            'company_id' => $companies['proenergi']->id, 'user_id' => $userProgrammer->id, 'manager_id' => $managerIt->id,
            'division_id' => $divTi->id, 'department_id' => $departments['IT']->id, 'section_id' => $secBackend->id,
            'position_id' => $positions['Programmer']->id, 'level_id' => $levels['Staff']->id,
            'name' => 'Budi Santoso', 'nip' => 'PE-1002', 'gender' => 'L', 'birth_place' => 'Jakarta',
            'birth_date' => '1996-07-20', 'email' => 'budi.programmer@proenergi.co.id', 'phone' => '081200000002',
            'employment_status' => 'contract', 'contract_end_date' => now()->addMonths(6),
        ]);

        // — Pro Energi > Divisi TI > Section Infrastructure —
        $spvInfra = $mk([
            'company_id' => $companies['proenergi']->id, 'manager_id' => $managerIt->id,
            'division_id' => $divTi->id, 'department_id' => $departments['IT']->id, 'section_id' => $secInfra->id,
            'position_id' => $positions['SPV Infrastructure']->id, 'level_id' => $levels['SPV']->id,
            'name' => 'Yoga Pratama', 'nip' => 'PE-1003', 'gender' => 'L', 'birth_place' => 'Surabaya', 'birth_date' => '1990-11-05',
        ]);
        $infraEngineer = $mk([
            'company_id' => $companies['proenergi']->id, 'manager_id' => $spvInfra->id,
            'division_id' => $divTi->id, 'department_id' => $departments['IT']->id, 'section_id' => $secInfra->id,
            'position_id' => $positions['Infrastructure Engineer']->id, 'level_id' => $levels['Staff']->id,
            'name' => 'Citra Ayu Lestari', 'nip' => 'PE-1004', 'gender' => 'P', 'birth_place' => 'Yogyakarta', 'birth_date' => '1998-02-14',
        ]);

        // — Pro Energi > Divisi Keuangan & Umum > Finance —
        $managerFinance = $mk([
            'company_id' => $companies['proenergi']->id, 'user_id' => $userManagerFinance->id,
            'division_id' => $divKeu->id, 'department_id' => $departments['Finance']->id,
            'position_id' => $positions['Manager Finance']->id, 'level_id' => $levels['Manager']->id,
            'name' => 'Sri Wulandari', 'nip' => 'PE-1005', 'gender' => 'P', 'birth_place' => 'Semarang', 'birth_date' => '1983-09-01',
            'email' => 'manager.finance@proenergi.co.id',
        ]);
        $spvTax = $mk([
            'company_id' => $companies['proenergi']->id, 'manager_id' => $managerFinance->id,
            'division_id' => $divKeu->id, 'department_id' => $departments['Finance']->id,
            'position_id' => $positions['SPV TAX']->id, 'level_id' => $levels['SPV']->id,
            'name' => 'Hendra Gunawan', 'nip' => 'PE-1006', 'gender' => 'L', 'birth_place' => 'Medan', 'birth_date' => '1988-05-17',
        ]);
        $seniorStaffFin = $mk([
            'company_id' => $companies['proenergi']->id, 'manager_id' => $managerFinance->id,
            'division_id' => $divKeu->id, 'department_id' => $departments['Finance']->id,
            'position_id' => $positions['Senior Staff']->id, 'level_id' => $levels['Senior Staff']->id,
            'name' => 'Putri Ramadhani', 'nip' => 'PE-1007', 'gender' => 'P', 'birth_place' => 'Malang', 'birth_date' => '1994-12-25',
        ]);

        // — Pro Energi > Divisi Keuangan & Umum > HR & GA —
        $hrManagerEmp = $mk([
            'company_id' => $companies['proenergi']->id, 'user_id' => $userHrManager->id,
            'division_id' => $divKeu->id, 'department_id' => $departments['HR & GA']->id,
            'position_id' => $positions['SPV HR']->id, 'level_id' => $levels['Manager']->id,
            'name' => 'Nadia Kusuma', 'nip' => 'PE-1008', 'gender' => 'P', 'birth_place' => 'Jakarta', 'birth_date' => '1987-04-08',
            'email' => 'hrmanager@proenergi.co.id',
        ]);
        $spvRecruitment = $mk([
            'company_id' => $companies['proenergi']->id, 'manager_id' => $hrManagerEmp->id,
            'division_id' => $divKeu->id, 'department_id' => $departments['HR & GA']->id,
            'position_id' => $positions['SPV Recruitment']->id, 'level_id' => $levels['SPV']->id,
            'name' => 'Ilham Maulana', 'nip' => 'PE-1009', 'gender' => 'L', 'birth_place' => 'Bogor', 'birth_date' => '1992-08-30',
        ]);

        // — Pro Energi > Tanpa Divisi (Logistik, Procurement) —
        $spvLogistikPe = $mk([
            'company_id' => $companies['proenergi']->id,
            'department_id' => $departments['Logistik']->id,
            'position_id' => $positions['SPV Logistik']->id, 'level_id' => $levels['SPV']->id,
            'name' => 'Fitriani Rahma', 'nip' => 'PE-1010', 'gender' => 'P', 'birth_place' => 'Palembang', 'birth_date' => '1991-01-19',
        ]);
        $srStaffProcurement = $mk([
            'company_id' => $companies['proenergi']->id,
            'department_id' => $departments['Procurement']->id,
            'position_id' => $positions['Senior Staff Procurement']->id, 'level_id' => $levels['Senior Staff']->id,
            'name' => 'Agus Setiawan', 'nip' => 'PE-1011', 'gender' => 'L', 'birth_place' => 'Solo', 'birth_date' => '1989-06-22',
        ]);

        $ceoEmp = $mk([
            'company_id' => $companies['proenergi']->id, 'user_id' => $userCeo->id,
            'department_id' => $departments['HR & GA']->id,
            'level_id' => $levels['Direksi']->id,
            'name' => 'Bambang Wijaya', 'nip' => 'PE-1000', 'gender' => 'L', 'birth_place' => 'Jakarta', 'birth_date' => '1975-10-10',
            'email' => 'ceo@proenergi.co.id',
        ]);

        // — TDS > Divisi Operasional > Logistik —
        $spvLogistikTds = $mk([
            'company_id' => $companies['tds']->id, 'user_id' => $userSpvLogistikTds->id,
            'division_id' => $divOpsTds->id, 'department_id' => $departments['Logistik']->id,
            'position_id' => $positions['SPV Logistik']->id, 'level_id' => $levels['SPV']->id,
            'name' => 'Rangga Saputra', 'nip' => 'TDS-2001', 'gender' => 'L', 'birth_place' => 'Bekasi', 'birth_date' => '1990-02-02',
            'email' => 'spv.logistik.tds@proenergi.co.id',
        ]);
        $adminTds = $mk([
            'company_id' => $companies['tds']->id, 'manager_id' => $spvLogistikTds->id,
            'division_id' => $divOpsTds->id, 'department_id' => $departments['Logistik']->id,
            'position_id' => $positions['Admin Staff']->id, 'level_id' => $levels['Admin']->id,
            'name' => 'Lestari Wibowo', 'nip' => 'TDS-2002', 'gender' => 'P', 'birth_place' => 'Depok', 'birth_date' => '1997-03-15',
        ]);

        // — TDS > Tanpa Divisi (IT) —
        $managerItTds = $mk([
            'company_id' => $companies['tds']->id, 'department_id' => $departments['IT']->id,
            'position_id' => $positions['SPV Programmer']->id, 'level_id' => $levels['Manager']->id,
            'name' => 'Wahyu Setiadi', 'nip' => 'TDS-2003', 'gender' => 'L', 'birth_place' => 'Tangerang', 'birth_date' => '1986-07-07',
        ]);
        $programmerTds = $mk([
            'company_id' => $companies['tds']->id, 'manager_id' => $managerItTds->id,
            'department_id' => $departments['IT']->id,
            'position_id' => $positions['Programmer']->id, 'level_id' => $levels['Staff']->id,
            'name' => 'Dewi Anggraini', 'nip' => 'TDS-2004', 'gender' => 'P', 'birth_place' => 'Bandung', 'birth_date' => '1999-09-09',
        ]);

        // — PFR (tanpa Divisi/Section sama sekali) —
        $spvCollectingPfr = $mk([
            'company_id' => $companies['pfr']->id, 'department_id' => $departments['Finance']->id,
            'position_id' => $positions['SPV Collecting']->id, 'level_id' => $levels['SPV']->id,
            'name' => 'Taufik Hidayat', 'nip' => 'PFR-3001', 'gender' => 'L', 'birth_place' => 'Cirebon', 'birth_date' => '1993-04-18',
        ]);
        $adminPfr = $mk([
            'company_id' => $companies['pfr']->id, 'manager_id' => $spvCollectingPfr->id,
            'department_id' => $departments['Finance']->id,
            'position_id' => $positions['Admin Staff']->id, 'level_id' => $levels['Admin']->id,
            'name' => 'Siti Nurhaliza', 'nip' => 'PFR-3002', 'gender' => 'P', 'birth_place' => 'Serang', 'birth_date' => '1995-05-05',
        ]);
        $spvRecruitmentPfr = $mk([
            'company_id' => $companies['pfr']->id, 'department_id' => $departments['HR & GA']->id,
            'position_id' => $positions['SPV Recruitment']->id, 'level_id' => $levels['SPV']->id,
            'name' => 'Reza Firmansyah', 'nip' => 'PFR-3003', 'gender' => 'L', 'birth_place' => 'Cilegon', 'birth_date' => '1991-11-11',
            'employment_status' => 'contract', 'contract_end_date' => now()->addMonths(3),
        ]);

        // ── Tab lengkap Employee Database (untuk 2-3 karyawan, buktikan semua tab jalan) ──
        $this->fillEmployeeDetails($managerIt, 'Bank Central Asia (BCA)', '1112223334');
        $this->fillEmployeeDetails($hrManagerEmp, 'Bank Mandiri', '5556667778');
        $this->fillEmployeeDetails($programmer, 'Bank Rakyat Indonesia (BRI)', '9990001112', contractType: 'pkwt', contractEnd: $programmer->contract_end_date);

        EmployeeFamilyMember::create(['employee_id' => $managerIt->id, 'relation' => 'spouse', 'name' => 'Rina Prasetyo', 'birth_date' => '1987-06-10']);
        EmployeeFamilyMember::create(['employee_id' => $managerIt->id, 'relation' => 'child', 'name' => 'Aditya Prasetyo', 'birth_date' => '2015-01-20']);
        EmployeeFamilyMember::create(['employee_id' => $hrManagerEmp->id, 'relation' => 'spouse', 'name' => 'Dimas Kusuma', 'birth_date' => '1985-02-02']);

        // ── Saldo Cuti ──
        $cutiTahunan = LeaveType::where('name', 'Cuti Tahunan')->first();
        foreach ([$managerIt, $programmer, $spvInfra, $infraEngineer, $managerFinance, $hrManagerEmp] as $emp) {
            LeaveBalance::forEmployee($emp->id, $cutiTahunan->id, now()->year);
        }

        // ── Transaksi contoh — dijalankan lewat Approval Engine (bukan set status manual) ──

        // 1) Cuti — pending, menunggu Manager IT (direct_manager)
        $leave1 = LeaveRequest::create([
            'employee_id' => $programmer->id, 'leave_type_id' => $cutiTahunan->id,
            'start_date' => now()->addDays(5), 'end_date' => now()->addDays(6), 'total_days' => 2,
            'reason' => 'Acara keluarga', 'status' => 'pending',
        ]);
        $this->engine->start($leave1->load('employee', 'leaveType'));

        // 2) Cuti — approver langsung (SPV Infrastructure) belum ada akun -> step di-skip
        //    otomatis, lanjut ke HR Manager (role) -> pending di sana.
        $leave2 = LeaveRequest::create([
            'employee_id' => $infraEngineer->id, 'leave_type_id' => $cutiTahunan->id,
            'start_date' => now()->addDays(10), 'end_date' => now()->addDays(10), 'total_days' => 1,
            'reason' => 'Keperluan pribadi', 'status' => 'pending',
        ]);
        $this->engine->start($leave2->load('employee', 'leaveType'));

        // 3) Cuti — full approved (2 step) supaya kelihatan saldo terpotong.
        $leave3 = LeaveRequest::create([
            'employee_id' => $seniorStaffFin->id, 'leave_type_id' => $cutiTahunan->id,
            'start_date' => now()->subDays(20), 'end_date' => now()->subDays(18), 'total_days' => 3,
            'reason' => 'Cuti tahunan', 'status' => 'pending',
        ]);
        LeaveBalance::forEmployee($seniorStaffFin->id, $cutiTahunan->id, now()->year);
        $req3 = $this->engine->start($leave3->load('employee', 'leaveType'));
        foreach ($req3->fresh()->steps as $step) {
            if ($step->status === 'pending') {
                $this->engine->approve($step, $adminUser, 'Disetujui — sample data');
            }
        }

        // 4) Perdin — pending (atasan Manager Finance kosong -> skip -> HR Manager role)
        $perdin1 = PerdinRequest::create([
            'no_advance' => PerdinRequest::generateNumber(), 'user_id' => $userManagerFinance->id,
            'department' => 'Finance', 'destination' => 'Surabaya',
            'departure_date' => now()->addDays(7), 'return_date' => now()->addDays(9),
            'purpose' => 'Audit cabang Surabaya', 'status' => 'pending',
        ]);
        PerdinBudgetItem::create(['perdin_request_id' => $perdin1->id, 'category' => 'transportasi', 'item_name' => 'Tiket Pesawat PP', 'handled_by' => 'ga', 'qty' => 1, 'unit_cost' => 1800000, 'total_cost' => 1800000]);
        PerdinBudgetItem::create(['perdin_request_id' => $perdin1->id, 'category' => 'penginapan', 'item_name' => 'Hotel 2 malam', 'handled_by' => 'ga', 'qty' => 2, 'unit_cost' => 650000, 'total_cost' => 1300000]);
        $perdin1->recalculateTotals();
        $this->engine->start($perdin1->fresh());

        // 5) Reimbursement — pending, menunggu Manager IT
        $reim1 = ReimbursementRequest::create([
            'user_id' => $userProgrammer->id, 'request_number' => ReimbursementRequest::generateNumber(),
            'request_date' => now(), 'medical_for' => 'employee', 'marital_status' => 'single', 'status' => 'pending',
        ]);
        ReimbursementItem::create(['reimbursement_request_id' => $reim1->id, 'patient_name' => 'Budi Santoso', 'treatment_date' => now()->subDays(2), 'institution' => 'Klinik Sehat Sentosa', 'amount_doctor' => 150000, 'amount_medicine' => 85000, 'total_claim' => 235000]);
        $reim1->recalculateTotal();
        $this->engine->start($reim1->fresh());

        // 6) Reimbursement — full approved, supaya saldo & periode pembayaran kelihatan.
        $reim2 = ReimbursementRequest::create([
            'user_id' => $userHrManager->id, 'request_number' => ReimbursementRequest::generateNumber(),
            'request_date' => now()->subDays(15), 'medical_for' => 'child_1', 'marital_status' => 'married', 'status' => 'pending',
        ]);
        ReimbursementItem::create(['reimbursement_request_id' => $reim2->id, 'patient_name' => 'Dimas Kusuma Jr.', 'treatment_date' => now()->subDays(16), 'institution' => 'RS Ibu & Anak', 'amount_doctor' => 300000, 'amount_lab' => 175000, 'total_claim' => 475000]);
        $reim2->recalculateTotal();
        $reim2->update(['payment_month' => now()->month, 'payment_year' => now()->year]);
        $req6 = $this->engine->start($reim2->fresh());
        foreach ($req6->fresh()->steps as $step) {
            if ($step->status === 'pending') {
                $this->engine->approve($step, $adminUser, 'Disetujui — sample data');
            }
        }

        // 7) Reward — pending, menunggu Manager IT
        $reward1 = RewardRequest::create([
            'employee_id' => $programmer->id, 'company_id' => $companies['proenergi']->id,
            'requested_by_user_id' => $userHrManager->id, 'reward_type' => 'Penghargaan Kinerja Terbaik Q3',
            'description' => 'Konsisten menyelesaikan sprint tepat waktu selama 3 bulan berturut-turut.',
            'effective_date' => now()->addDays(14), 'amount' => 1500000, 'status' => 'pending',
        ]);
        $this->engine->start($reward1->fresh());

        // 8) Punishment — SP1, pending
        $punishment1 = PunishmentRequest::create([
            'employee_id' => $srStaffProcurement->id, 'company_id' => $companies['proenergi']->id,
            'requested_by_user_id' => $userHrManager->id, 'violation_type' => 'Keterlambatan berulang',
            'sanction_level' => 'sp1', 'description' => 'Terlambat lebih dari 5 kali dalam sebulan tanpa keterangan.',
            'incident_date' => now()->subDays(3), 'effective_date' => now(), 'status' => 'pending',
        ]);
        $this->engine->start($punishment1->fresh());

        // 9) Promosi & Rotasi — draft (belum diajukan)
        PromotionRotationRequest::create([
            'employee_id' => $spvInfra->id, 'company_id' => $companies['proenergi']->id,
            'requested_by_user_id' => $userHrManager->id, 'request_type' => 'promotion',
            'from_position_id' => $positions['SPV Infrastructure']->id, 'to_position_id' => $positions['Manager IT']->id,
            'effective_date' => now()->addMonth(), 'reason' => 'Kinerja konsisten baik, siap naik ke level manajerial.',
            'status' => 'draft',
        ]);

        // 10) Termination — draft (belum diajukan)
        TerminationRequest::create([
            'employee_id' => $adminPfr->id, 'company_id' => $companies['pfr']->id,
            'requested_by_user_id' => $userHrManager->id, 'termination_type' => 'resign',
            'reason' => 'Mengundurkan diri untuk melanjutkan studi.',
            'last_working_date' => now()->addDays(30), 'effective_date' => now()->addDays(30),
            'status' => 'draft',
        ]);

        $this->command?->info('Sample data selesai: ' . Employee::count() . ' karyawan, ' . User::count() . ' user.');
    }

    private function fillEmployeeDetails(
        Employee $employee,
        string $bankName,
        string $accountNumber,
        string $contractType = 'pkwtt',
        $contractEnd = null,
    ): void {
        $bank = Bank::where('name', $bankName)->first();

        EmployeeEducation::create([
            'employee_id' => $employee->id,
            'education_level_id' => \App\Models\Master\EducationLevel::where('code', 'S1')->value('id'),
            'education_major_id' => \App\Models\Master\EducationMajor::where('name', 'Teknik Informatika')->value('id')
                ?? \App\Models\Master\EducationMajor::first()?->id,
            'institution' => 'Universitas Indonesia', 'graduation_year' => 2010, 'gpa' => 3.45,
        ]);

        EmployeeWorkExperience::create([
            'employee_id' => $employee->id, 'company_name' => 'PT Contoh Sebelumnya',
            'company_city' => 'Jakarta', 'start_date' => now()->subYears(6), 'end_date' => now()->subYears(2),
            'end_job_title' => 'Staff', 'end_pay_rate' => 6000000, 'job_description' => 'Operasional harian.',
        ]);

        EmployeeSkill::create(['employee_id' => $employee->id, 'name' => 'Microsoft Excel', 'proficiency' => 'advanced']);
        EmployeeSkill::create(['employee_id' => $employee->id, 'name' => 'Komunikasi', 'proficiency' => 'expert']);

        if ($bank) {
            EmployeeBankAccount::create([
                'employee_id' => $employee->id, 'bank_id' => $bank->id,
                'account_number' => $accountNumber, 'account_holder_name' => $employee->name,
                'is_primary' => true, 'is_active' => true,
            ]);
        }

        EmployeeNssf::create([
            'employee_id' => $employee->id,
            'health_registered' => true, 'health_number' => '000' . $employee->id . '11223344',
            'health_join_date' => $employee->start_date,
            'employment_registered' => true, 'employment_number' => '111' . $employee->id . '22334455',
            'employment_join_date' => $employee->start_date,
        ]);

        EmployeeContract::create([
            'employee_id' => $employee->id, 'contract_type' => $contractType,
            'number' => 'SPK/' . now()->year . '/' . str_pad((string) $employee->id, 3, '0', STR_PAD_LEFT),
            'start_date' => $employee->start_date, 'end_date' => $contractEnd, 'status' => 'active',
        ]);

        EmployeeFacility::create([
            'employee_id' => $employee->id, 'name' => 'Laptop', 'description' => 'Laptop kerja standar',
            'received_date' => $employee->start_date,
        ]);
    }
}
