-- Dipecah dari database/deploy-all-produksi.sql — jalankan HANYA tahap yang belum ada di prod.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- TAHAP 12 — recruitment-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: Recruitment + Pre-Employment + Onboarding (PRD HRIS v1.2 Bab 3 modul #3/4/5)
--
-- Setara dengan migration 2026_08_28_1008xx. Career-site publik TIDAK termasuk
-- (form internal saja, sesuai PRD 3.3 Out of Scope Fase 1).
--
-- WAJIB backup dulu. Jalankan setelah role-permission-manual.sql &
-- approval-engine-manual.sql (butuh company/departments/sections/positions/employees/
-- employee_types/users sudah ada).
-- ============================================================================

CREATE TABLE `job_requisitions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `section_id` bigint(20) unsigned DEFAULT NULL,
  `position_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `reason` text DEFAULT NULL,
  `headcount_requested` int(10) unsigned NOT NULL DEFAULT 1,
  `employment_type_id` bigint(20) unsigned DEFAULT NULL,
  `target_join_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `requested_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `notes_rejection` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_requisitions_company_id_foreign` (`company_id`),
  KEY `job_requisitions_department_id_foreign` (`department_id`),
  KEY `job_requisitions_section_id_foreign` (`section_id`),
  KEY `job_requisitions_position_id_foreign` (`position_id`),
  KEY `job_requisitions_employment_type_id_foreign` (`employment_type_id`),
  KEY `job_requisitions_requested_by_user_id_foreign` (`requested_by_user_id`),
  CONSTRAINT `job_requisitions_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `job_requisitions_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `job_requisitions_employment_type_id_foreign` FOREIGN KEY (`employment_type_id`) REFERENCES `employee_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `job_requisitions_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `job_requisitions_requested_by_user_id_foreign` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `job_requisitions_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `job_requisition_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'applied',
  `mcu_result` varchar(20) DEFAULT NULL,
  `converted_employee_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `candidates_job_requisition_id_foreign` (`job_requisition_id`),
  KEY `candidates_converted_employee_id_foreign` (`converted_employee_id`),
  CONSTRAINT `candidates_converted_employee_id_foreign` FOREIGN KEY (`converted_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `candidates_job_requisition_id_foreign` FOREIGN KEY (`job_requisition_id`) REFERENCES `job_requisitions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidate_interviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `stage` varchar(100) NOT NULL,
  `scheduled_at` datetime DEFAULT NULL,
  `interviewer_employee_id` bigint(20) unsigned DEFAULT NULL,
  `result` varchar(20) NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `candidate_interviews_candidate_id_foreign` (`candidate_id`),
  KEY `candidate_interviews_interviewer_employee_id_foreign` (`interviewer_employee_id`),
  CONSTRAINT `candidate_interviews_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `candidate_interviews_interviewer_employee_id_foreign` FOREIGN KEY (`interviewer_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidate_offers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `position_id` bigint(20) unsigned DEFAULT NULL,
  `offered_salary` bigint(20) DEFAULT NULL,
  `start_date_offered` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `candidate_offers_candidate_id_foreign` (`candidate_id`),
  KEY `candidate_offers_position_id_foreign` (`position_id`),
  CONSTRAINT `candidate_offers_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `candidate_offers_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidate_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `doc_type` varchar(60) NOT NULL,
  `title` varchar(200) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `candidate_documents_candidate_id_foreign` (`candidate_id`),
  CONSTRAINT `candidate_documents_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `onboarding_checklist_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `label` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `material_path` varchar(500) DEFAULT NULL,
  `material_original_name` varchar(255) DEFAULT NULL,
  `material_url` varchar(500) DEFAULT NULL,
  `category` varchar(20) NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `requires_acknowledgement` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `onboarding_checklist_items_company_id_foreign` (`company_id`),
  CONSTRAINT `onboarding_checklist_items_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- (kalau tabel sudah ada dari rilis lama, jalankan ALTER berikut:)
-- ALTER TABLE `onboarding_checklist_items`
--   ADD COLUMN `description` text DEFAULT NULL AFTER `label`,
--   ADD COLUMN `material_path` varchar(500) DEFAULT NULL AFTER `description`,
--   ADD COLUMN `material_original_name` varchar(255) DEFAULT NULL AFTER `material_path`,
--   ADD COLUMN `material_url` varchar(500) DEFAULT NULL AFTER `material_original_name`,
--   ADD COLUMN `requires_acknowledgement` tinyint(1) NOT NULL DEFAULT 0 AFTER `is_required`;

CREATE TABLE `employee_onboarding_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `onboarding_checklist_item_id` bigint(20) unsigned NOT NULL,
  `is_done` tinyint(1) NOT NULL DEFAULT 0,
  `done_at` datetime DEFAULT NULL,
  `done_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `acknowledged_at` datetime DEFAULT NULL,
  `acknowledgement_note` varchar(500) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_onboarding_task_unique` (`employee_id`,`onboarding_checklist_item_id`),
  KEY `employee_onboarding_tasks_onboarding_checklist_item_id_foreign` (`onboarding_checklist_item_id`),
  KEY `employee_onboarding_tasks_done_by_user_id_foreign` (`done_by_user_id`),
  CONSTRAINT `employee_onboarding_tasks_done_by_user_id_foreign` FOREIGN KEY (`done_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_onboarding_tasks_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_onboarding_tasks_onboarding_checklist_item_id_foreign` FOREIGN KEY (`onboarding_checklist_item_id`) REFERENCES `onboarding_checklist_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Item checklist onboarding default (global) ───────────────────────────────
-- Setara OnboardingChecklistItemSeeder. Bisa ditambah/nonaktifkan/dilampiri materi
-- per PT lewat menu Rekrutmen > Onboarding > Template Checklist.
-- Kategori dokumen/akun/aset = diceklis HR. Kategori induction (requires_acknowledgement=1)
-- = dibaca & dikonfirmasi KARYAWAN lewat menu "Onboarding Saya".
-- Saat konversi kandidat, item NIP + Perjanjian kerja (+ Email & akun bila buat akun)
-- otomatis ditandai selesai.
INSERT INTO `onboarding_checklist_items`
(`company_id`,`label`,`description`,`category`,`is_required`,`requires_acknowledgement`,`sort_order`,`is_active`,`created_at`,`updated_at`) VALUES
(NULL,'Background check / verifikasi latar belakang',NULL,'dokumen',1,0,10,1,NOW(),NOW()),
(NULL,'Perjanjian kerja ditandatangani',NULL,'dokumen',1,0,20,1,NOW(),NOW()),
(NULL,'Nomor Induk Karyawan (NIP) diterbitkan',NULL,'akun',1,0,30,1,NOW(),NOW()),
(NULL,'Email & akun sistem dibuat',NULL,'akun',1,0,40,1,NOW(),NOW()),
(NULL,'Kartu akses / kartu absensi',NULL,'aset',1,0,50,1,NOW(),NOW()),
(NULL,'Laptop / perangkat kerja',NULL,'aset',1,0,60,1,NOW(),NOW()),
(NULL,'Meja kerja / workstation',NULL,'aset',1,0,70,1,NOW(),NOW()),
(NULL,'Seragam / APD',NULL,'aset',0,0,80,1,NOW(),NOW()),
(NULL,'Welcome','Sambutan manajemen & gambaran umum hari pertama.','induction',1,1,100,1,NOW(),NOW()),
(NULL,'Company Introduction / Orientation','Sejarah, visi-misi, nilai perusahaan, lini bisnis, dan lokasi kerja.','induction',1,1,110,1,NOW(),NOW()),
(NULL,'Organization','Struktur organisasi, jenjang jabatan, dan alur pelaporan.','induction',1,1,120,1,NOW(),NOW()),
(NULL,'HR Procedure','Kehadiran, cuti, lembur, penilaian kinerja, tata tertib, dan sanksi.','induction',1,1,130,1,NOW(),NOW()),
(NULL,'Fakta Integritas','Pernyataan integritas, benturan kepentingan, anti-suap & gratifikasi. Wajib dibaca dan disetujui.','induction',1,1,140,1,NOW(),NOW()),
(NULL,'HR Operation & Incentive','Penggajian, komponen upah, THR/bonus, insentif, BPJS, dan reimbursement.','induction',1,1,150,1,NOW(),NOW()),
(NULL,'GA Procedure','Fasilitas kantor, aset, kendaraan, perjalanan dinas, kebersihan & keamanan.','induction',1,1,160,1,NOW(),NOW()),
(NULL,'Vopak Procedure','Prosedur operasi & HSSE terminal Vopak yang berlaku di area kerja.','induction',1,1,170,1,NOW(),NOW()),
(NULL,'Logistic & Operational Procedure','Alur logistik, penerimaan/pengiriman, dan SOP operasional lapangan.','induction',0,1,180,1,NOW(),NOW()),
(NULL,'Sales Administration & Finance Procedure','Administrasi penjualan, invoicing, penagihan, dan pelaporan keuangan.','induction',0,1,190,1,NOW(),NOW()),
(NULL,'Legal & Collection Procedure','Kontrak, kepatuhan hukum, dan prosedur penagihan piutang.','induction',0,1,200,1,NOW(),NOW()),
(NULL,'Business Overview (Commercial)','Peta pasar, pelanggan utama, dan strategi komersial.','induction',0,1,210,1,NOW(),NOW()),
(NULL,'Selling Skill & Product Knowledge','Pengetahuan produk dan keterampilan penjualan dasar.','induction',0,1,220,1,NOW(),NOW()),
(NULL,'Procurement Procedure','Permintaan pembelian, vendor, dan proses pengadaan.','induction',0,1,230,1,NOW(),NOW());

-- ============================================================================
-- Setelah tabel dibuat, WAJIB jalankan ulang seeder berikut (idempoten):
--   php artisan db:seed --class=PermissionCatalogSeeder   (permission recruitment.*)
--   php artisan db:seed --class=ApprovalWorkflowSeeder    (workflow job_requisition per company)
--   php artisan db:seed --class=OnboardingChecklistItemSeeder  (kalau tidak pakai INSERT di atas)
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_100801_create_job_requisitions_table',<BATCH>),
-- ('2026_08_28_100802_create_candidates_tables',<BATCH>),
-- ('2026_08_28_100803_create_onboarding_tables',<BATCH>);
-- ============================================================================


-- =============================================================================

SET FOREIGN_KEY_CHECKS = 1;
