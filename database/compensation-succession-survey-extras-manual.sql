-- =============================================================================
-- Modul: 3 area "Kematangan HR" lanjutan — Compensation (benchmark gaji per
--        Level), Succession Planning (jabatan kritikal + talent pool), Survey
--        pulse & eNPS (surveys.type). Skema saja — 1 migrasi utk 3 area.
--
-- Setara dengan migration:
--   2026_08_31_180000_create_compensation_succession_survey_extras
--
-- WAJIB backup dulu. Jalankan SETELAH:
--   - master-organization-manual.sql (butuh `levels`)
--   - master-data-manual.sql / migrasi awal (butuh `positions`, `employees`, `users`)
--   - engagement-analytics-manual.sql (butuh `surveys`)
-- =============================================================================

CREATE TABLE `salary_benchmarks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `level_id` bigint(20) unsigned NOT NULL,
  `market_min` bigint(20) unsigned NOT NULL,
  `market_mid` bigint(20) unsigned NOT NULL,
  `market_max` bigint(20) unsigned NOT NULL,
  `source` varchar(150) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `updated_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `salary_benchmarks_level_id_unique` (`level_id`),
  KEY `salary_benchmarks_updated_by_user_id_foreign` (`updated_by_user_id`),
  CONSTRAINT `salary_benchmarks_level_id_foreign` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`) ON DELETE CASCADE,
  CONSTRAINT `salary_benchmarks_updated_by_user_id_foreign` FOREIGN KEY (`updated_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `positions`
  ADD COLUMN `is_critical_position` tinyint(1) NOT NULL DEFAULT 0 AFTER `tarif_lembur`,
  ADD COLUMN `succession_risk` varchar(10) DEFAULT NULL AFTER `is_critical_position`,
  ADD COLUMN `succession_notes` text DEFAULT NULL AFTER `succession_risk`;

CREATE TABLE `talent_pool_members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `position_id` bigint(20) unsigned NOT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `readiness` varchar(20) NOT NULL,             -- ready_now/ready_1_2yr/ready_3_5yr/development
  `development_notes` text DEFAULT NULL,
  `added_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `talent_pool_unique` (`position_id`,`employee_id`),
  KEY `talent_pool_members_employee_id_foreign` (`employee_id`),
  KEY `talent_pool_members_added_by_user_id_foreign` (`added_by_user_id`),
  CONSTRAINT `talent_pool_members_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `talent_pool_members_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `talent_pool_members_added_by_user_id_foreign` FOREIGN KEY (`added_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `surveys`
  ADD COLUMN `type` varchar(20) NOT NULL DEFAULT 'standard' AFTER `is_anonymous`; -- standard/pulse/enps

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_08_31_180000_create_compensation_succession_survey_extras', <BATCH>);
-- =============================================================================
