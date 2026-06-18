<?php

namespace Database\Seeders;

use App\Models\Appraisal\AppraisalFlowConfig;
use App\Models\Appraisal\AppraisalPeriod;
use App\Models\Employee;
use App\Models\Level;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class ITDemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── Pastikan role ceo ada ──────────────────────────────────────────
        Role::firstOrCreate(['name' => 'ceo', 'guard_name' => 'web']);

        // ── Level SPV (sudah ada dari LevelSeeder) ────────────────────────
        $spvLevel = Level::where('name', 'SPV')->firstOrFail();

        // ── Karyawan IT level SPV (yang dinilai) ──────────────────────────
        // Hanya SPV ke atas yang masuk penilaian — Staff tidak masuk
        $itEmployees = [
            [
                'name'              => 'Andi Saputra',
                'nip'               => 'IT-001',
                'lob'               => 'Technology',
                'department'        => 'IT',
                'position'          => 'SPV Programmer',
                'level_id'          => $spvLevel->id,
                'start_date'        => '2021-03-01',
                'employment_status' => 'permanent',
                'is_active'         => true,
            ],
            [
                'name'              => 'Candra Wijaya',
                'nip'               => 'IT-003',
                'lob'               => 'Technology',
                'department'        => 'IT',
                'position'          => 'SPV Infrastructure',
                'level_id'          => $spvLevel->id,
                'start_date'        => '2020-01-10',
                'employment_status' => 'permanent',
                'is_active'         => true,
            ],
        ];

        foreach ($itEmployees as $emp) {
            Employee::updateOrCreate(['nip' => $emp['nip']], $emp);
        }

        // ── Nonaktifkan karyawan Staff IT yang salah dibuat sebelumnya ─────
        Employee::whereIn('nip', ['IT-002', 'IT-004'])->update(['is_active' => false]);

        // ── User accounts untuk demo IT ───────────────────────────────────
        // IT Manager = evaluator (yang mengisi form penilaian untuk SPV)
        // CEO = final approver untuk dept IT
        $itUsers = [
            [
                'name'     => 'IT Manager',
                'email'    => 'it.manager@proenergi.co.id',
                'password' => bcrypt('password'),
                'role'     => 'evaluator',
            ],
            [
                'name'     => 'Direktur IT',
                'email'    => 'direktur.it@proenergi.co.id',
                'password' => bcrypt('password'),
                'role'     => 'user_ii',
            ],
            [
                'name'     => 'CEO',
                'email'    => 'ceo@proenergi.co.id',
                'password' => bcrypt('password'),
                'role'     => 'ceo',
            ],
        ];

        foreach ($itUsers as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                ['name' => $u['name'], 'password' => $u['password']]
            );
            $user->syncRoles([$u['role']]);
        }

        // Nonaktifkan akun SPV yang salah konsep sebelumnya
        foreach (['spv.programmer@proenergi.co.id', 'spv.infra@proenergi.co.id'] as $email) {
            User::where('email', $email)->update(['name' => '[disabled] ' . $email]);
        }

        // ── Periode penilaian ─────────────────────────────────────────────
        AppraisalPeriod::firstOrCreate(
            ['name' => 'Penilaian Kinerja 2025'],
            [
                'year'       => 2025,
                'start_date' => '2025-01-01',
                'end_date'   => '2025-12-31',
                'status'     => 'open',
            ]
        );

        // ── Pastikan flow config IT ada (step1=user_ii, step2=ceo) ────────
        AppraisalFlowConfig::updateOrCreate(
            ['department' => 'IT', 'step' => 1],
            ['role' => 'user_ii', 'label' => 'Direktur IT']
        );
        AppraisalFlowConfig::updateOrCreate(
            ['department' => 'IT', 'step' => 2],
            ['role' => 'ceo', 'label' => 'CEO']
        );
    }
}
