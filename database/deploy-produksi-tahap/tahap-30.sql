-- Dipecah dari database/deploy-all-produksi.sql — jalankan HANYA tahap yang belum ada di prod.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- TAHAP 30 — notification-talent-extras-manual.sql
-- =============================================================================
-- =============================================================================
-- Modul: 4 gap tambahan hasil review kematangan HR lanjutan —
--        (1) Notification Center (bel notifikasi in-app, tabel `notifications`
--            standar Laravel — User sudah pakai trait Notifiable dari awal),
--        (2) Struktur Gaji Internal / Salary Grade per Level (beda dari
--            salary_benchmarks yang acuan pasar eksternal),
--        (3) Grid 9-Kotak — `employees.potential_rating` dkk (sumbu performa
--            dari total_score Appraisal terakhir, tidak butuh kolom baru),
--        (4) Recognition / Kudos — apresiasi non-finansial antar karyawan.
--
-- Setara dengan migration:
--   2026_09_01_100000_create_notification_and_talent_extras
--
-- WAJIB backup dulu. Jalankan SETELAH:
--   - master-organization-manual.sql (butuh `levels`)
--   - migrasi dasar (butuh `companies`, `employees`, `users`)
-- =============================================================================

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `salary_grades` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,   -- NULL = berlaku semua PT (global/fallback)
  `level_id` bigint(20) unsigned NOT NULL,
  `grade_min` bigint(20) unsigned NOT NULL,
  `grade_mid` bigint(20) unsigned NOT NULL,
  `grade_max` bigint(20) unsigned NOT NULL,
  `notes` text DEFAULT NULL,
  `updated_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_grades_company_id_foreign` (`company_id`),
  KEY `salary_grades_level_id_foreign` (`level_id`),
  KEY `salary_grades_updated_by_user_id_foreign` (`updated_by_user_id`),
  CONSTRAINT `salary_grades_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `salary_grades_level_id_foreign` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`) ON DELETE CASCADE,
  CONSTRAINT `salary_grades_updated_by_user_id_foreign` FOREIGN KEY (`updated_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `employees`
  ADD COLUMN `potential_rating` varchar(10) DEFAULT NULL AFTER `is_active`,           -- low/medium/high
  ADD COLUMN `potential_notes` text DEFAULT NULL AFTER `potential_rating`,
  ADD COLUMN `potential_assessed_at` date DEFAULT NULL AFTER `potential_notes`,
  ADD COLUMN `potential_assessed_by_user_id` bigint(20) unsigned DEFAULT NULL AFTER `potential_assessed_at`,
  ADD CONSTRAINT `employees_potential_assessed_by_user_id_foreign`
    FOREIGN KEY (`potential_assessed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

CREATE TABLE `kudos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `from_employee_id` bigint(20) unsigned NOT NULL,
  `to_employee_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `category` varchar(30) NOT NULL,   -- teamwork/innovation/leadership/customer_focus/integrity/excellence
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kudos_from_employee_id_foreign` (`from_employee_id`),
  KEY `kudos_to_employee_id_foreign` (`to_employee_id`),
  KEY `kudos_company_id_foreign` (`company_id`),
  CONSTRAINT `kudos_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `kudos_from_employee_id_foreign` FOREIGN KEY (`from_employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kudos_to_employee_id_foreign` FOREIGN KEY (`to_employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_09_01_100000_create_notification_and_talent_extras', <BATCH>);
-- =============================================================================


-- =============================================================================

SET FOREIGN_KEY_CHECKS = 1;
