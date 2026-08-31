<?php

namespace App\Support;

/**
 * Sumber tunggal daftar modul & aksi untuk sistem Role & Permission (PRD Bab 4).
 * Dipakai BARENG oleh:
 *  - database/seeders/PermissionCatalogSeeder.php (generate baris `permissions`)
 *  - app/Http/Controllers/Admin/RoleController.php (render matrix checkbox modul x aksi)
 *
 * Nama permission = "<module>.<action>" (guard "web"), sesuai konvensi spatie/laravel-permission.
 * Menambah modul baru (mis. modul Manpower Planning / Recruitment): tambah 1 entri di sini,
 * lalu jalankan ulang PermissionCatalogSeeder — tidak perlu migrasi baru.
 */
class PermissionCatalog
{
    /**
     * @return array<string, array{label: string, actions: string[]}>
     */
    public static function modules(): array
    {
        return [
            'ga'                        => ['label' => 'General Affairs',                       'actions' => ['view', 'create', 'edit', 'delete']],
            'reimbursement-admin'       => ['label' => 'Reimbursement (Admin)',                  'actions' => ['view', 'edit', 'delete']],
            'perdin-admin'              => ['label' => 'Perjalanan Dinas (Admin)',                'actions' => ['view']],
            'laporan'                   => ['label' => 'Laporan & Export',                        'actions' => ['view']],
            'attendance'                => ['label' => 'Absensi',                                 'actions' => ['view', 'create']],
            'overtime'                  => ['label' => 'Lembur',                                  'actions' => ['view', 'create']],
            'leave-admin'               => ['label' => 'Cuti (Admin)',                            'actions' => ['view', 'edit']],
            'payroll'                   => ['label' => 'Penggajian',                              'actions' => ['view', 'create', 'edit']],
            'approval-workflow'         => ['label' => 'Pengaturan Alur Persetujuan',             'actions' => ['view', 'edit']],
            'hr-request'                => ['label' => 'Reward / Punishment / Promosi / Termination', 'actions' => ['view', 'create', 'edit', 'delete', 'approve']],
            'whistleblower-admin'       => ['label' => 'Whistleblower (Admin)',                   'actions' => ['view', 'edit']],
            'activity-log'              => ['label' => 'Activity Log',                            'actions' => ['view']],
            'master-data'               => ['label' => 'Master Data Referensi',                   'actions' => ['view', 'create', 'edit', 'delete']],
            'user-management'          => ['label' => 'Manajemen User',                          'actions' => ['view', 'create', 'edit', 'delete']],
            'roles-manage'              => ['label' => 'Role & Hak Akses',                        'actions' => ['view', 'create', 'edit', 'delete']],
            'employee-master'          => ['label' => 'Data Karyawan',                           'actions' => ['view', 'create', 'edit', 'delete']],
            'org-structure'             => ['label' => 'Struktur Organisasi',                     'actions' => ['view', 'create', 'edit', 'delete']],
            'appraisal-config'         => ['label' => 'Konfigurasi Penilaian Kinerja',            'actions' => ['view', 'create', 'edit', 'delete']],
            'appraisal-participate'    => ['label' => 'Partisipasi Penilaian Kinerja',            'actions' => ['view']],
            'perdin-participate'       => ['label' => 'Partisipasi Perjalanan Dinas',             'actions' => ['view']],
            'reimbursement-participate' => ['label' => 'Partisipasi Reimbursement',               'actions' => ['view']],
            'manpower-plan'             => ['label' => 'Manpower Planning',                       'actions' => ['view', 'create', 'edit', 'delete', 'approve']],
            'recruitment'               => ['label' => 'Recruitment & Onboarding',                'actions' => ['view', 'create', 'edit', 'delete', 'approve']],
            'training'                  => ['label' => 'Training & Development',                  'actions' => ['view', 'create', 'edit', 'delete']],
            'competency'                => ['label' => 'Competency Framework',                    'actions' => ['view', 'create', 'edit', 'delete']],
            'career'                    => ['label' => 'Career Management',                        'actions' => ['view', 'edit']],
            'shift'                     => ['label' => 'Shift & Roster',                          'actions' => ['view', 'create', 'edit', 'delete']],
            'offboarding'              => ['label' => 'Clearance Resign (Offboarding)',          'actions' => ['view', 'create', 'edit', 'delete']],
            'announcement'             => ['label' => 'Pengumuman',                              'actions' => ['view', 'create', 'edit', 'delete']],
            'survey'                    => ['label' => 'Survey Engagement',                       'actions' => ['view', 'create', 'edit', 'delete']],
        ];
    }

    /** Semua nama permission "module.action" yang seharusnya ada di tabel `permissions`. */
    public static function allPermissionNames(): array
    {
        $names = [];
        foreach (self::modules() as $module => $def) {
            foreach ($def['actions'] as $action) {
                $names[] = "{$module}.{$action}";
            }
        }
        return $names;
    }

    public static function moduleLabel(string $module): string
    {
        return self::modules()[$module]['label'] ?? $module;
    }
}
