-- Modul: HR (Absensi, Cuti, Lembur, Penggajian)
-- Setara dengan migration:
--   2026_07_02_200002_create_leave_types_table
--   2026_07_02_200003_create_leave_balances_table
--   2026_07_02_200004_create_leave_requests_table
--   2026_07_02_200005_create_attendance_records_table
--   2026_07_02_200006_create_salary_tables
--   2026_07_15_090001_add_calculation_fields_to_salary_components_table
--   2026_07_15_090002_reseed_salary_components
--   2026_07_16_090000_add_tunjangan_fields_to_positions_table
--   2026_07_16_090001_add_position_calc_types_to_salary_components
--   2026_07_16_090002_add_tarif_lembur_to_positions_table
--   2026_07_16_090003_add_overtime_calc_type_and_component
--
-- PRASYARAT: jalankan database/employee-master-data-manual.sql DULU
-- (butuh tabel companies, departments, positions, employees.department_id/position_id).
-- WAJIB backup database dulu sebelum jalankan. Jalankan berurutan dari atas.

-- 1) Jenis cuti + seed default
CREATE TABLE `leave_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `days_per_year` int(11) NOT NULL DEFAULT 0,
  `is_paid` tinyint(1) NOT NULL DEFAULT 1,
  `requires_doc` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_types_company_id_foreign` (`company_id`),
  CONSTRAINT `leave_types_company_id_foreign`
    FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `leave_types` (`company_id`, `name`, `days_per_year`, `is_paid`, `requires_doc`, `is_active`, `created_at`, `updated_at`) VALUES
(NULL, 'Cuti Tahunan',     12, 1, 0, 1, NOW(), NOW()),
(NULL, 'Cuti Sakit',        0, 1, 1, 1, NOW(), NOW()),
(NULL, 'Cuti Melahirkan',  90, 1, 1, 1, NOW(), NOW()),
(NULL, 'Cuti Menikah',      3, 1, 0, 1, NOW(), NOW()),
(NULL, 'Cuti Duka',         2, 1, 0, 1, NOW(), NOW()),
(NULL, 'Izin Tidak Masuk',  0, 0, 0, 1, NOW(), NOW());

-- 2) Saldo cuti per karyawan per tahun
CREATE TABLE `leave_balances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `leave_type_id` bigint(20) unsigned NOT NULL,
  `year` year(4) NOT NULL,
  `allocated` decimal(5,1) NOT NULL DEFAULT 0.0,
  `used` decimal(5,1) NOT NULL DEFAULT 0.0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leave_balances_employee_id_leave_type_id_year_unique` (`employee_id`,`leave_type_id`,`year`),
  KEY `leave_balances_leave_type_id_foreign` (`leave_type_id`),
  CONSTRAINT `leave_balances_employee_id_foreign`
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_balances_leave_type_id_foreign`
    FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Pengajuan cuti + approval berjenjang (manager -> HR)
CREATE TABLE `leave_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `leave_type_id` bigint(20) unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_days` decimal(5,1) NOT NULL,
  `reason` text DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `status` enum('draft','submitted','approved_manager','approved_hr','rejected') NOT NULL DEFAULT 'draft',
  `manager_approved_by` bigint(20) unsigned DEFAULT NULL,
  `manager_approved_at` timestamp NULL DEFAULT NULL,
  `manager_notes` text DEFAULT NULL,
  `hr_approved_by` bigint(20) unsigned DEFAULT NULL,
  `hr_approved_at` timestamp NULL DEFAULT NULL,
  `hr_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_requests_employee_id_foreign` (`employee_id`),
  KEY `leave_requests_leave_type_id_foreign` (`leave_type_id`),
  KEY `leave_requests_manager_approved_by_foreign` (`manager_approved_by`),
  KEY `leave_requests_hr_approved_by_foreign` (`hr_approved_by`),
  CONSTRAINT `leave_requests_employee_id_foreign`
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_requests_leave_type_id_foreign`
    FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`),
  CONSTRAINT `leave_requests_manager_approved_by_foreign`
    FOREIGN KEY (`manager_approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_requests_hr_approved_by_foreign`
    FOREIGN KEY (`hr_approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Absensi harian (input manual & import log mesin fingerprint)
CREATE TABLE `attendance_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `status` enum('hadir','telat','izin','sakit','cuti','alpha','libur') NOT NULL DEFAULT 'hadir',
  `late_minutes` int(11) NOT NULL DEFAULT 0,
  `overtime_minutes` int(11) NOT NULL DEFAULT 0,
  `source` enum('manual','import','mesin') NOT NULL DEFAULT 'manual',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendance_records_employee_id_date_unique` (`employee_id`,`date`),
  KEY `attendance_records_company_id_foreign` (`company_id`),
  CONSTRAINT `attendance_records_employee_id_foreign`
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_records_company_id_foreign`
    FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5) Komponen gaji master (final state — sudah termasuk seed & kalkulasi
--    otomatis yang tadinya ditambah bertahap lewat beberapa migration)
CREATE TABLE `salary_components` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('allowance','deduction') NOT NULL,
  `calculation_type` enum('manual','percent_of_base','late_deduction','medical_claim','mirror_pph21','position_fixed','position_daily','overtime') NOT NULL DEFAULT 'manual',
  `rate_percent` decimal(5,2) DEFAULT NULL,
  `salary_cap` bigint(20) DEFAULT NULL,
  `is_taxable` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_components_company_id_foreign` (`company_id`),
  CONSTRAINT `salary_components_company_id_foreign`
    FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `salary_components`
  (`company_id`,`name`,`type`,`calculation_type`,`rate_percent`,`salary_cap`,`is_taxable`,`is_active`,`sort_order`,`created_at`,`updated_at`) VALUES
(NULL, 'Gaji Pokok',                 'allowance', 'manual',          NULL, NULL, 1, 1, 1,  NOW(), NOW()),
(NULL, 'Tunjangan Transportasi',     'allowance', 'manual',          NULL, NULL, 0, 0, 2,  NOW(), NOW()),
(NULL, 'Tunjangan Makan',            'allowance', 'manual',          NULL, NULL, 0, 0, 3,  NOW(), NOW()),
(NULL, 'Tunjangan Jabatan',          'allowance', 'position_fixed',  NULL, NULL, 1, 1, 4,  NOW(), NOW()),
(NULL, 'Tunjangan Operasional',      'allowance', 'manual',          NULL, NULL, 0, 1, 5,  NOW(), NOW()),
(NULL, 'Tunjangan Makan & Transport','allowance', 'position_daily',  NULL, NULL, 0, 1, 6,  NOW(), NOW()),
(NULL, 'Medical Claim',              'allowance', 'medical_claim',   NULL, NULL, 0, 1, 7,  NOW(), NOW()),
(NULL, 'Tunjangan PPh 21',           'allowance', 'mirror_pph21',    NULL, NULL, 1, 1, 8,  NOW(), NOW()),
(NULL, 'Tunjangan Lembur',           'allowance', 'overtime',        NULL, NULL, 0, 1, 9,  NOW(), NOW()),
(NULL, 'BPJS Kesehatan',             'deduction', 'percent_of_base', 1.00, NULL, 0, 1, 10, NOW(), NOW()),
(NULL, 'BPJS Ketenagakerjaan',       'deduction', 'manual',          NULL, NULL, 0, 0, 11, NOW(), NOW()),
(NULL, 'Jaminan Pensiun',            'deduction', 'percent_of_base', 1.00, NULL, 0, 1, 11, NOW(), NOW()),
(NULL, 'Potongan Absensi',           'deduction', 'manual',          NULL, NULL, 0, 0, 12, NOW(), NOW()),
(NULL, 'Potongan Keterlambatan',     'deduction', 'late_deduction',  NULL, NULL, 0, 1, 12, NOW(), NOW()),
(NULL, 'Potongan PPh 21',            'deduction', 'manual',          NULL, NULL, 0, 1, 13, NOW(), NOW());

-- 6) Nilai komponen gaji manual per karyawan
CREATE TABLE `employee_salary_components` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `salary_component_id` bigint(20) unsigned NOT NULL,
  `amount` bigint(20) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `emp_sal_comp_unique` (`employee_id`,`salary_component_id`),
  KEY `employee_salary_components_salary_component_id_foreign` (`salary_component_id`),
  CONSTRAINT `employee_salary_components_employee_id_foreign`
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_salary_components_salary_component_id_foreign`
    FOREIGN KEY (`salary_component_id`) REFERENCES `salary_components` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7) Periode penggajian per perusahaan per bulan
CREATE TABLE `payroll_periods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `month` tinyint(4) NOT NULL,
  `year` year(4) NOT NULL,
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `notes` text DEFAULT NULL,
  `closed_by` bigint(20) unsigned DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payroll_periods_company_id_month_year_unique` (`company_id`,`month`,`year`),
  KEY `payroll_periods_closed_by_foreign` (`closed_by`),
  CONSTRAINT `payroll_periods_company_id_foreign`
    FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_periods_closed_by_foreign`
    FOREIGN KEY (`closed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8) Slip gaji per karyawan per periode
CREATE TABLE `payroll_slips` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payroll_period_id` bigint(20) unsigned NOT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `working_days` int(11) NOT NULL DEFAULT 0,
  `attendance_days` int(11) NOT NULL DEFAULT 0,
  `leave_days` decimal(5,1) NOT NULL DEFAULT 0.0,
  `alpha_days` int(11) NOT NULL DEFAULT 0,
  `late_minutes` int(11) NOT NULL DEFAULT 0,
  `total_allowances` bigint(20) NOT NULL DEFAULT 0,
  `total_deductions` bigint(20) NOT NULL DEFAULT 0,
  `gross_salary` bigint(20) NOT NULL DEFAULT 0,
  `net_salary` bigint(20) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payroll_slips_payroll_period_id_employee_id_unique` (`payroll_period_id`,`employee_id`),
  KEY `payroll_slips_employee_id_foreign` (`employee_id`),
  CONSTRAINT `payroll_slips_payroll_period_id_foreign`
    FOREIGN KEY (`payroll_period_id`) REFERENCES `payroll_periods` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_slips_employee_id_foreign`
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9) Rincian komponen per slip gaji (snapshot nominal saat payroll diproses)
CREATE TABLE `payroll_slip_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payroll_slip_id` bigint(20) unsigned NOT NULL,
  `salary_component_id` bigint(20) unsigned DEFAULT NULL,
  `component_name` varchar(100) NOT NULL,
  `type` enum('allowance','deduction') NOT NULL,
  `amount` bigint(20) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_slip_details_salary_component_id_foreign` (`salary_component_id`),
  CONSTRAINT `payroll_slip_details_payroll_slip_id_foreign`
    FOREIGN KEY (`payroll_slip_id`) REFERENCES `payroll_slips` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_slip_details_salary_component_id_foreign`
    FOREIGN KEY (`salary_component_id`) REFERENCES `salary_components` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10) Tunjangan jabatan/harian & tarif lembur melekat ke Position
--     (dipakai oleh calculation_type position_fixed/position_daily/overtime di atas)
ALTER TABLE `positions`
  ADD COLUMN `tunjangan_jabatan` bigint(20) DEFAULT NULL AFTER `name`,
  ADD COLUMN `tunjangan_harian` bigint(20) DEFAULT NULL AFTER `tunjangan_jabatan`,
  ADD COLUMN `tarif_lembur` bigint(20) DEFAULT NULL AFTER `tunjangan_harian`;

-- ============================================================
-- Setelah semua di atas selesai:
--   1. Isi tunjangan_jabatan / tunjangan_harian / tarif_lembur tiap
--      Jabatan lewat menu Data Karyawan > Jabatan (kalau mau otomatis
--      dihitung payroll — kalau tidak diisi, dianggap 0).
--   2. Buka menu HR Manajemen > Penggajian > Komponen Gaji untuk cek
--      15 komponen default sudah sesuai kebutuhan.
-- ============================================================

-- Opsional: daftarkan ke tabel migrations Laravel supaya `php artisan migrate`
-- tidak mencoba menjalankan ulang perubahan ini nanti. Cek dulu:
--   SELECT MAX(batch) FROM migrations;
-- lalu ganti angka <BATCH> di bawah dengan (hasil query di atas + 1).
--
-- INSERT INTO `migrations` (`migration`, `batch`) VALUES
-- ('2026_07_02_200002_create_leave_types_table', <BATCH>),
-- ('2026_07_02_200003_create_leave_balances_table', <BATCH>),
-- ('2026_07_02_200004_create_leave_requests_table', <BATCH>),
-- ('2026_07_02_200005_create_attendance_records_table', <BATCH>),
-- ('2026_07_02_200006_create_salary_tables', <BATCH>),
-- ('2026_07_15_090001_add_calculation_fields_to_salary_components_table', <BATCH>),
-- ('2026_07_15_090002_reseed_salary_components', <BATCH>),
-- ('2026_07_16_090000_add_tunjangan_fields_to_positions_table', <BATCH>),
-- ('2026_07_16_090001_add_position_calc_types_to_salary_components', <BATCH>),
-- ('2026_07_16_090002_add_tarif_lembur_to_positions_table', <BATCH>),
-- ('2026_07_16_090003_add_overtime_calc_type_and_component', <BATCH>);
