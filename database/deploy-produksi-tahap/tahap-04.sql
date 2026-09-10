-- Dipecah dari database/deploy-all-produksi.sql — jalankan HANYA tahap yang belum ada di prod.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- TAHAP 4 — employee-data-tabs-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: Tab Data Karyawan (Employee Database — PRD HRIS v1.2 Bab 5.2)
--   NSSF/BPJS, Pendidikan, Pengalaman Kerja, Skill, Riwayat Organisasi,
--   Fasilitas, Rekening Bank, Kontrak.
--
-- Setara dengan migration:
--   2026_08_28_100201_create_employee_nssf_table
--   2026_08_28_100202_create_employee_educations_table
--   2026_08_28_100203_create_employee_work_experiences_table
--   2026_08_28_100204_create_employee_skills_table
--   2026_08_28_100205_create_employee_org_experiences_table
--   2026_08_28_100206_create_employee_facilities_table
--   2026_08_28_100207_create_employee_bank_accounts_table
--   2026_08_28_100208_create_employee_contracts_table
--
-- WAJIB backup dulu. Jalankan SETELAH master-data-manual.sql (butuh tabel
-- education_levels, education_majors, banks). Urut dari atas.
-- ============================================================================

CREATE TABLE `employee_nssf` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `health_registered` tinyint(1) NOT NULL DEFAULT 0,
  `health_number` varchar(50) DEFAULT NULL,
  `health_join_date` date DEFAULT NULL,
  `employment_registered` tinyint(1) NOT NULL DEFAULT 0,
  `employment_number` varchar(50) DEFAULT NULL,
  `employment_join_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_nssf_employee_id_unique` (`employee_id`),
  CONSTRAINT `employee_nssf_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_educations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `education_level_id` bigint(20) unsigned DEFAULT NULL,
  `education_major_id` bigint(20) unsigned DEFAULT NULL,
  `institution` varchar(200) DEFAULT NULL,
  `graduation_year` year(4) DEFAULT NULL,
  `gpa` decimal(4,2) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_educations_employee_id_foreign` (`employee_id`),
  KEY `employee_educations_education_level_id_foreign` (`education_level_id`),
  KEY `employee_educations_education_major_id_foreign` (`education_major_id`),
  CONSTRAINT `employee_educations_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_educations_education_level_id_foreign` FOREIGN KEY (`education_level_id`) REFERENCES `education_levels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_educations_education_major_id_foreign` FOREIGN KEY (`education_major_id`) REFERENCES `education_majors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_work_experiences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `company_city` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `end_job_title` varchar(150) DEFAULT NULL,
  `end_pay_rate` bigint(20) DEFAULT NULL,
  `job_description` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_work_experiences_employee_id_foreign` (`employee_id`),
  CONSTRAINT `employee_work_experiences_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_skills` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `proficiency` enum('basic','intermediate','advanced','expert') DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_skills_employee_id_foreign` (`employee_id`),
  CONSTRAINT `employee_skills_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_org_experiences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `position_id` bigint(20) unsigned DEFAULT NULL,
  `unit_name` varchar(200) DEFAULT NULL,
  `position_name` varchar(150) DEFAULT NULL,
  `change_type` enum('join','promotion','rotation','mutation','demotion','other') NOT NULL DEFAULT 'other',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_org_experiences_employee_id_foreign` (`employee_id`),
  KEY `employee_org_experiences_company_id_foreign` (`company_id`),
  KEY `employee_org_experiences_position_id_foreign` (`position_id`),
  CONSTRAINT `employee_org_experiences_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_org_experiences_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_org_experiences_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_facilities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `received_date` date DEFAULT NULL,
  `returned_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_facilities_employee_id_foreign` (`employee_id`),
  CONSTRAINT `employee_facilities_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_bank_accounts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `bank_id` bigint(20) unsigned NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `account_holder_name` varchar(150) NOT NULL,
  `branch_name` varchar(150) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_bank_accounts_employee_id_foreign` (`employee_id`),
  KEY `employee_bank_accounts_bank_id_foreign` (`bank_id`),
  CONSTRAINT `employee_bank_accounts_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_bank_accounts_bank_id_foreign` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_contracts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `contract_type` enum('pkwtt','pkwt','probation','magang','harian','other') NOT NULL DEFAULT 'pkwt',
  `number` varchar(100) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','expired','terminated','renewed') NOT NULL DEFAULT 'active',
  `document_path` varchar(500) DEFAULT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_contracts_employee_id_foreign` (`employee_id`),
  CONSTRAINT `employee_contracts_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Backfill kontrak dari kolom ringkasan employees.contract_end_date yang ada.
INSERT INTO `employee_contracts`
  (`employee_id`, `contract_type`, `start_date`, `end_date`, `status`, `notes`, `created_at`, `updated_at`)
SELECT
  `id`,
  IF(`employment_status` = 'probation', 'probation', 'pkwt'),
  COALESCE(`start_date`, `contract_end_date`),
  `contract_end_date`,
  'active',
  'Migrasi otomatis dari kolom contract_end_date.',
  NOW(), NOW()
FROM `employees`
WHERE `contract_end_date` IS NOT NULL;

-- ============================================================================
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
--
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_100201_create_employee_nssf_table',<BATCH>),
-- ('2026_08_28_100202_create_employee_educations_table',<BATCH>),
-- ('2026_08_28_100203_create_employee_work_experiences_table',<BATCH>),
-- ('2026_08_28_100204_create_employee_skills_table',<BATCH>),
-- ('2026_08_28_100205_create_employee_org_experiences_table',<BATCH>),
-- ('2026_08_28_100206_create_employee_facilities_table',<BATCH>),
-- ('2026_08_28_100207_create_employee_bank_accounts_table',<BATCH>),
-- ('2026_08_28_100208_create_employee_contracts_table',<BATCH>);
-- ============================================================================


-- =============================================================================

SET FOREIGN_KEY_CHECKS = 1;
