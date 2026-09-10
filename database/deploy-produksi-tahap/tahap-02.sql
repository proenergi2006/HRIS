-- Dipecah dari database/deploy-all-produksi.sql — jalankan HANYA tahap yang belum ada di prod.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- TAHAP 2 — master-organization-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: Master Organization — Company > Division > Department > Section > Position
--        (mengikuti PRD HRIS v1.2)
--
-- Setara dengan migration:
--   2026_08_28_100101_create_divisions_table
--   2026_08_28_100102_create_sections_table
--   2026_08_28_100103_add_org_fields_to_departments_table
--   2026_08_28_100104_add_org_fields_to_positions_table
--   2026_08_28_100105_add_division_section_to_employees_table
--   2026_08_28_100106_create_org_change_logs_table
--
-- WAJIB backup dulu. Jalankan SETELAH database/master-data-manual.sql
-- (butuh tabel employees & companies sudah siap). Urut dari atas.
-- ============================================================================

-- 1) Divisi -----------------------------------------------------------------
CREATE TABLE `divisions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `head_employee_id` bigint(20) unsigned DEFAULT NULL,
  `cost_center` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `divisions_code_unique` (`code`),
  KEY `divisions_company_id_foreign` (`company_id`),
  KEY `divisions_head_employee_id_foreign` (`head_employee_id`),
  CONSTRAINT `divisions_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `divisions_head_employee_id_foreign` FOREIGN KEY (`head_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Section --------------------------------------------------------------
CREATE TABLE `sections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `department_id` bigint(20) unsigned NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `head_employee_id` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sections_code_unique` (`code`),
  KEY `sections_department_id_foreign` (`department_id`),
  KEY `sections_head_employee_id_foreign` (`head_employee_id`),
  CONSTRAINT `sections_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sections_head_employee_id_foreign` FOREIGN KEY (`head_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Kolom baru di departments ------------------------------------------
ALTER TABLE `departments`
  ADD COLUMN `division_id` bigint(20) unsigned DEFAULT NULL AFTER `company_id`,
  ADD COLUMN `head_employee_id` bigint(20) unsigned DEFAULT NULL AFTER `name`,
  ADD COLUMN `cost_center` varchar(50) DEFAULT NULL AFTER `head_employee_id`,
  ADD CONSTRAINT `departments_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `departments_head_employee_id_foreign` FOREIGN KEY (`head_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

-- 4) Kolom baru di positions -------------------------------------------
ALTER TABLE `positions`
  ADD COLUMN `section_id` bigint(20) unsigned DEFAULT NULL AFTER `department_id`,
  ADD COLUMN `level_id` bigint(20) unsigned DEFAULT NULL AFTER `section_id`,
  ADD COLUMN `reports_to_position_id` bigint(20) unsigned DEFAULT NULL AFTER `level_id`,
  ADD COLUMN `job_description` text DEFAULT NULL AFTER `name`,
  ADD CONSTRAINT `positions_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `positions_level_id_foreign` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `positions_reports_to_position_id_foreign` FOREIGN KEY (`reports_to_position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL;

-- 5) Kolom baru di employees ------------------------------------------
ALTER TABLE `employees`
  ADD COLUMN `division_id` bigint(20) unsigned DEFAULT NULL AFTER `department_id`,
  ADD COLUMN `section_id` bigint(20) unsigned DEFAULT NULL AFTER `division_id`,
  ADD CONSTRAINT `employees_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL;

-- Catatan: division_id & section_id karyawan sengaja dibiarkan NULL —
-- diisi HR bertahap lewat menu setelah struktur baru dibuat.

-- 6) Audit riwayat perubahan struktur ---------------------------------
CREATE TABLE `org_change_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `unit_type` enum('division','department','section','position') NOT NULL,
  `unit_id` bigint(20) unsigned NOT NULL,
  `unit_name` varchar(150) DEFAULT NULL,
  `action` enum('created','updated','moved','deactivated','deleted') NOT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changes`)),
  `effective_date` date NOT NULL,
  `changed_by` bigint(20) unsigned DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `org_change_logs_changed_by_foreign` (`changed_by`),
  KEY `org_change_logs_unit_type_unit_id_index` (`unit_type`,`unit_id`),
  KEY `org_change_logs_effective_date_index` (`effective_date`),
  CONSTRAINT `org_change_logs_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Setelah selesai:
--   1. Buka "Struktur Organisasi > Divisi/Departemen/Section/Jabatan" untuk
--      lengkapi divisi & section, lalu petakan karyawan.
--   2. Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
--
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_100101_create_divisions_table',<BATCH>),
-- ('2026_08_28_100102_create_sections_table',<BATCH>),
-- ('2026_08_28_100103_add_org_fields_to_departments_table',<BATCH>),
-- ('2026_08_28_100104_add_org_fields_to_positions_table',<BATCH>),
-- ('2026_08_28_100105_add_division_section_to_employees_table',<BATCH>),
-- ('2026_08_28_100106_create_org_change_logs_table',<BATCH>);
-- ============================================================================


-- =============================================================================

SET FOREIGN_KEY_CHECKS = 1;
