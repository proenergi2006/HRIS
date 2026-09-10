-- Dipecah dari database/deploy-all-produksi.sql — jalankan HANYA tahap yang belum ada di prod.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- TAHAP 10 — overtime-request-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: Pengajuan Lembur self-service (PRD HRIS v1.2 Bab 3 modul #8 — Attendance
-- & Leave; Bab 3.2 secara eksplisit menyebut Overtime butuh Dynamic Approval
-- Workflow).
--
-- Setara dengan migration 2026_08_28_101101. Transaction type 'overtime_request'
-- SUDAH ADA di approval_workflows sejak batch approval-engine-manual.sql (default:
-- direct_manager) — file ini cuma menambah tabel modelnya, tidak perlu ubah
-- approval_workflows/approval_workflow_steps.
--
-- WAJIB backup dulu. Jalankan setelah approval-engine-manual.sql.
-- ============================================================================

CREATE TABLE `overtime_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `requested_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `date` date NOT NULL,
  `planned_hours` decimal(4,1) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `overtime_requests_employee_id_foreign` (`employee_id`),
  KEY `overtime_requests_company_id_foreign` (`company_id`),
  KEY `overtime_requests_requested_by_user_id_foreign` (`requested_by_user_id`),
  CONSTRAINT `overtime_requests_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `overtime_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `overtime_requests_requested_by_user_id_foreign` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Begitu disetujui, otomatis menulis ke attendance_records.overtime_minutes
-- (sumber data yang sama dipakai halaman HR > Lembur & Tunjangan Lembur payroll)
-- — tidak perlu migrasi data tambahan.
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_101101_create_overtime_requests_table',<BATCH>);
-- ============================================================================


-- =============================================================================

SET FOREIGN_KEY_CHECKS = 1;
