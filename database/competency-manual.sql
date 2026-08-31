-- ============================================================================
-- Modul: Competency Framework (melengkapi PRD HRIS v1.2 Bab 3 modul #11
--        Training & Development — bagian "competency").
--
-- Setara dengan migration 2026_08_29_150000_create_competency_tables.
--
-- WAJIB backup dulu. Jalankan setelah training-career-manual.sql (butuh
-- companies/positions/employees/users sudah ada).
-- ============================================================================

CREATE TABLE `competencies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `competencies_code_unique` (`code`),
  KEY `competencies_company_id_foreign` (`company_id`),
  CONSTRAINT `competencies_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `position_competencies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `position_id` bigint(20) unsigned NOT NULL,
  `competency_id` bigint(20) unsigned NOT NULL,
  `required_level` tinyint(3) unsigned NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `position_competencies_position_id_competency_id_unique` (`position_id`,`competency_id`),
  KEY `position_competencies_competency_id_foreign` (`competency_id`),
  CONSTRAINT `position_competencies_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `position_competencies_competency_id_foreign` FOREIGN KEY (`competency_id`) REFERENCES `competencies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_competencies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `competency_id` bigint(20) unsigned NOT NULL,
  `actual_level` tinyint(3) unsigned NOT NULL,
  `assessed_on` date DEFAULT NULL,
  `assessor_user_id` bigint(20) unsigned DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_competencies_employee_id_competency_id_unique` (`employee_id`,`competency_id`),
  KEY `employee_competencies_competency_id_foreign` (`competency_id`),
  KEY `employee_competencies_assessor_user_id_foreign` (`assessor_user_id`),
  CONSTRAINT `employee_competencies_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_competencies_competency_id_foreign` FOREIGN KEY (`competency_id`) REFERENCES `competencies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_competencies_assessor_user_id_foreign` FOREIGN KEY (`assessor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Setelah tabel dibuat, WAJIB jalankan seeder berikut (idempoten):
--   php artisan db:seed --class=PermissionCatalogSeeder   (permission competency.*)
--   php artisan db:seed --class=CompetencySeeder          (~10 kompetensi default)
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_29_150000_create_competency_tables',<BATCH>);
-- ============================================================================
