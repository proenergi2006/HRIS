-- ============================================================================
-- Modul: Performance Management — rebuild Appraisal dari model "aspek + bobot"
-- (BS/B/C/K, atau 4-penilai self/atasan1/atasan2/HO) ke model KPI/objective-
-- based, dan migrasi approval dari state machine 2-step (appraisal_flow_configs)
-- ke Approval Engine generik (approval_workflows/approval_requests) — sama
-- seperti Cuti/Perdin/Reimbursement/Reward/Punishment/Promosi/Termination/
-- Manpower Plan/Job Requisition/Overtime/dll.
--
-- Setara dengan migration 2026_08_28_101301_rebuild_appraisal_to_kpi_model.
--
-- PERINGATAN — DESTRUKTIF: DROP 5 tabel appraisal lama beserta isinya
-- (appraisal_approvals, appraisal_items, appraisal_aspect_weights,
-- appraisal_aspects, appraisal_flow_configs). WAJIB BACKUP DULU kalau ada
-- data transaksi appraisal nyata di tabel-tabel ini — keputusan produk
-- (28 Agu 2026) adalah hapus & ganti total, bukan migrasi data lama.
--
-- Jalankan setelah approval-engine-manual.sql (butuh approval_workflows/
-- approval_requests/approval_request_steps sudah ada) & master-organization
-- (butuh departments/positions sudah ada, dipakai ITDemoSeeder versi baru).
-- ============================================================================

DROP TABLE IF EXISTS `appraisal_approvals`;
DROP TABLE IF EXISTS `appraisal_items`;
DROP TABLE IF EXISTS `appraisal_aspect_weights`;
DROP TABLE IF EXISTS `appraisal_aspects`;
DROP TABLE IF EXISTS `appraisal_flow_configs`;

-- ── appraisal_templates: buang scoring_type (cuma 1 model skor sekarang) ────
ALTER TABLE `appraisal_templates`
  DROP COLUMN `scoring_type`;

-- ── appraisal_template_objectives: KPI starter per template (opsional,      ─
--    otomatis disalin ke appraisal_objectives saat appraisal baru dibuat) ──
CREATE TABLE `appraisal_template_objectives` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `appraisal_template_id` bigint(20) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `weight_pct` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `order` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `appraisal_template_objectives_appraisal_template_id_foreign` (`appraisal_template_id`),
  CONSTRAINT `appraisal_template_objectives_appraisal_template_id_foreign` FOREIGN KEY (`appraisal_template_id`) REFERENCES `appraisal_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── appraisals: buang kolom model lama, sesuaikan status/total_score ke ─────
--    konvensi Approval Engine (draft/pending/approved/rejected/cancelled) ──
ALTER TABLE `appraisals`
  DROP COLUMN `score_self`,
  DROP COLUMN `score_atasan1`,
  DROP COLUMN `score_atasan2`,
  DROP COLUMN `score_ho`,
  DROP COLUMN `avg_late_per_month`,
  DROP COLUMN `avg_leave_per_month`,
  DROP COLUMN `warning_letter`,
  DROP COLUMN `sp_level`,
  DROP COLUMN `decision`,
  DROP COLUMN `individual_development_plan`;

ALTER TABLE `appraisals`
  MODIFY `status` varchar(20) NOT NULL DEFAULT 'draft',
  MODIFY `total_score` decimal(6,2) NOT NULL DEFAULT 0.00,
  -- template sekarang opsional ("Tanpa Template — isi KPI dari nol"), FK
  -- aslinya NOT NULL sejak tabel dibuat (create_appraisals_table).
  MODIFY `appraisal_template_id` bigint(20) unsigned DEFAULT NULL,
  ADD COLUMN `development_notes` text DEFAULT NULL AFTER `strength_points`;

ALTER TABLE `appraisals`
  CHANGE COLUMN `strength_points` `strengths` text DEFAULT NULL;

ALTER TABLE `appraisals`
  DROP COLUMN `development_need`;

-- ── appraisal_objectives: KPI/objective aktual per appraisal (pengganti ─────
--    appraisal_aspects + appraisal_items) ───────────────────────────────────
CREATE TABLE `appraisal_objectives` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `appraisal_id` bigint(20) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `weight_pct` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `target` text DEFAULT NULL,
  `actual` text DEFAULT NULL,
  `achievement_pct` decimal(6,2) DEFAULT NULL,
  `score` decimal(6,2) DEFAULT NULL,
  `order` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `appraisal_objectives_appraisal_id_foreign` (`appraisal_id`),
  CONSTRAINT `appraisal_objectives_appraisal_id_foreign` FOREIGN KEY (`appraisal_id`) REFERENCES `appraisals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Approval workflow default untuk transaction_type='appraisal' (specific_role
-- hr_manager -> specific_role ceo, per company) sudah didaftarkan lewat
-- ApprovalWorkflowSeeder — kalau approval-engine-manual.sql sudah pernah
-- dijalankan sebelum patch ini, jalankan ulang seeder tsb (atau INSERT manual
-- ke approval_workflows/approval_workflow_steps mengikuti pola company lain).
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_101301_rebuild_appraisal_to_kpi_model',<BATCH>);
-- ============================================================================
