-- Dipecah dari database/deploy-all-produksi.sql — jalankan HANYA tahap yang belum ada di prod.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- TAHAP 11 — manpower-planning-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: Manpower Planning (PRD HRIS v1.2 Bab 3 modul #2)
--
-- Setara dengan migration 2026_08_28_100701_create_manpower_plans_table.
--
-- WAJIB backup dulu. Jalankan setelah role-permission-manual.sql &
-- approval-engine-manual.sql (butuh company/departments/sections/positions/users sudah ada).
-- ============================================================================

CREATE TABLE `manpower_plans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `section_id` bigint(20) unsigned DEFAULT NULL,
  `position_id` bigint(20) unsigned DEFAULT NULL,
  `year` smallint(5) unsigned NOT NULL,
  `month` tinyint(3) unsigned DEFAULT NULL,
  `planned_headcount` int(10) unsigned NOT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `requested_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `notes_rejection` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `manpower_plans_company_id_foreign` (`company_id`),
  KEY `manpower_plans_department_id_foreign` (`department_id`),
  KEY `manpower_plans_section_id_foreign` (`section_id`),
  KEY `manpower_plans_position_id_foreign` (`position_id`),
  KEY `manpower_plans_requested_by_user_id_foreign` (`requested_by_user_id`),
  CONSTRAINT `manpower_plans_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `manpower_plans_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `manpower_plans_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `manpower_plans_requested_by_user_id_foreign` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `manpower_plans_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Setelah tabel dibuat, WAJIB jalankan ulang seeder berikut (idempoten):
--   php artisan db:seed --class=PermissionCatalogSeeder   (permission manpower-plan.*)
--   php artisan db:seed --class=ApprovalWorkflowSeeder    (workflow manpower_plan_request per company)
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_100701_create_manpower_plans_table',<BATCH>);
-- ============================================================================


-- =============================================================================

SET FOREIGN_KEY_CHECKS = 1;
