-- Dipecah dari database/deploy-all-produksi.sql — jalankan HANYA tahap yang belum ada di prod.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- TAHAP 27 — performance-management-extras-manual.sql
-- =============================================================================
-- =============================================================================
-- Modul: Performance Management — pelengkap (Continuous Feedback/1-on-1,
--        OKR cascading, 360° Feedback), di luar appraisal formal KPI yang sudah ada.
--
-- Setara dengan migration:
--   2026_08_31_170000_create_performance_extras_tables
--
-- WAJIB backup dulu. Jalankan SETELAH appraisal-kpi-manual.sql (butuh tabel
-- `appraisal_objectives`) dan master-organization-manual.sql (butuh `departments`).
-- =============================================================================

-- ── 1-on-1 / Continuous Feedback ─────────────────────────────────────────────
CREATE TABLE `performance_checkins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `created_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `checkin_date` date NOT NULL,
  `notes` text NOT NULL,
  `action_items` text DEFAULT NULL,
  `employee_comment` text DEFAULT NULL,
  `next_checkin_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `performance_checkins_employee_id_foreign` (`employee_id`),
  KEY `performance_checkins_created_by_user_id_foreign` (`created_by_user_id`),
  CONSTRAINT `performance_checkins_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `performance_checkins_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── OKR — Sasaran Perusahaan/Departemen berjenjang ──────────────────────────
CREATE TABLE `company_objectives` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `parent_objective_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `year` smallint(5) unsigned NOT NULL,
  `quarter` tinyint(3) unsigned DEFAULT NULL,
  `owner_employee_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company_objectives_company_id_foreign` (`company_id`),
  KEY `company_objectives_department_id_foreign` (`department_id`),
  KEY `company_objectives_parent_objective_id_foreign` (`parent_objective_id`),
  KEY `company_objectives_owner_employee_id_foreign` (`owner_employee_id`),
  KEY `company_objectives_created_by_user_id_foreign` (`created_by_user_id`),
  CONSTRAINT `company_objectives_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `company_objectives_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `company_objectives_parent_objective_id_foreign` FOREIGN KEY (`parent_objective_id`) REFERENCES `company_objectives` (`id`) ON DELETE SET NULL,
  CONSTRAINT `company_objectives_owner_employee_id_foreign` FOREIGN KEY (`owner_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `company_objectives_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `appraisal_objectives`
  ADD COLUMN `company_objective_id` bigint(20) unsigned DEFAULT NULL AFTER `appraisal_id`,
  ADD CONSTRAINT `appraisal_objectives_company_objective_id_foreign`
      FOREIGN KEY (`company_objective_id`) REFERENCES `company_objectives` (`id`) ON DELETE SET NULL;
-- Catatan: kolom ini SUDAH bisa dipakai lewat query/API, tapi form Edit Appraisal
-- (resources/views/appraisal/appraisal/edit.blade.php) BELUM ada dropdown-nya —
-- OKR sekarang berjalan sebagai tracker berjenjang berdiri sendiri (progres per
-- sasaran = "Belum ada KPI tertaut" sampai penautan lewat UI dikerjakan menyusul).

-- ── 360° Feedback ────────────────────────────────────────────────────────────
CREATE TABLE `feedback_360_cycles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `title` varchar(150) NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `created_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `feedback_360_cycles_company_id_foreign` (`company_id`),
  KEY `feedback_360_cycles_created_by_user_id_foreign` (`created_by_user_id`),
  CONSTRAINT `feedback_360_cycles_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `feedback_360_cycles_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `feedback_360_reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cycle_id` bigint(20) unsigned NOT NULL,
  `subject_employee_id` bigint(20) unsigned NOT NULL,
  `rater_employee_id` bigint(20) unsigned NOT NULL,
  `relation_type` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `submitted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `f360_review_unique` (`cycle_id`,`subject_employee_id`,`rater_employee_id`),
  KEY `feedback_360_reviews_subject_employee_id_foreign` (`subject_employee_id`),
  KEY `feedback_360_reviews_rater_employee_id_foreign` (`rater_employee_id`),
  CONSTRAINT `feedback_360_reviews_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `feedback_360_cycles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `feedback_360_reviews_subject_employee_id_foreign` FOREIGN KEY (`subject_employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `feedback_360_reviews_rater_employee_id_foreign` FOREIGN KEY (`rater_employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `feedback_360_answers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `review_id` bigint(20) unsigned NOT NULL,
  `question_key` varchar(60) NOT NULL,
  `rating` tinyint(3) unsigned DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `f360_answer_unique` (`review_id`,`question_key`),
  CONSTRAINT `feedback_360_answers_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `feedback_360_reviews` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Pertanyaan kompetensi (communication/teamwork/quality/reliability/leadership/
-- problem_solving) TETAP, didefinisikan di App\Models\Appraisal\Feedback360Review::$questions
-- (bukan tabel referensi) — supaya skema tetap ringkas.

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_08_31_170000_create_performance_extras_tables', <BATCH>);
-- =============================================================================

-- =============================================================================

SET FOREIGN_KEY_CHECKS = 1;
