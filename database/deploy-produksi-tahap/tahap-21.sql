-- Dipecah dari database/deploy-all-produksi.sql — jalankan HANYA tahap yang belum ada di prod.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- TAHAP 21 — offboarding-letter-request-manual.sql
-- =============================================================================
-- =============================================================================
-- Modul: Clearance Resign (Offboarding) + Permintaan Surat Self-Service
-- Fase 2 HRD — keduanya dikelola HR langsung (tanpa Approval Engine).
-- Setara dengan migration:
--   2026_08_29_180200_create_offboarding_tables.php
--   2026_08_29_180300_create_letter_requests_table.php
-- WAJIB backup database dulu sebelum menjalankan file ini.
-- Jalankan SETELAH gap-closure-manual.sql (perlu employee_facilities, termination_requests)
-- dan letter templates sudah ada (employee-administration-manual.sql).
-- =============================================================================

-- ── C2. Clearance Resign (Offboarding) ──────────────────────────────────────
-- Mirror onboarding_checklist_items/employee_onboarding_tasks. Task
-- dimaterialisasi otomatis saat TerminationRequest dibuat (lihat
-- HrRequestController::store()) atau manual via "Mulai Clearance".

CREATE TABLE `offboarding_checklist_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `label` varchar(200) NOT NULL,
  `category` varchar(20) NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `offboarding_checklist_items_company_id_foreign` (`company_id`),
  CONSTRAINT `offboarding_checklist_items_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_offboarding_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `offboarding_checklist_item_id` bigint(20) unsigned NOT NULL,
  `is_done` tinyint(1) NOT NULL DEFAULT 0,
  `done_at` datetime DEFAULT NULL,
  `done_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_offboarding_task_unique` (`employee_id`,`offboarding_checklist_item_id`),
  KEY `employee_offboarding_tasks_offboarding_checklist_item_id_foreign` (`offboarding_checklist_item_id`),
  KEY `employee_offboarding_tasks_done_by_user_id_foreign` (`done_by_user_id`),
  CONSTRAINT `employee_offboarding_tasks_done_by_user_id_foreign` FOREIGN KEY (`done_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_offboarding_tasks_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_offboarding_tasks_offboarding_checklist_item_id_foreign` FOREIGN KEY (`offboarding_checklist_item_id`) REFERENCES `offboarding_checklist_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Item checklist default (OffboardingChecklistItemSeeder) — berlaku semua PT.
INSERT INTO offboarding_checklist_items (company_id, label, category, is_required, sort_order, is_active, created_at, updated_at) VALUES
  (NULL, 'Kembalikan laptop/aset kantor',     'aset',     1, 1, 1, NOW(), NOW()),
  (NULL, 'Kembalikan ID card & akses gedung', 'aset',     1, 2, 1, NOW(), NOW()),
  (NULL, 'Nonaktifkan email & akun sistem',   'akun',     1, 1, 1, NOW(), NOW()),
  (NULL, 'Serah terima pekerjaan',             'exit',     1, 1, 1, NOW(), NOW()),
  (NULL, 'Exit interview',                     'exit',     1, 2, 1, NOW(), NOW()),
  (NULL, 'Pelunasan kasbon/pinjaman',         'keuangan', 1, 1, 1, NOW(), NOW()),
  (NULL, 'Surat pengalaman kerja',             'dokumen',  1, 1, 1, NOW(), NOW());

-- ── C3. Permintaan Surat Self-Service ────────────────────────────────────────

ALTER TABLE letter_templates
  ADD COLUMN self_service tinyint(1) NOT NULL DEFAULT 0 AFTER is_active;

CREATE TABLE `letter_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `requested_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `letter_template_id` bigint(20) unsigned DEFAULT NULL,
  `purpose` varchar(60) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `employee_letter_id` bigint(20) unsigned DEFAULT NULL,
  `handled_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `handled_at` datetime DEFAULT NULL,
  `rejection_note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `letter_requests_employee_id_foreign` (`employee_id`),
  KEY `letter_requests_requested_by_user_id_foreign` (`requested_by_user_id`),
  KEY `letter_requests_letter_template_id_foreign` (`letter_template_id`),
  KEY `letter_requests_employee_letter_id_foreign` (`employee_letter_id`),
  KEY `letter_requests_handled_by_user_id_foreign` (`handled_by_user_id`),
  CONSTRAINT `letter_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `letter_requests_employee_letter_id_foreign` FOREIGN KEY (`employee_letter_id`) REFERENCES `employee_letters` (`id`) ON DELETE SET NULL,
  CONSTRAINT `letter_requests_handled_by_user_id_foreign` FOREIGN KEY (`handled_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `letter_requests_letter_template_id_foreign` FOREIGN KEY (`letter_template_id`) REFERENCES `letter_templates` (`id`) ON DELETE SET NULL,
  CONSTRAINT `letter_requests_requested_by_user_id_foreign` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Setelah ini, HR perlu mencentang "self_service" pada minimal 1 template surat
-- (mis. "Surat Keterangan Kerja") lewat halaman Template Surat supaya muncul
-- di form permintaan mandiri karyawan.

-- INSERT INTO migrations (migration, batch) VALUES
--   ('2026_08_29_180200_create_offboarding_tables', <batch>),
--   ('2026_08_29_180300_create_letter_requests_table', <batch>);


-- =============================================================================

SET FOREIGN_KEY_CHECKS = 1;
