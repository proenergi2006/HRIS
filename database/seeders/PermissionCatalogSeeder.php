<?php

namespace Database\Seeders;

use App\Models\RoleCompanyAssignment;
use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Bootstrap Role & Permission skema PRD (Bab 4). Aman dijalankan ulang (idempoten).
 *
 * 1. Generate semua `permissions` dari App\Support\PermissionCatalog.
 * 2. Petakan 8 role LAMA -> permission yang PERSIS SAMA dengan akses efektifnya sekarang
 *    (hasil audit routes/web.php + sidebar.blade.php sebelum migrasi ke permission-based) —
 *    supaya tidak ada perubahan perilaku sampai admin benar-benar mengedit role via UI baru.
 * 3. Tambah role baru sesuai PRD (super_admin, hr_group_admin, hr_admin, hr_payroll, recruiter)
 *    dengan mapping default yang masuk akal — role lama TIDAK dihapus/diganti paksa.
 * 4. Backfill role_company_assignments untuk semua user existing dari model_has_roles saat ini,
 *    company_id = NULL (berlaku semua company) — tidak ada yang makin terbatas dari kondisi sekarang.
 */
class PermissionCatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionCatalog::allPermissionNames() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $legacyMap = [
            'admin' => [
                'ga.view', 'ga.create', 'ga.edit', 'ga.delete',
                'reimbursement-admin.view', 'reimbursement-admin.edit', 'reimbursement-admin.delete',
                'perdin-admin.view',
                'laporan.view',
                'attendance.view', 'attendance.create',
                'overtime.view', 'overtime.create',
                'leave-admin.view', 'leave-admin.edit',
                'payroll.view', 'payroll.create', 'payroll.edit',
                'approval-workflow.view', 'approval-workflow.edit',
                'hr-request.view', 'hr-request.create', 'hr-request.edit', 'hr-request.delete', 'hr-request.approve',
                'whistleblower-admin.view', 'whistleblower-admin.edit',
                'activity-log.view',
                'master-data.view', 'master-data.create', 'master-data.edit', 'master-data.delete',
                'user-management.view', 'user-management.create', 'user-management.edit', 'user-management.delete',
                'roles-manage.view', 'roles-manage.create', 'roles-manage.edit', 'roles-manage.delete',
                'employee-master.view', 'employee-master.create', 'employee-master.edit', 'employee-master.delete',
                'org-structure.view', 'org-structure.create', 'org-structure.edit', 'org-structure.delete',
                'appraisal-config.view', 'appraisal-config.create', 'appraisal-config.edit', 'appraisal-config.delete',
                // admin sengaja TIDAK dapat appraisal-participate/perdin-participate (whitelist lama tidak
                // menyertakan admin — menu sidebar-nya memang disembunyikan untuk admin, lihat komentar di
                // sidebar.blade.php). reimbursement-participate tetap didapat (whitelist = semua kecuali admin_ga).
                'reimbursement-participate.view',
                'manpower-plan.view', 'manpower-plan.create', 'manpower-plan.edit', 'manpower-plan.delete', 'manpower-plan.approve',
                'recruitment.view', 'recruitment.create', 'recruitment.edit', 'recruitment.delete', 'recruitment.approve',
                'training.view', 'training.create', 'training.edit', 'training.delete',
                'competency.view', 'competency.create', 'competency.edit', 'competency.delete',
                'career.view', 'career.edit',
                'shift.view', 'shift.create', 'shift.edit', 'shift.delete',
                'offboarding.view', 'offboarding.create', 'offboarding.edit', 'offboarding.delete',
                'announcement.view', 'announcement.create', 'announcement.edit', 'announcement.delete',
                'survey.view', 'survey.create', 'survey.edit', 'survey.delete',
            ],
            'admin_ga' => [
                'ga.view', 'ga.create', 'ga.edit', 'ga.delete',
            ],
            'hr_manager' => [
                'perdin-admin.view',
                'laporan.view',
                'attendance.view', 'attendance.create',
                'overtime.view', 'overtime.create',
                'leave-admin.view', 'leave-admin.edit',
                'payroll.view', 'payroll.create', 'payroll.edit',
                'approval-workflow.view', 'approval-workflow.edit',
                'hr-request.view', 'hr-request.create', 'hr-request.edit', 'hr-request.delete', 'hr-request.approve',
                'appraisal-participate.view', 'perdin-participate.view', 'reimbursement-participate.view',
                'manpower-plan.view', 'manpower-plan.create', 'manpower-plan.edit', 'manpower-plan.delete', 'manpower-plan.approve',
                'recruitment.view', 'recruitment.create', 'recruitment.edit', 'recruitment.delete', 'recruitment.approve',
                'training.view', 'training.create', 'training.edit', 'training.delete',
                'competency.view', 'competency.create', 'competency.edit', 'competency.delete',
                'career.view', 'career.edit',
                'shift.view', 'shift.create', 'shift.edit', 'shift.delete',
                'offboarding.view', 'offboarding.create', 'offboarding.edit', 'offboarding.delete',
                'announcement.view', 'announcement.create', 'announcement.edit', 'announcement.delete',
                'survey.view', 'survey.create', 'survey.edit', 'survey.delete',
            ],
            'evaluator' => [
                'appraisal-participate.view', 'perdin-participate.view', 'reimbursement-participate.view',
            ],
            'user_ii' => [
                'appraisal-participate.view', 'perdin-participate.view', 'reimbursement-participate.view',
            ],
            'cfo' => [
                'appraisal-participate.view', 'perdin-participate.view', 'reimbursement-participate.view',
            ],
            'ceo' => [
                'appraisal-participate.view', 'perdin-participate.view', 'reimbursement-participate.view',
            ],
            'karyawan' => [
                'reimbursement-participate.view',
            ],
        ];

        foreach ($legacyMap as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
        }

        // Role baru sesuai PRD Bab 4 — TAMBAHAN, bukan pengganti 8 role lama.
        $newRoleMap = [
            'super_admin'    => PermissionCatalog::allPermissionNames(), // akses penuh
            'hr_group_admin' => [
                'master-data.view', 'master-data.create', 'master-data.edit', 'master-data.delete',
                'org-structure.view', 'org-structure.create', 'org-structure.edit', 'org-structure.delete',
                'approval-workflow.view', 'approval-workflow.edit',
                'laporan.view',
                'user-management.view', 'user-management.create', 'user-management.edit', 'user-management.delete',
                'roles-manage.view', 'roles-manage.create', 'roles-manage.edit', 'roles-manage.delete',
                'manpower-plan.view', 'manpower-plan.approve',
                'career.view', 'career.edit',
            ],
            'hr_admin' => [
                'employee-master.view', 'employee-master.create', 'employee-master.edit', 'employee-master.delete',
                'attendance.view', 'attendance.create',
                'overtime.view', 'overtime.create',
                'leave-admin.view', 'leave-admin.edit',
                'perdin-admin.view',
                'reimbursement-admin.view', 'reimbursement-admin.edit', 'reimbursement-admin.delete',
                'hr-request.view', 'hr-request.create', 'hr-request.edit', 'hr-request.delete', 'hr-request.approve',
                'laporan.view',
                'manpower-plan.view', 'manpower-plan.create', 'manpower-plan.edit', 'manpower-plan.delete', 'manpower-plan.approve',
                'recruitment.view', 'recruitment.create', 'recruitment.edit', 'recruitment.delete', 'recruitment.approve',
                'training.view', 'training.create', 'training.edit', 'training.delete',
                'competency.view', 'competency.create', 'competency.edit', 'competency.delete',
                'career.view', 'career.edit',
                'shift.view', 'shift.create', 'shift.edit', 'shift.delete',
                'offboarding.view', 'offboarding.create', 'offboarding.edit', 'offboarding.delete',
                'announcement.view', 'announcement.create', 'announcement.edit', 'announcement.delete',
                'survey.view', 'survey.create', 'survey.edit', 'survey.delete',
            ],
            'hr_payroll' => [
                'payroll.view', 'payroll.create', 'payroll.edit',
            ],
            'recruiter' => [
                'recruitment.view', 'recruitment.create', 'recruitment.edit', 'recruitment.delete',
            ],
        ];

        foreach ($newRoleMap as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            if ($permissions) {
                $role->syncPermissions($permissions);
            }
        }

        // Backfill role_company_assignments dari model_has_roles saat ini — company_id NULL
        // (berlaku semua company) supaya tidak ada user yang makin terbatas dari kondisi sekarang.
        User::with('roles')->chunk(200, function ($users) {
            foreach ($users as $user) {
                foreach ($user->roles as $role) {
                    RoleCompanyAssignment::firstOrCreate([
                        'user_id'    => $user->id,
                        'role_id'    => $role->id,
                        'company_id' => null,
                    ]);
                }
            }
        });
    }
}
