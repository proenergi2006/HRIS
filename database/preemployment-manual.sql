-- =============================================================================
-- Modul: Pre-Employment (PRD Bab 3 modul #4) — data terstruktur + checklist
--        wajib yang dilengkapi SEBELUM kandidat "accepted" dikonversi jadi
--        Employee. Saat convert, data dipindah ke employees + employee_bank_accounts
--        + employee_nssf (logika di CandidateController).
--
-- Setara dengan migration:
--   2026_08_31_120000_create_preemployment_tables
--
-- WAJIB backup dulu. Jalankan SETELAH:
--   - candidate-database-ats-manual.sql (butuh `candidates`)
--   - master-data-manual.sql (butuh marital_statuses / religions / blood_types / banks)
-- =============================================================================

CREATE TABLE `candidate_preemployment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `gender` varchar(10) DEFAULT NULL,            -- L / P (sejajar employees.gender)
  `birth_place` varchar(100) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `marital_status_id` bigint(20) unsigned DEFAULT NULL,
  `religion_id` bigint(20) unsigned DEFAULT NULL,
  `blood_type_id` bigint(20) unsigned DEFAULT NULL,
  `ktp_number` text DEFAULT NULL,               -- ciphertext (cast encrypted)
  `npwp_number` text DEFAULT NULL,              -- ciphertext
  `ktp_address` varchar(255) DEFAULT NULL,
  `ktp_city` varchar(100) DEFAULT NULL,
  `domicile_address` varchar(255) DEFAULT NULL,
  `domicile_city` varchar(100) DEFAULT NULL,
  `bank_id` bigint(20) unsigned DEFAULT NULL,
  `bank_account_number` text DEFAULT NULL,      -- ciphertext
  `bank_account_holder` varchar(150) DEFAULT NULL,
  `bpjs_health_number` varchar(50) DEFAULT NULL,
  `bpjs_health_date` date DEFAULT NULL,
  `bpjs_employment_number` varchar(50) DEFAULT NULL,
  `bpjs_employment_date` date DEFAULT NULL,
  `emergency_contact_name` varchar(120) DEFAULT NULL,
  `emergency_contact_relation` varchar(60) DEFAULT NULL,
  `emergency_contact_phone` varchar(30) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `candidate_preemployment_candidate_id_unique` (`candidate_id`),
  KEY `candidate_preemployment_marital_status_id_foreign` (`marital_status_id`),
  KEY `candidate_preemployment_religion_id_foreign` (`religion_id`),
  KEY `candidate_preemployment_blood_type_id_foreign` (`blood_type_id`),
  KEY `candidate_preemployment_bank_id_foreign` (`bank_id`),
  CONSTRAINT `candidate_preemployment_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `candidate_preemployment_marital_status_id_foreign` FOREIGN KEY (`marital_status_id`) REFERENCES `marital_statuses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `candidate_preemployment_religion_id_foreign` FOREIGN KEY (`religion_id`) REFERENCES `religions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `candidate_preemployment_blood_type_id_foreign` FOREIGN KEY (`blood_type_id`) REFERENCES `blood_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `candidate_preemployment_bank_id_foreign` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `preemployment_checklist_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,   -- NULL = global
  `label` varchar(200) NOT NULL,
  `category` varchar(20) NOT NULL DEFAULT 'dokumen', -- dokumen/data/verifikasi
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `preemployment_checklist_items_company_id_foreign` (`company_id`),
  CONSTRAINT `preemployment_checklist_items_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidate_preemployment_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `preemployment_checklist_item_id` bigint(20) unsigned NOT NULL,
  `is_done` tinyint(1) NOT NULL DEFAULT 0,
  `done_at` datetime DEFAULT NULL,
  `done_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpt_candidate_item_unique` (`candidate_id`,`preemployment_checklist_item_id`),
  KEY `candidate_preemployment_tasks_preemployment_checklist_item_id_foreign` (`preemployment_checklist_item_id`),
  KEY `candidate_preemployment_tasks_done_by_user_id_foreign` (`done_by_user_id`),
  CONSTRAINT `candidate_preemployment_tasks_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `candidate_preemployment_tasks_item_foreign` FOREIGN KEY (`preemployment_checklist_item_id`) REFERENCES `preemployment_checklist_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `candidate_preemployment_tasks_done_by_user_id_foreign` FOREIGN KEY (`done_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Item checklist default (global) ─────────────────────────────────────────
INSERT INTO `preemployment_checklist_items` (`company_id`,`label`,`category`,`is_required`,`sort_order`,`is_active`,`created_at`,`updated_at`) VALUES
(NULL,'Offer diterima & ditandatangani','verifikasi',1,10,1,NOW(),NOW()),
(NULL,'Data pribadi lengkap (TTL, alamat, dll)','data',1,20,1,NOW(),NOW()),
(NULL,'KTP','dokumen',1,30,1,NOW(),NOW()),
(NULL,'NPWP','dokumen',1,40,1,NOW(),NOW()),
(NULL,'Kartu Keluarga','dokumen',1,50,1,NOW(),NOW()),
(NULL,'Rekening bank (bank, no. rek, a.n.)','data',1,60,1,NOW(),NOW()),
(NULL,'Data BPJS Kesehatan','data',0,70,1,NOW(),NOW()),
(NULL,'Data BPJS Ketenagakerjaan','data',0,80,1,NOW(),NOW()),
(NULL,'Ijazah & transkrip nilai','dokumen',1,90,1,NOW(),NOW()),
(NULL,'SKCK','dokumen',0,100,1,NOW(),NOW()),
(NULL,'Surat pengalaman kerja / paklaring','dokumen',0,110,1,NOW(),NOW()),
(NULL,'Hasil Medical Check-Up (MCU)','dokumen',1,120,1,NOW(),NOW()),
(NULL,'Pas foto','dokumen',1,130,1,NOW(),NOW());

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_08_31_120000_create_preemployment_tables', <BATCH>);
-- =============================================================================
