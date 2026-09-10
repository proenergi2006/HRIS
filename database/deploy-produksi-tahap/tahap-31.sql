-- Dipecah dari database/deploy-all-produksi.sql — jalankan HANYA tahap yang belum ada di prod.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- TAHAP 31 — gap4-extras-manual.sql
-- =============================================================================
-- =============================================================================
-- Modul: 7 gap lanjutan hasil review kematangan HR lebih dalam —
--        Notification Center (perluas cakupan, TANPA skema baru di sini),
--        Merit Increase (salary_increase_requests, lewat Approval Engine),
--        Employee Referral (candidates.referred_by_employee_id dkk),
--        Probation Review (probation_reviews),
--        Kalender Cuti Tim (TANPA skema baru — read-only dari leave_requests),
--        Riwayat Grid 9-Kotak & Struktur Gaji (employee_potential_history,
--        salary_grade_history), Pulse Survey auto-recurring (surveys.recurrence
--        + parent_survey_id).
--
-- Setara dengan migration:
--   2026_09_01_110000_create_gap4_extras
--
-- WAJIB backup dulu. Jalankan SETELAH:
--   - migrasi dasar (butuh `employees`, `companies`, `users`)
--   - master-organization-manual.sql (butuh `levels`)
--   - candidate-database-ats-manual.sql (butuh `candidates`)
--   - compensation-succession-survey-extras-manual.sql (butuh `surveys.type`,
--     kolom `surveys` sudah ada duluan)
-- =============================================================================

CREATE TABLE `salary_increase_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `requested_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `current_salary` bigint(20) unsigned DEFAULT NULL,
  `proposed_salary` bigint(20) unsigned NOT NULL,
  `effective_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',   -- draft/pending/approved/rejected/cancelled
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_increase_requests_employee_id_foreign` (`employee_id`),
  KEY `salary_increase_requests_company_id_foreign` (`company_id`),
  KEY `salary_increase_requests_requested_by_user_id_foreign` (`requested_by_user_id`),
  CONSTRAINT `salary_increase_requests_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `salary_increase_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `salary_increase_requests_requested_by_user_id_foreign` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `candidates`
  ADD COLUMN `referred_by_employee_id` bigint(20) unsigned DEFAULT NULL AFTER `source`,
  ADD COLUMN `referral_bonus_amount` bigint(20) unsigned DEFAULT NULL AFTER `referred_by_employee_id`,
  ADD COLUMN `referral_bonus_paid_at` date DEFAULT NULL AFTER `referral_bonus_amount`,
  ADD CONSTRAINT `candidates_referred_by_employee_id_foreign`
    FOREIGN KEY (`referred_by_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

CREATE TABLE `probation_reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `review_date` date NOT NULL,
  `decision` varchar(20) NOT NULL,   -- passed/extended/failed
  `performance_notes` text DEFAULT NULL,
  `extended_until` date DEFAULT NULL,
  `reviewed_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `probation_reviews_employee_id_foreign` (`employee_id`),
  KEY `probation_reviews_reviewed_by_user_id_foreign` (`reviewed_by_user_id`),
  CONSTRAINT `probation_reviews_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `probation_reviews_reviewed_by_user_id_foreign` FOREIGN KEY (`reviewed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_potential_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `potential_rating` varchar(10) NOT NULL,   -- low/medium/high
  `notes` text DEFAULT NULL,
  `assessed_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `assessed_at` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_potential_history_employee_id_foreign` (`employee_id`),
  KEY `employee_potential_history_assessed_by_user_id_foreign` (`assessed_by_user_id`),
  CONSTRAINT `employee_potential_history_assessed_by_user_id_foreign` FOREIGN KEY (`assessed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_potential_history_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `salary_grade_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `level_id` bigint(20) unsigned NOT NULL,
  `grade_min` bigint(20) unsigned NOT NULL,
  `grade_mid` bigint(20) unsigned NOT NULL,
  `grade_max` bigint(20) unsigned NOT NULL,
  `changed_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_grade_history_company_id_foreign` (`company_id`),
  KEY `salary_grade_history_level_id_foreign` (`level_id`),
  KEY `salary_grade_history_changed_by_user_id_foreign` (`changed_by_user_id`),
  CONSTRAINT `salary_grade_history_changed_by_user_id_foreign` FOREIGN KEY (`changed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `salary_grade_history_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `salary_grade_history_level_id_foreign` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `surveys`
  ADD COLUMN `recurrence` varchar(20) NOT NULL DEFAULT 'none' AFTER `type`,   -- none/monthly/quarterly
  ADD COLUMN `parent_survey_id` bigint(20) unsigned DEFAULT NULL AFTER `recurrence`,
  ADD CONSTRAINT `surveys_parent_survey_id_foreign`
    FOREIGN KEY (`parent_survey_id`) REFERENCES `surveys` (`id`) ON DELETE SET NULL;

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_09_01_110000_create_gap4_extras', <BATCH>);
-- =============================================================================

-- =============================================================================
-- SETELAH kode live, jalankan ulang seeder berikut (idempoten, aman diulang):
--   php artisan db:seed --class=ApprovalWorkflowSeeder
-- (menambahkan workflow default 'salary_increase_request' — direct_manager →
--  hr_manager → ceo — untuk 3 PT, TANPA mengubah workflow existing lain)
-- =============================================================================


-- =============================================================================

SET FOREIGN_KEY_CHECKS = 1;
