-- Dipecah dari database/deploy-all-produksi.sql — jalankan HANYA tahap yang belum ada di prod.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- TAHAP 23 — job-requisition-budget-control-manual.sql
-- =============================================================================
-- =============================================================================
-- Modul: Job Requisition — tipe permintaan + tautan Manpower Plan + Budget Control
--
-- Setara dengan migration:
--   2026_08_31_100000_add_request_type_and_mpp_to_job_requisitions
--
-- WAJIB backup dulu. Jalankan SETELAH recruitment-manual.sql (butuh job_requisitions)
-- dan manpower-planning-manual.sql (butuh manpower_plans).
--
-- Budget Control ada di kode (JobRequisition::budgetViolation()) — file ini hanya
-- menambah 3 kolom. Requisition tipe 'additional'/'new_position' hanya bisa diajukan
-- bila kuota Manpower Plan yang disetujui masih cukup
-- (planned_headcount − aktual − sedang direkrut ≥ headcount_requested).
-- =============================================================================

ALTER TABLE `job_requisitions`
  ADD COLUMN `request_type` varchar(20) NOT NULL DEFAULT 'replacement' AFTER `title`,
  ADD COLUMN `manpower_plan_id` bigint(20) unsigned DEFAULT NULL AFTER `request_type`,
  ADD COLUMN `replaces_employee_id` bigint(20) unsigned DEFAULT NULL AFTER `manpower_plan_id`,
  ADD CONSTRAINT `job_requisitions_manpower_plan_id_foreign`
      FOREIGN KEY (`manpower_plan_id`) REFERENCES `manpower_plans` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `job_requisitions_replaces_employee_id_foreign`
      FOREIGN KEY (`replaces_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

-- request_type: 'replacement' (pengganti, tidak menambah headcount)
--             | 'additional' (tambahan headcount — butuh kuota MPP)
--             | 'new_position' (posisi baru — butuh kuota MPP)
-- Baris lama otomatis 'replacement' (default) — aman, tidak kena Budget Control.

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_08_31_100000_add_request_type_and_mpp_to_job_requisitions', <BATCH>);
-- =============================================================================


-- =============================================================================

SET FOREIGN_KEY_CHECKS = 1;
