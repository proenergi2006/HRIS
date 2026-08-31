<?php

namespace Database\Seeders;

use App\Models\Appraisal\Appraisal;
use App\Models\Appraisal\AppraisalObjective;
use App\Models\Appraisal\AppraisalPeriod;
use App\Models\Appraisal\AppraisalTemplate;
use App\Models\CareerPath;
use App\Models\CareerPathStep;
use App\Models\Candidate;
use App\Models\Company;
use App\Models\Competency\Competency;
use App\Models\Competency\EmployeeCompetency;
use App\Models\Competency\PositionCompetency;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDataChangeRequest;
use App\Models\GA\MeetingRoom;
use App\Models\GA\RoomCleaningItem;
use App\Models\GA\RoomCleaningLog;
use App\Models\GA\RoomCleaningLogDetail;
use App\Models\GA\Vault;
use App\Models\GA\VaultDocument;
use App\Models\GA\VaultDocumentTransaction;
use App\Models\GA\Vehicle;
use App\Models\GA\VehicleUsage;
use App\Models\HR\AttendanceRecord;
use App\Models\HR\BonusPeriod;
use App\Models\HR\EmployeeLoan;
use App\Models\HR\LeaveRequest;
use App\Models\HR\LeaveType;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\RosterEntry;
use App\Models\HR\Shift;
use App\Models\HR\ThrPeriod;
use App\Models\JobRequisition;
use App\Models\Level;
use App\Models\LetterRequest;
use App\Models\LetterTemplate;
use App\Models\ManpowerPlan;
use App\Models\Master\EmployeeType;
use App\Models\OvertimeRequest;
use App\Models\Perdin\PerdinBudgetItem;
use App\Models\Perdin\PerdinRequest;
use App\Models\Position;
use App\Models\PromotionRotationRequest;
use App\Models\PunishmentRequest;
use App\Models\Reimbursement\ReimbursementBalance;
use App\Models\Reimbursement\ReimbursementItem;
use App\Models\Reimbursement\ReimbursementRequest;
use App\Models\RewardRequest;
use App\Models\Survey\Survey;
use App\Models\Survey\SurveyAnswer;
use App\Models\Survey\SurveyQuestion;
use App\Models\Survey\SurveyResponse;
use App\Models\TerminationRequest;
use App\Models\TrainingProgram;
use App\Models\TrainingParticipant;
use App\Models\Announcement;
use App\Models\User;
use App\Models\WhistleblowerReport;
use App\Services\ApprovalEngine;
use App\Services\Payroll\Pph21Calculator;
use Illuminate\Database\Seeder;

/**
 * Sample data untuk SEMUA menu yang belum tersentuh oleh OrgSampleDataSeeder:
 * GA (kendaraan/ruangan/berangkas), Whistleblower, Manpower Planning,
 * Recruitment (requisition/kandidat/biaya), Training, Career, Competency,
 * Appraisal, Absensi/Lembur/Cuti, Shift & Roster, Kasbon/Bonus/THR/Payroll,
 * Reward/Punishment/Promosi/Termination, Perdin, Reimbursement, Perubahan
 * Data, Permintaan Surat, Pengumuman, Survey.
 *
 * Prasyarat: OrgSampleDataSeeder sudah dijalankan (butuh 37 karyawan + 6
 * departemen). Jalankan manual:
 *   php artisan db:seed --class=OrgSampleDataSeeder
 *   php artisan db:seed --class=AllMenuSampleDataSeeder
 */
class AllMenuSampleDataSeeder extends Seeder
{
    private ApprovalEngine $engine;
    private Company $company;
    private User $admin;
    private array $emp = [];

    public function run(): void
    {
        $this->engine  = app(ApprovalEngine::class);
        $this->company = Company::where('code', 'proenergi')->firstOrFail();
        $this->admin   = User::where('email', 'admin@proenergi.co.id')->firstOrFail();

        foreach (Employee::where('company_id', $this->company->id)->get() as $e) {
            $this->emp[$e->nip] = $e;
        }

        $this->seedGa();
        $this->seedWhistleblower();
        $this->seedManpowerPlan();
        $this->seedRecruitment();
        $this->seedTraining();
        $this->seedCareer();
        $this->seedCompetency();
        $this->seedAppraisal();
        $this->seedAttendanceOvertimeLeave();
        $this->seedShiftRoster();
        $this->seedKasbonBonusThrPayroll();
        $this->seedHrRequests();
        $this->seedPerdinReimbursement();
        $this->seedEssExtras();
        $this->seedEngagement();

        $this->command?->info('AllMenuSampleDataSeeder selesai — sample data terisi di semua menu.');
    }

    private function e(string $nip): Employee
    {
        return $this->emp[$nip];
    }

    private function forceApprove($approvalRequest): void
    {
        foreach ($approvalRequest->fresh()->steps as $step) {
            if ($step->status === 'pending') {
                $this->engine->approve($step, $this->admin, 'Disetujui — sample data');
            }
        }
    }

    // ── GA ───────────────────────────────────────────────────────────────

    private function seedGa(): void
    {
        $v1 = Vehicle::firstOrCreate(['plate' => 'B 1234 SPE'], ['name' => 'Toyota Avanza', 'type' => 'MPV', 'color' => 'Silver', 'year' => 2022, 'is_active' => true]);
        Vehicle::firstOrCreate(['plate' => 'B 5678 SPE'], ['name' => 'Toyota Hilux', 'type' => 'Pickup', 'color' => 'Putih', 'year' => 2021, 'is_active' => true]);

        if (! VehicleUsage::where('vehicle_id', $v1->id)->exists()) {
            VehicleUsage::create([
                'vehicle_id' => $v1->id, 'driver_name' => 'Rian Hidayat', 'destination' => 'Kunjungan gudang Bekasi',
                'check_in_at' => now()->subDays(3), 'check_out_at' => now()->subDays(3)->addHours(4),
                'km_out' => 15200, 'status' => 'checked_out',
            ]);
        }

        $r1 = MeetingRoom::firstOrCreate(['name' => 'Ruang Rapat Cendana'], ['location' => 'Lantai 2', 'is_active' => true]);
        $r2 = MeetingRoom::firstOrCreate(['name' => 'Ruang Rapat Melati'], ['location' => 'Lantai 3', 'is_active' => true]);
        foreach ([$r1, $r2] as $room) {
            foreach (['Meja & kursi rapi', 'Lantai bersih', 'AC berfungsi', 'Proyektor & layar bersih'] as $i => $item) {
                RoomCleaningItem::firstOrCreate(['room_id' => $room->id, 'name' => $item], ['order' => $i + 1]);
            }
        }
        if (! RoomCleaningLog::where('room_id', $r1->id)->exists()) {
            $log = RoomCleaningLog::create(['room_id' => $r1->id, 'cleaner_name' => 'Petugas Kebersihan', 'cleaned_at' => now()->subDay()]);
            foreach ($r1->cleaningItems as $item) {
                RoomCleaningLogDetail::create(['log_id' => $log->id, 'item_id' => $item->id]);
            }
        }

        $vault = Vault::firstOrCreate(['name' => 'Berangkas Dokumen Legal']);
        $doc1 = VaultDocument::firstOrCreate(['vault_id' => $vault->id, 'detail' => 'Akta Pendirian PT. Pro Energi'], ['is_active' => true]);
        VaultDocument::firstOrCreate(['vault_id' => $vault->id, 'detail' => 'Sertifikat Tanah Kantor Pusat'], ['is_active' => true]);

        if (! VaultDocumentTransaction::where('document_id', $doc1->id)->exists()) {
            VaultDocumentTransaction::create([
                'document_id' => $doc1->id, 'status' => 'pengembalian', 'transaction_date' => now()->subDays(10),
                'nama' => 'Fitri Handayani', 'keperluan' => 'pengembalian_jaminan',
                'photo_handover' => 'sample-data/placeholder.jpg', 'created_by' => $this->admin->id,
            ]);
        }
    }

    // ── Whistleblower ────────────────────────────────────────────────────

    private function seedWhistleblower(): void
    {
        $reports = [
            ['category' => 'Dugaan Gratifikasi', 'status' => 'new', 'anon' => true,
                'desc' => 'Diduga ada penerimaan hadiah dari vendor logistik saat proses tender berlangsung.'],
            ['category' => 'Pelanggaran Hukum', 'status' => 'in_review', 'anon' => false,
                'desc' => 'Ditemukan dugaan pelanggaran prosedur keselamatan kerja di gudang cabang.'],
        ];
        foreach ($reports as $i => $r) {
            if (WhistleblowerReport::where('description', $r['desc'])->exists()) {
                continue;
            }
            WhistleblowerReport::create([
                'ticket_number'      => WhistleblowerReport::generateTicket(),
                'category'           => $r['category'],
                'branch_location'    => 'Jakarta',
                'reporter_relation'  => 'Karyawan',
                'description'        => $r['desc'],
                'is_anonymous'       => $r['anon'],
                'reporter_name'      => $r['anon'] ? null : 'Pelapor Internal',
                'previously_reported' => false,
                'willing_to_be_contacted' => ! $r['anon'],
                'status'             => $r['status'],
            ]);
        }
    }

    // ── Manpower Planning ────────────────────────────────────────────────

    private function seedManpowerPlan(): void
    {
        $plans = [
            ['dept' => 'Logistik', 'year' => now()->year, 'month' => now()->month + 1, 'headcount' => 2, 'status' => 'pending', 'notes' => 'Tambahan tim gudang untuk ekspansi cabang baru.'],
            ['dept' => 'IT', 'year' => now()->year, 'month' => now()->month + 2, 'headcount' => 1, 'status' => 'approved', 'notes' => 'Kebutuhan 1 Programmer tambahan untuk proyek internal.'],
            ['dept' => 'Commercial', 'year' => now()->year + 1, 'month' => 1, 'headcount' => 3, 'status' => 'draft', 'notes' => 'Rencana ekspansi tim sales tahun depan.'],
        ];

        foreach ($plans as $p) {
            $dept = Department::where('company_id', $this->company->id)->where('name', $p['dept'])->first();
            if (! $dept || ManpowerPlan::where('department_id', $dept->id)->where('year', $p['year'])->where('month', $p['month'])->exists()) {
                continue;
            }
            $plan = ManpowerPlan::create([
                'company_id' => $this->company->id, 'department_id' => $dept->id,
                'year' => $p['year'], 'month' => $p['month'], 'planned_headcount' => $p['headcount'],
                'notes' => $p['notes'], 'status' => 'draft', 'requested_by_user_id' => $this->admin->id,
            ]);
            if ($p['status'] === 'draft') {
                continue;
            }
            $plan->update(['status' => 'pending']);
            $req = $this->engine->start($plan->fresh());
            if ($p['status'] === 'approved') {
                $this->forceApprove($req);
            }
        }
    }

    // ── Recruitment ──────────────────────────────────────────────────────

    private function seedRecruitment(): void
    {
        $permanentTypeId = EmployeeType::where('legacy_key', 'permanent')->value('id');
        $logDept = Department::where('company_id', $this->company->id)->where('name', 'Logistik')->first();
        $commDept = Department::where('company_id', $this->company->id)->where('name', 'Commercial')->first();

        $req1 = JobRequisition::firstOrCreate(
            ['company_id' => $this->company->id, 'department_id' => $logDept->id, 'title' => 'Staff Gudang'],
            [
                'reason' => 'Penambahan kapasitas gudang cabang baru', 'headcount_requested' => 2,
                'employment_type_id' => $permanentTypeId, 'target_join_date' => now()->addMonths(2),
                'status' => 'draft', 'requested_by_user_id' => $this->admin->id,
            ]
        );
        if ($req1->wasRecentlyCreated) {
            $req1->update(['status' => 'pending']);
            $this->forceApprove($this->engine->start($req1->fresh()));
        }

        $req2 = JobRequisition::firstOrCreate(
            ['company_id' => $this->company->id, 'department_id' => $commDept->id, 'title' => 'Sales Executive'],
            [
                'reason' => 'Ekspansi target penjualan Q1', 'headcount_requested' => 1,
                'employment_type_id' => $permanentTypeId, 'target_join_date' => now()->addMonth(),
                'status' => 'draft', 'requested_by_user_id' => $this->admin->id,
            ]
        );
        if ($req2->wasRecentlyCreated) {
            $req2->update(['status' => 'pending']);
            $this->engine->start($req2->fresh());
        }

        $candidates = [
            ['req' => $req1, 'name' => 'Wahyu Ramadhan', 'status' => 'applied'],
            ['req' => $req1, 'name' => 'Xena Marlina', 'status' => 'screening'],
            ['req' => $req1, 'name' => 'Yusuf Alamsyah', 'status' => 'interview'],
            ['req' => $req2, 'name' => 'Zahra Anindita', 'status' => 'offer'],
        ];
        foreach ($candidates as $c) {
            Candidate::firstOrCreate(
                ['job_requisition_id' => $c['req']->id, 'name' => $c['name']],
                ['email' => \Illuminate\Support\Str::slug($c['name'], '.') . '@contoh-pelamar.com', 'source' => 'Jobstreet', 'status' => $c['status']]
            );
        }

        \App\Models\RecruitmentCost::firstOrCreate(
            ['company_id' => $this->company->id, 'job_requisition_id' => $req1->id, 'category' => 'iklan'],
            ['amount' => 750000, 'incurred_on' => now()->subDays(5), 'notes' => 'Pasang iklan lowongan Jobstreet', 'created_by' => $this->admin->id]
        );
        \App\Models\RecruitmentCost::firstOrCreate(
            ['company_id' => $this->company->id, 'job_requisition_id' => $req2->id, 'category' => 'agency'],
            ['amount' => 2500000, 'incurred_on' => now()->subDays(2), 'notes' => 'Fee agency rekrutmen Sales Executive', 'created_by' => $this->admin->id]
        );
    }

    // ── Training & Development ──────────────────────────────────────────

    private function seedTraining(): void
    {
        $prog1 = TrainingProgram::firstOrCreate(
            ['company_id' => $this->company->id, 'title' => 'K3 Dasar (Keselamatan & Kesehatan Kerja)'],
            ['category' => 'Safety', 'provider' => 'Internal', 'duration_hours' => 8, 'is_active' => true]
        );
        $prog2 = TrainingProgram::firstOrCreate(
            ['company_id' => $this->company->id, 'title' => 'Excel & Data Analysis untuk Staff'],
            ['category' => 'Technical', 'provider' => 'Internal', 'duration_hours' => 16, 'is_active' => true]
        );

        $participants = [
            [$prog1, $this->e('LOG-005'), 'completed', '88'],
            [$prog1, $this->e('LOG-006'), 'completed', '92'],
            [$prog1, $this->e('HRGA-005'), 'ongoing', null],
            [$prog2, $this->e('FIN-005'), 'completed', '85'],
            [$prog2, $this->e('FIN-006'), 'planned', null],
        ];
        foreach ($participants as [$prog, $employee, $status, $score]) {
            TrainingParticipant::firstOrCreate(
                ['training_program_id' => $prog->id, 'employee_id' => $employee->id],
                [
                    'start_date' => now()->subDays(20), 'end_date' => $status === 'completed' ? now()->subDays(18) : null,
                    'status' => $status, 'score' => $score,
                ]
            );
        }
    }

    // ── Career Management ────────────────────────────────────────────────

    private function seedCareer(): void
    {
        $path = CareerPath::firstOrCreate(
            ['company_id' => $this->company->id, 'title' => 'Jalur Karir Logistik'],
            ['description' => 'Staff -> Senior Staff -> SPV -> Manager di fungsi Logistik.', 'is_active' => true]
        );
        $steps = ['Staff Logistik', 'Senior Staff Logistik', 'SPV Logistik', 'Manager Logistik'];
        foreach ($steps as $i => $posName) {
            $pos = Position::where('company_id', $this->company->id)->where('name', $posName)->first();
            if ($pos) {
                CareerPathStep::firstOrCreate(['career_path_id' => $path->id, 'position_id' => $pos->id], ['step_order' => $i + 1]);
            }
        }
        foreach (['LOG-005', 'LOG-006'] as $nip) {
            $this->e($nip)->update(['career_path_id' => $path->id]);
        }
    }

    // ── Competency Framework ─────────────────────────────────────────────

    private function seedCompetency(): void
    {
        $managerPositions = Position::where('company_id', $this->company->id)->where('name', 'like', 'Manager %')->get();
        $leadership = Competency::where('name', 'Kepemimpinan')->first();
        $decision   = Competency::where('name', 'Pengambilan Keputusan')->first();
        foreach ($managerPositions as $pos) {
            if ($leadership) PositionCompetency::firstOrCreate(['position_id' => $pos->id, 'competency_id' => $leadership->id], ['required_level' => 4]);
            if ($decision)   PositionCompetency::firstOrCreate(['position_id' => $pos->id, 'competency_id' => $decision->id], ['required_level' => 4]);
        }

        $teamwork = Competency::where('name', 'Kerja Sama Tim')->first();
        $technical = Competency::where('name', 'Penguasaan Teknis Jabatan')->first();
        foreach (['LOG-001', 'IT-101', 'FIN-001', 'HRGA-001'] as $nip) {
            $employee = $this->e($nip);
            if ($leadership) EmployeeCompetency::firstOrCreate(
                ['employee_id' => $employee->id, 'competency_id' => $leadership->id],
                ['actual_level' => rand(3, 5), 'assessed_on' => now()->subMonth(), 'assessor_user_id' => $this->admin->id]
            );
            if ($teamwork) EmployeeCompetency::firstOrCreate(
                ['employee_id' => $employee->id, 'competency_id' => $teamwork->id],
                ['actual_level' => rand(3, 5), 'assessed_on' => now()->subMonth(), 'assessor_user_id' => $this->admin->id]
            );
            if ($technical) EmployeeCompetency::firstOrCreate(
                ['employee_id' => $employee->id, 'competency_id' => $technical->id],
                ['actual_level' => rand(2, 4), 'assessed_on' => now()->subMonth(), 'assessor_user_id' => $this->admin->id]
            );
        }
    }

    // ── Appraisal ────────────────────────────────────────────────────────

    private function seedAppraisal(): void
    {
        $period = AppraisalPeriod::firstOrCreate(
            ['name' => 'Penilaian Kinerja ' . now()->year],
            ['year' => now()->year, 'start_date' => now()->startOfYear(), 'end_date' => now()->endOfYear(), 'status' => 'open']
        );
        $template = AppraisalTemplate::where('is_default', true)->first();
        if (! $template) {
            return;
        }

        $subjects = [
            ['nip' => 'LOG-002', 'evaluator' => 'rian.hidayat@proenergi.co.id', 'status' => 'approved'],
            ['nip' => 'IT-001',  'evaluator' => 'eko.prasetyo@proenergi.co.id', 'status' => 'pending'],
            ['nip' => 'HRGA-002', 'evaluator' => 'fitri.handayani@proenergi.co.id', 'status' => 'draft'],
        ];

        foreach ($subjects as $s) {
            $employee = $this->e($s['nip']);
            if (Appraisal::where('employee_id', $employee->id)->where('appraisal_period_id', $period->id)->exists()) {
                continue;
            }
            $evaluator = User::where('email', $s['evaluator'])->first();
            $appraisal = Appraisal::create([
                'employee_id' => $employee->id, 'appraisal_period_id' => $period->id,
                'appraisal_template_id' => $template->id, 'evaluator_id' => $evaluator?->id,
                'status' => 'draft',
            ]);

            $total = 0;
            foreach ($template->objectives as $obj) {
                $achievement = rand(75, 100);
                $score = round($obj->weight_pct * $achievement / 100, 2);
                $total += $score;
                AppraisalObjective::create([
                    'appraisal_id' => $appraisal->id, 'title' => $obj->title, 'category' => $obj->category,
                    'weight_pct' => $obj->weight_pct, 'achievement_pct' => $achievement, 'score' => $score, 'order' => $obj->order,
                ]);
            }
            $grade = $template->gradeBands->sortByDesc('min_score')->first(fn ($b) => $total >= $b->min_score);
            $appraisal->update(['total_score' => $total, 'grade' => $grade?->grade_label]);

            if ($s['status'] === 'draft') {
                continue;
            }
            $appraisal->update(['status' => 'pending', 'submitted_at' => now()]);
            $req = $this->engine->start($appraisal->fresh());
            if ($s['status'] === 'approved') {
                $this->forceApprove($req);
            }
        }
    }

    // ── Absensi, Lembur, Cuti ────────────────────────────────────────────

    private function seedAttendanceOvertimeLeave(): void
    {
        $nips = ['LOG-003', 'LOG-004', 'LOG-005', 'IT-102', 'IT-104', 'HRGA-003', 'HRGA-005', 'FIN-003', 'FIN-005'];
        $statuses = ['hadir', 'hadir', 'hadir', 'hadir', 'telat', 'hadir', 'izin', 'hadir', 'sakit'];

        for ($d = 14; $d >= 1; $d--) {
            $date = now()->subDays($d);
            if ($date->isWeekend()) {
                continue;
            }
            foreach ($nips as $i => $nip) {
                $employee = $this->e($nip);
                $status = $statuses[($i + $d) % count($statuses)];
                AttendanceRecord::updateOrCreate(
                    ['employee_id' => $employee->id, 'date' => $date->toDateString()],
                    [
                        'company_id'   => $this->company->id,
                        'check_in'     => in_array($status, ['hadir', 'telat']) ? ($status === 'telat' ? '08:22:00' : '07:55:00') : null,
                        'check_out'    => in_array($status, ['hadir', 'telat']) ? '17:10:00' : null,
                        'status'       => $status,
                        'late_minutes' => $status === 'telat' ? 22 : 0,
                        'source'       => 'manual',
                    ]
                );
            }
        }

        // Lembur — self-service, 1 pending 1 approved
        $ot1 = OvertimeRequest::firstOrCreate(
            ['employee_id' => $this->e('IT-104')->id, 'date' => now()->subDays(3)->toDateString()],
            ['company_id' => $this->company->id, 'requested_by_user_id' => $this->e('IT-101')->user_id,
                'planned_hours' => 3, 'reason' => 'Deploy sistem di luar jam kerja', 'status' => 'draft']
        );
        if ($ot1->wasRecentlyCreated) {
            $ot1->update(['status' => 'pending']);
            $this->engine->start($ot1->fresh());
        }
        $ot2 = OvertimeRequest::firstOrCreate(
            ['employee_id' => $this->e('FIN-005')->id, 'date' => now()->subDays(7)->toDateString()],
            ['company_id' => $this->company->id, 'requested_by_user_id' => $this->e('FIN-001')->user_id,
                'planned_hours' => 2, 'reason' => 'Tutup buku bulanan', 'status' => 'draft']
        );
        if ($ot2->wasRecentlyCreated) {
            $ot2->update(['status' => 'pending']);
            $this->forceApprove($this->engine->start($ot2->fresh()));
        }

        // Cuti — 1 pending, 1 approved (potong saldo)
        $cutiTahunan = LeaveType::where('name', 'Cuti Tahunan')->first();
        $leave1 = LeaveRequest::firstOrCreate(
            ['employee_id' => $this->e('PROC-005')->id, 'start_date' => now()->addDays(5)->toDateString()],
            ['leave_type_id' => $cutiTahunan->id, 'end_date' => now()->addDays(6)->toDateString(), 'total_days' => 2,
                'reason' => 'Acara keluarga', 'status' => 'pending']
        );
        if ($leave1->wasRecentlyCreated) {
            $this->engine->start($leave1->fresh()->load('employee', 'leaveType'));
        }
        $leave2 = LeaveRequest::firstOrCreate(
            ['employee_id' => $this->e('COMM-005')->id, 'start_date' => now()->subDays(10)->toDateString()],
            ['leave_type_id' => $cutiTahunan->id, 'end_date' => now()->subDays(9)->toDateString(), 'total_days' => 2,
                'reason' => 'Cuti tahunan', 'status' => 'pending']
        );
        if ($leave2->wasRecentlyCreated) {
            $this->forceApprove($this->engine->start($leave2->fresh()->load('employee', 'leaveType')));
        }
    }

    // ── Shift & Roster ───────────────────────────────────────────────────

    private function seedShiftRoster(): void
    {
        $pagi = Shift::where('code', 'PAGI')->first();
        $siang = Shift::where('code', 'SIANG')->first();
        if (! $pagi) {
            return;
        }

        foreach (['LOG-003', 'LOG-004', 'LOG-005', 'LOG-006'] as $i => $nip) {
            $employee = $this->e($nip);
            $shift = $i < 2 ? $pagi : $siang;
            for ($d = 0; $d < 14; $d++) {
                $date = now()->addDays($d);
                if ($date->isWeekend()) {
                    continue;
                }
                RosterEntry::updateOrCreate(
                    ['employee_id' => $employee->id, 'work_date' => $date->toDateString()],
                    ['company_id' => $this->company->id, 'shift_id' => $shift->id]
                );
            }
        }
    }

    // ── Kasbon, Bonus, THR, Payroll ──────────────────────────────────────

    private function seedKasbonBonusThrPayroll(): void
    {
        // 2 karyawan IT lama (dari ITDemoSeeder) belum punya Gaji Pokok — lengkapi
        // supaya payroll/THR/bukti-potong mereka tidak Rp0.
        $gajiPokokComp = \App\Models\HR\SalaryComponent::where('name', 'Gaji Pokok')->first();
        if ($gajiPokokComp) {
            foreach (['IT-001', 'IT-003'] as $nip) {
                \App\Models\HR\EmployeeSalaryComponent::firstOrCreate(
                    ['employee_id' => $this->e($nip)->id, 'salary_component_id' => $gajiPokokComp->id],
                    ['amount' => 9000000]
                );
            }
        }

        // Kasbon
        $loan = EmployeeLoan::firstOrCreate(
            ['employee_id' => $this->e('LOG-005')->id, 'reference_no' => 'KSB/SAMPLE/001'],
            [
                'company_id' => $this->company->id, 'loan_type' => 'kasbon', 'principal' => 3000000,
                'installment_count' => 3, 'installment_amount' => 1000000,
                'start_month' => now()->month, 'start_year' => now()->year,
                'status' => 'active', 'notes' => 'Kasbon keperluan pribadi', 'created_by' => $this->admin->id,
            ]
        );
        if ($loan->wasRecentlyCreated) {
            $loan->generateSchedule();
        }

        // Bonus
        $bonusPeriod = BonusPeriod::firstOrCreate(
            ['company_id' => $this->company->id, 'name' => 'Bonus Kinerja Tahunan ' . now()->year],
            ['bonus_type' => 'bonus', 'payment_date' => now()->subDays(2), 'is_taxable' => true, 'status' => 'open']
        );
        if ($bonusPeriod->wasRecentlyCreated) {
            $calc = app(Pph21Calculator::class);
            foreach (['LOG-001', 'IT-101', 'FIN-001'] as $nip) {
                $employee = $this->e($nip);
                $gross = 5000000;
                $tax = $calc->calculate($employee, $gross)['amount'];
                \App\Models\HR\BonusPayment::create([
                    'bonus_period_id' => $bonusPeriod->id, 'employee_id' => $employee->id,
                    'gross_amount' => $gross, 'tax_amount' => $tax, 'net_amount' => $gross - $tax,
                ]);
            }
        }

        // THR
        $thrPeriod = ThrPeriod::firstOrCreate(
            ['company_id' => $this->company->id, 'year' => now()->year, 'holiday_name' => 'Idul Fitri ' . now()->year],
            ['payment_date' => now()->subMonths(2), 'status' => 'open']
        );
        if ($thrPeriod->wasRecentlyCreated) {
            app(\App\Http\Controllers\HR\ThrController::class)->generate($thrPeriod);
        }

        // Payroll — 1 periode closed supaya Bukti Potong & Slip ada isinya
        $period = PayrollPeriod::firstOrCreate(
            ['company_id' => $this->company->id, 'month' => now()->month, 'year' => now()->year],
            ['status' => 'open']
        );
        if ($period->wasRecentlyCreated) {
            app(\App\Http\Controllers\HR\PayrollController::class)->generate(request(), $period);
            // Ditutup supaya slip final & muncul di Bukti Potong PPh21 + Slip Gaji Saya (ESS).
            $period->update(['status' => 'closed', 'closed_by' => $this->admin->id, 'closed_at' => now()]);
        }
    }

    // ── Reward / Punishment / Promosi / Termination ─────────────────────

    private function seedHrRequests(): void
    {
        $reward = RewardRequest::firstOrCreate(
            ['employee_id' => $this->e('IT-104')->id, 'reward_type' => 'Penghargaan Kinerja Terbaik'],
            [
                'company_id' => $this->company->id, 'requested_by_user_id' => $this->e('IT-101')->user_id,
                'description' => 'Konsisten menyelesaikan pekerjaan tepat waktu.', 'effective_date' => now()->addDays(14),
                'amount' => 1000000, 'status' => 'draft',
            ]
        );
        if ($reward->wasRecentlyCreated) {
            $reward->update(['status' => 'pending']);
            $this->engine->start($reward->fresh());
        }

        $punishment = PunishmentRequest::firstOrCreate(
            ['employee_id' => $this->e('PROC-006')->id, 'violation_type' => 'Keterlambatan berulang'],
            [
                'company_id' => $this->company->id, 'requested_by_user_id' => $this->e('PROC-001')->user_id,
                'sanction_level' => 'sp1', 'description' => 'Terlambat lebih dari 5 kali dalam sebulan.',
                'incident_date' => now()->subDays(3), 'effective_date' => now(), 'status' => 'draft',
            ]
        );
        if ($punishment->wasRecentlyCreated) {
            $punishment->update(['status' => 'pending']);
            $this->engine->start($punishment->fresh());
        }

        $promo = PromotionRotationRequest::firstOrCreate(
            ['employee_id' => $this->e('COMM-003')->id, 'request_type' => 'promotion'],
            [
                'company_id' => $this->company->id, 'requested_by_user_id' => $this->e('COMM-001')->user_id,
                'from_position_id' => $this->e('COMM-003')->position_id,
                'to_position_id' => Position::where('company_id', $this->company->id)->where('name', 'SPV Commercial')->value('id'),
                'effective_date' => now()->addMonth(), 'reason' => 'Kinerja konsisten baik.', 'status' => 'draft',
            ]
        );

        $termNip = 'FIN-006';
        $termination = TerminationRequest::firstOrCreate(
            ['employee_id' => $this->e($termNip)->id, 'termination_type' => 'resign'],
            [
                'company_id' => $this->company->id, 'requested_by_user_id' => $this->e('FIN-001')->user_id,
                'reason' => 'Mengundurkan diri untuk melanjutkan studi.',
                'last_working_date' => now()->addDays(30), 'effective_date' => now()->addDays(30), 'status' => 'draft',
            ]
        );
        if ($termination->wasRecentlyCreated) {
            // Materialisasi checklist clearance resign — normalnya jalan lewat HrRequestController::store(),
            // dipanggil manual di sini karena seeder tidak lewat HTTP.
            \App\Http\Controllers\HR\OffboardingController::materialize($this->e($termNip));
        }
    }

    // ── Perdin & Reimbursement ───────────────────────────────────────────

    private function seedPerdinReimbursement(): void
    {
        $userIt = $this->e('IT-101')->user_id;
        $perdin = PerdinRequest::firstOrCreate(
            ['user_id' => $userIt, 'destination' => 'Surabaya', 'purpose' => 'Audit sistem cabang Surabaya'],
            [
                'no_advance' => PerdinRequest::generateNumber(), 'department' => 'IT',
                'departure_date' => now()->addDays(7), 'return_date' => now()->addDays(9), 'status' => 'draft',
            ]
        );
        if ($perdin->wasRecentlyCreated) {
            PerdinBudgetItem::create(['perdin_request_id' => $perdin->id, 'category' => 'transportasi', 'item_name' => 'Tiket Pesawat PP', 'handled_by' => 'ga', 'qty' => 1, 'unit_cost' => 1800000]);
            PerdinBudgetItem::create(['perdin_request_id' => $perdin->id, 'category' => 'penginapan', 'item_name' => 'Hotel 2 malam', 'handled_by' => 'ga', 'qty' => 2, 'unit_cost' => 650000]);
            $perdin->recalculateTotals();
            $perdin->update(['status' => 'pending']);
            $this->engine->start($perdin->fresh());
        }

        foreach ([$this->e('LOG-001')->user_id, $this->e('FIN-001')->user_id] as $userId) {
            if ($userId && ! ReimbursementBalance::where('user_id', $userId)->where('period_year', now()->year)->exists()) {
                ReimbursementBalance::create(['user_id' => $userId, 'period_year' => now()->year, 'balance_type' => 'medical', 'initial_balance' => 3000000]);
            }
        }

        $reim = ReimbursementRequest::firstOrCreate(
            ['user_id' => $this->e('LOG-001')->user_id, 'medical_for' => 'employee'],
            ['request_number' => ReimbursementRequest::generateNumber(), 'request_date' => now(), 'marital_status' => 'married', 'status' => 'draft']
        );
        if ($reim->wasRecentlyCreated) {
            ReimbursementItem::create(['reimbursement_request_id' => $reim->id, 'patient_name' => 'Rian Hidayat', 'treatment_date' => now()->subDays(2), 'institution' => 'Klinik Sehat Sentosa', 'amount_doctor' => 150000, 'amount_medicine' => 85000, 'total_claim' => 235000]);
            $reim->recalculateTotal();
            $reim->update(['status' => 'pending']);
            $this->engine->start($reim->fresh());
        }
    }

    // ── ESS tambahan: Perubahan Data, Permintaan Surat ──────────────────

    private function seedEssExtras(): void
    {
        $employee = $this->e('IT-102');
        $change = EmployeeDataChangeRequest::firstOrCreate(
            ['employee_id' => $employee->id, 'field_key' => 'phone'],
            [
                'requested_by_user_id' => $this->e('IT-101')->user_id ?? $this->admin->id,
                'old_value' => $employee->phone, 'new_value' => '081299998888',
                'reason' => 'Ganti nomor HP', 'status' => 'draft',
            ]
        );
        if ($change->wasRecentlyCreated) {
            $change->update(['status' => 'pending']);
            $this->engine->start($change->fresh());
        }

        $template = LetterTemplate::firstOrCreate(
            ['title' => 'Surat Keterangan Kerja'],
            [
                'category' => 'keterangan_kerja', 'self_service' => true, 'is_active' => true,
                'body' => "Yang bertanda tangan di bawah ini menerangkan bahwa:\n\nNama: {{nama}}\nNIP: {{nip}}\nJabatan: {{jabatan}}\nDepartemen: {{departemen}}\n\nadalah benar karyawan {{perusahaan}} sejak {{tgl_mulai}}.\n\nDemikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.",
            ]
        );
        LetterRequest::firstOrCreate(
            ['employee_id' => $this->e('FIN-003')->id, 'letter_template_id' => $template->id],
            ['requested_by_user_id' => $this->e('FIN-001')->user_id, 'purpose' => 'bank', 'notes' => 'Untuk keperluan KPR', 'status' => 'pending']
        );
    }

    // ── Pengumuman & Survey ──────────────────────────────────────────────

    private function seedEngagement(): void
    {
        $announcements = [
            ['title' => 'Libur Nasional Bersama', 'category' => 'info', 'pinned' => true, 'body' => 'Kantor libur pada tanggal cuti bersama sesuai SKB 3 Menteri.'],
            ['title' => 'Update Kebijakan Cuti Tahunan', 'category' => 'kebijakan', 'pinned' => false, 'body' => 'Kuota cuti tahunan kini bisa dibawa maksimal 6 hari ke tahun berikutnya.'],
            ['title' => 'Town Hall Meeting Q' . ceil(now()->month / 3), 'category' => 'acara', 'pinned' => false, 'body' => 'Seluruh karyawan diundang mengikuti town hall meeting kuartalan.'],
        ];
        foreach ($announcements as $a) {
            Announcement::firstOrCreate(
                ['title' => $a['title']],
                ['body' => $a['body'], 'category' => $a['category'], 'is_pinned' => $a['pinned'], 'published_at' => now()->subDays(rand(1, 10)), 'created_by_user_id' => $this->admin->id, 'is_active' => true]
            );
        }

        $survey = Survey::firstOrCreate(
            ['title' => 'Survey Kepuasan Karyawan ' . now()->year],
            ['description' => 'Mohon isi dengan jujur, jawaban Anda anonim.', 'is_anonymous' => true, 'status' => 'draft', 'created_by_user_id' => $this->admin->id]
        );
        if ($survey->wasRecentlyCreated) {
            $q1 = SurveyQuestion::create(['survey_id' => $survey->id, 'text' => 'Seberapa puas Anda bekerja di perusahaan ini?', 'type' => 'scale', 'is_required' => true, 'sort_order' => 1]);
            $q2 = SurveyQuestion::create(['survey_id' => $survey->id, 'text' => 'Aspek apa yang paling perlu ditingkatkan?', 'type' => 'single', 'options' => ['Gaji & Benefit', 'Jenjang Karir', 'Lingkungan Kerja', 'Fasilitas'], 'is_required' => true, 'sort_order' => 2]);
            $survey->update(['status' => 'open']);

            foreach (['LOG-005', 'IT-104', 'FIN-005'] as $nip) {
                $employee = $this->e($nip);
                $response = SurveyResponse::create(['survey_id' => $survey->id, 'submitted_at' => now()->subDays(rand(1, 5))]);
                SurveyAnswer::create(['survey_response_id' => $response->id, 'survey_question_id' => $q1->id, 'value' => (string) rand(6, 10)]);
                SurveyAnswer::create(['survey_response_id' => $response->id, 'survey_question_id' => $q2->id, 'value_json' => [collect($q2->options)->random()]]);
            }
        }
    }
}
