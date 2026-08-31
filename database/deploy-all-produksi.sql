-- =============================================================================
-- DEPLOY PRODUKSI — HRIS v1.2 (Fase 2 HRD + Rebuild Organisasi)
-- Gabungan 25 file database/*-manual.sql dalam URUTAN JALAN.
-- Rincian/dependency per modul: database/DEPLOY-PRODUKSI.md
-- Daftar tabel+kolom: database/SKEMA-PERUBAHAN.md
--
-- WAJIB: mysqldump backup penuh + `php artisan down` DULU.
-- DDL (CREATE/ALTER TABLE) di MySQL TIDAK bisa di-rollback — andalkan backup.
-- Baris `-- >>>` = langkah artisan / deploy kode yang HARUS dikerjakan di titik itu.
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;



-- =============================================================================
-- TAHAP 1 — master-data-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: Master Data referensi (mengikuti PRD HRIS v1.2)
--
-- Setara dengan migration:
--   2026_08_28_100001_create_religions_table
--   2026_08_28_100002_create_education_levels_table
--   2026_08_28_100003_create_education_majors_table
--   2026_08_28_100004_create_marital_statuses_table
--   2026_08_28_100005_create_blood_types_table
--   2026_08_28_100006_create_employee_types_table
--   2026_08_28_100007_create_banks_table
--   2026_08_28_100008_create_company_banks_table
--   2026_08_28_100009_create_provinces_table
--   2026_08_28_100010_create_cities_table
--   2026_08_28_100011_convert_employee_reference_fields_to_masters
--
-- WAJIB backup database dulu. Jalankan berurutan dari atas.
-- Data Provinsi + Kota/Kabupaten TIDAK ada di file ini — jalankan lewat
-- seeder Laravel:  php artisan db:seed --class=RegionSeeder
-- (atau minta developer meng-generate INSERT-nya dari database/data/regions.php).
-- ============================================================================

-- 1) Tabel master sederhana --------------------------------------------------

CREATE TABLE `religions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `religions_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `religions` (`code`,`name`,`sort_order`,`is_active`,`created_at`,`updated_at`) VALUES
('ISLAM','Islam',1,1,NOW(),NOW()),('KRISTEN','Kristen',2,1,NOW(),NOW()),
('KATOLIK','Katolik',3,1,NOW(),NOW()),('HINDU','Hindu',4,1,NOW(),NOW()),
('BUDDHA','Buddha',5,1,NOW(),NOW()),('KONGHUCU','Konghucu',6,1,NOW(),NOW()),
('KEPERCAYAAN','Kepercayaan',7,1,NOW(),NOW());

CREATE TABLE `education_levels` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sort_order` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `education_levels_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `education_levels` (`code`,`name`,`sort_order`,`is_active`,`created_at`,`updated_at`) VALUES
('TIDAK_SEKOLAH','Tidak Sekolah',0,1,NOW(),NOW()),('SD','SD / Sederajat',1,1,NOW(),NOW()),
('SMP','SMP / Sederajat',2,1,NOW(),NOW()),('SMA','SMA / SMK / Sederajat',3,1,NOW(),NOW()),
('D1','Diploma 1 (D1)',4,1,NOW(),NOW()),('D2','Diploma 2 (D2)',5,1,NOW(),NOW()),
('D3','Diploma 3 (D3)',6,1,NOW(),NOW()),('S1','Sarjana / D4 (S1)',7,1,NOW(),NOW()),
('S2','Magister (S2)',8,1,NOW(),NOW()),('S3','Doktor (S3)',9,1,NOW(),NOW());

CREATE TABLE `education_majors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `education_majors_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `education_majors` (`code`,`name`,`sort_order`,`is_active`,`created_at`,`updated_at`) VALUES
('M001','Akuntansi',1,1,NOW(),NOW()),('M002','Manajemen',2,1,NOW(),NOW()),
('M003','Administrasi',3,1,NOW(),NOW()),('M004','Ekonomi',4,1,NOW(),NOW()),
('M005','Hukum',5,1,NOW(),NOW()),('M006','Teknik Informatika',6,1,NOW(),NOW()),
('M007','Sistem Informasi',7,1,NOW(),NOW()),('M008','Teknik Industri',8,1,NOW(),NOW()),
('M009','Teknik Mesin',9,1,NOW(),NOW()),('M010','Teknik Elektro',10,1,NOW(),NOW()),
('M011','Teknik Sipil',11,1,NOW(),NOW()),('M012','Teknik Kimia',12,1,NOW(),NOW()),
('M013','Ilmu Komunikasi',13,1,NOW(),NOW()),('M014','Psikologi',14,1,NOW(),NOW()),
('M015','Sumber Daya Manusia',15,1,NOW(),NOW()),('M016','Perpajakan',16,1,NOW(),NOW()),
('M017','Sekretari',17,1,NOW(),NOW()),('M018','Lainnya',18,1,NOW(),NOW());

CREATE TABLE `marital_statuses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `ptkp_code` varchar(10) DEFAULT NULL,
  `legacy_key` varchar(30) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `marital_statuses_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `marital_statuses` (`code`,`name`,`ptkp_code`,`legacy_key`,`sort_order`,`is_active`,`created_at`,`updated_at`) VALUES
('BELUM_KAWIN','Belum Kawin','TK','belum_kawin',1,1,NOW(),NOW()),
('KAWIN','Kawin','K','kawin',2,1,NOW(),NOW()),
('CERAI_HIDUP','Cerai Hidup','TK','cerai_hidup',3,1,NOW(),NOW()),
('CERAI_MATI','Cerai Mati','TK','cerai_mati',4,1,NOW(),NOW());

CREATE TABLE `blood_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `name` varchar(20) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blood_types_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `blood_types` (`code`,`name`,`sort_order`,`is_active`,`created_at`,`updated_at`) VALUES
('A','A',1,1,NOW(),NOW()),('B','B',2,1,NOW(),NOW()),('AB','AB',3,1,NOW(),NOW()),('O','O',4,1,NOW(),NOW());

CREATE TABLE `employee_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `legacy_key` varchar(30) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_types_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `employee_types` (`code`,`name`,`legacy_key`,`sort_order`,`is_active`,`created_at`,`updated_at`) VALUES
('TETAP','Karyawan Tetap','permanent',1,1,NOW(),NOW()),
('KONTRAK','Kontrak (PKWT)','contract',2,1,NOW(),NOW()),
('PROBATION','Probation','probation',3,1,NOW(),NOW()),
('HARIAN','Harian Lepas',NULL,4,1,NOW(),NOW()),
('OUTSOURCE','Outsource',NULL,5,1,NOW(),NOW()),
('MAGANG','Magang / Intern',NULL,6,1,NOW(),NOW());

CREATE TABLE `banks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `swift_code` varchar(20) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `banks_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `banks` (`code`,`name`,`swift_code`,`sort_order`,`is_active`,`created_at`,`updated_at`) VALUES
('014','Bank Central Asia (BCA)','CENAIDJA',1,1,NOW(),NOW()),
('008','Bank Mandiri','BMRIIDJA',2,1,NOW(),NOW()),
('002','Bank Rakyat Indonesia (BRI)','BRINIDJA',3,1,NOW(),NOW()),
('009','Bank Negara Indonesia (BNI)','BNINIDJA',4,1,NOW(),NOW()),
('200','Bank Tabungan Negara (BTN)','BTANIDJA',5,1,NOW(),NOW()),
('022','CIMB Niaga','BNIAIDJA',6,1,NOW(),NOW()),
('013','Bank Permata','BBBAIDJA',7,1,NOW(),NOW()),
('011','Bank Danamon','BDINIDJA',8,1,NOW(),NOW()),
('019','Bank Panin','PINBIDJA',9,1,NOW(),NOW()),
('016','Maybank Indonesia','IBBKIDJA',10,1,NOW(),NOW()),
('451','Bank Syariah Indonesia (BSI)','BSMDIDJA',11,1,NOW(),NOW()),
('153','Bank Sinarmas','SBJKIDJA',12,1,NOW(),NOW()),
('426','Bank Mega','MEGAIDJA',13,1,NOW(),NOW()),
('028','Bank OCBC NISP','NISPIDJA',14,1,NOW(),NOW()),
('213','Bank BTPN','BTPNIDJA',15,1,NOW(),NOW());

-- 2) Rekening perusahaan (per company) --------------------------------------

CREATE TABLE `company_banks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `bank_id` bigint(20) unsigned NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `account_name` varchar(150) NOT NULL,
  `branch_name` varchar(150) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_bank_unique` (`company_id`,`bank_id`,`account_number`),
  KEY `company_banks_bank_id_foreign` (`bank_id`),
  CONSTRAINT `company_banks_bank_id_foreign` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`),
  CONSTRAINT `company_banks_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Wilayah (isi lewat: php artisan db:seed --class=RegionSeeder) ----------

CREATE TABLE `provinces` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `provinces_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `province_id` bigint(20) unsigned NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('kota','kabupaten') NOT NULL DEFAULT 'kabupaten',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cities_code_unique` (`code`),
  KEY `cities_province_id_name_index` (`province_id`,`name`),
  CONSTRAINT `cities_province_id_foreign` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Konversi field referensi di tabel employees ---------------------------
--    Cek dulu: DESCRIBE employees;  (harus masih ada kolom `religion`,
--    `marital_status`, `blood_type`, `employee_type`, `domicile_city`, `ktp_city`)

ALTER TABLE `employees`
  ADD COLUMN `religion_id` bigint(20) unsigned DEFAULT NULL AFTER `religion`,
  ADD COLUMN `marital_status_id` bigint(20) unsigned DEFAULT NULL AFTER `marital_status`,
  ADD COLUMN `blood_type_id` bigint(20) unsigned DEFAULT NULL AFTER `blood_type`,
  ADD COLUMN `employee_type_id` bigint(20) unsigned DEFAULT NULL AFTER `employee_type`,
  ADD COLUMN `domicile_province_id` bigint(20) unsigned DEFAULT NULL AFTER `domicile_city`,
  ADD COLUMN `domicile_city_id` bigint(20) unsigned DEFAULT NULL AFTER `domicile_province_id`,
  ADD COLUMN `ktp_province_id` bigint(20) unsigned DEFAULT NULL AFTER `ktp_city`,
  ADD COLUMN `ktp_city_id` bigint(20) unsigned DEFAULT NULL AFTER `ktp_province_id`,
  ADD CONSTRAINT `employees_religion_id_foreign` FOREIGN KEY (`religion_id`) REFERENCES `religions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_marital_status_id_foreign` FOREIGN KEY (`marital_status_id`) REFERENCES `marital_statuses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_blood_type_id_foreign` FOREIGN KEY (`blood_type_id`) REFERENCES `blood_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_employee_type_id_foreign` FOREIGN KEY (`employee_type_id`) REFERENCES `employee_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_domicile_province_id_foreign` FOREIGN KEY (`domicile_province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_domicile_city_id_foreign` FOREIGN KEY (`domicile_city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_ktp_province_id_foreign` FOREIGN KEY (`ktp_province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_ktp_city_id_foreign` FOREIGN KEY (`ktp_city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL;

-- Backfill dari nilai lama (jalankan SETELAH RegionSeeder untuk city match)
UPDATE `employees` e JOIN `religions` r        ON LOWER(TRIM(e.`religion`))       = LOWER(r.`name`)        SET e.`religion_id` = r.`id` WHERE e.`religion_id` IS NULL;
UPDATE `employees` e JOIN `marital_statuses` m ON e.`marital_status`               = m.`legacy_key`          SET e.`marital_status_id` = m.`id` WHERE e.`marital_status_id` IS NULL;
UPDATE `employees` e JOIN `blood_types` b      ON LOWER(TRIM(e.`blood_type`))     = LOWER(b.`name`)        SET e.`blood_type_id` = b.`id` WHERE e.`blood_type_id` IS NULL;
UPDATE `employees` e JOIN `employee_types` t   ON e.`employment_status`            = t.`legacy_key`          SET e.`employee_type_id` = t.`id` WHERE e.`employee_type_id` IS NULL;
UPDATE `employees` e JOIN `cities` c ON LOWER(TRIM(e.`domicile_city`)) = LOWER(c.`name`) SET e.`domicile_city_id` = c.`id`, e.`domicile_province_id` = c.`province_id` WHERE e.`domicile_city_id` IS NULL;
UPDATE `employees` e JOIN `cities` c ON LOWER(TRIM(e.`ktp_city`))      = LOWER(c.`name`) SET e.`ktp_city_id` = c.`id`,      e.`ktp_province_id` = c.`province_id` WHERE e.`ktp_city_id` IS NULL;

-- Rename kolom teks `religion` supaya tidak bentrok dengan relasi Eloquent.
-- (kolom teks lain — marital_status, blood_type — dibiarkan, dipakai fallback.)
ALTER TABLE `employees` CHANGE COLUMN `religion` `religion_name` varchar(30) DEFAULT NULL;

-- CEK hasil sebelum lanjut:
--   SELECT id, name, religion_name, religion_id, marital_status, marital_status_id,
--          domicile_city, domicile_city_id FROM employees;

-- ============================================================================
-- Opsional: daftarkan ke tabel migrations Laravel. Cek: SELECT MAX(batch) FROM migrations;
-- lalu ganti <BATCH> (hasil + 1):
--
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_100001_create_religions_table',<BATCH>),
-- ('2026_08_28_100002_create_education_levels_table',<BATCH>),
-- ('2026_08_28_100003_create_education_majors_table',<BATCH>),
-- ('2026_08_28_100004_create_marital_statuses_table',<BATCH>),
-- ('2026_08_28_100005_create_blood_types_table',<BATCH>),
-- ('2026_08_28_100006_create_employee_types_table',<BATCH>),
-- ('2026_08_28_100007_create_banks_table',<BATCH>),
-- ('2026_08_28_100008_create_company_banks_table',<BATCH>),
-- ('2026_08_28_100009_create_provinces_table',<BATCH>),
-- ('2026_08_28_100010_create_cities_table',<BATCH>),
-- ('2026_08_28_100011_convert_employee_reference_fields_to_masters',<BATCH>);
-- ============================================================================


-- =============================================================================
-- TAHAP 2 — master-organization-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: Master Organization — Company > Division > Department > Section > Position
--        (mengikuti PRD HRIS v1.2)
--
-- Setara dengan migration:
--   2026_08_28_100101_create_divisions_table
--   2026_08_28_100102_create_sections_table
--   2026_08_28_100103_add_org_fields_to_departments_table
--   2026_08_28_100104_add_org_fields_to_positions_table
--   2026_08_28_100105_add_division_section_to_employees_table
--   2026_08_28_100106_create_org_change_logs_table
--
-- WAJIB backup dulu. Jalankan SETELAH database/master-data-manual.sql
-- (butuh tabel employees & companies sudah siap). Urut dari atas.
-- ============================================================================

-- 1) Divisi -----------------------------------------------------------------
CREATE TABLE `divisions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `head_employee_id` bigint(20) unsigned DEFAULT NULL,
  `cost_center` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `divisions_code_unique` (`code`),
  KEY `divisions_company_id_foreign` (`company_id`),
  KEY `divisions_head_employee_id_foreign` (`head_employee_id`),
  CONSTRAINT `divisions_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `divisions_head_employee_id_foreign` FOREIGN KEY (`head_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Section --------------------------------------------------------------
CREATE TABLE `sections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `department_id` bigint(20) unsigned NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `head_employee_id` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sections_code_unique` (`code`),
  KEY `sections_department_id_foreign` (`department_id`),
  KEY `sections_head_employee_id_foreign` (`head_employee_id`),
  CONSTRAINT `sections_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sections_head_employee_id_foreign` FOREIGN KEY (`head_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Kolom baru di departments ------------------------------------------
ALTER TABLE `departments`
  ADD COLUMN `division_id` bigint(20) unsigned DEFAULT NULL AFTER `company_id`,
  ADD COLUMN `head_employee_id` bigint(20) unsigned DEFAULT NULL AFTER `name`,
  ADD COLUMN `cost_center` varchar(50) DEFAULT NULL AFTER `head_employee_id`,
  ADD CONSTRAINT `departments_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `departments_head_employee_id_foreign` FOREIGN KEY (`head_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

-- 4) Kolom baru di positions -------------------------------------------
ALTER TABLE `positions`
  ADD COLUMN `section_id` bigint(20) unsigned DEFAULT NULL AFTER `department_id`,
  ADD COLUMN `level_id` bigint(20) unsigned DEFAULT NULL AFTER `section_id`,
  ADD COLUMN `reports_to_position_id` bigint(20) unsigned DEFAULT NULL AFTER `level_id`,
  ADD COLUMN `job_description` text DEFAULT NULL AFTER `name`,
  ADD CONSTRAINT `positions_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `positions_level_id_foreign` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `positions_reports_to_position_id_foreign` FOREIGN KEY (`reports_to_position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL;

-- 5) Kolom baru di employees ------------------------------------------
ALTER TABLE `employees`
  ADD COLUMN `division_id` bigint(20) unsigned DEFAULT NULL AFTER `department_id`,
  ADD COLUMN `section_id` bigint(20) unsigned DEFAULT NULL AFTER `division_id`,
  ADD CONSTRAINT `employees_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL;

-- Catatan: division_id & section_id karyawan sengaja dibiarkan NULL —
-- diisi HR bertahap lewat menu setelah struktur baru dibuat.

-- 6) Audit riwayat perubahan struktur ---------------------------------
CREATE TABLE `org_change_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `unit_type` enum('division','department','section','position') NOT NULL,
  `unit_id` bigint(20) unsigned NOT NULL,
  `unit_name` varchar(150) DEFAULT NULL,
  `action` enum('created','updated','moved','deactivated','deleted') NOT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changes`)),
  `effective_date` date NOT NULL,
  `changed_by` bigint(20) unsigned DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `org_change_logs_changed_by_foreign` (`changed_by`),
  KEY `org_change_logs_unit_type_unit_id_index` (`unit_type`,`unit_id`),
  KEY `org_change_logs_effective_date_index` (`effective_date`),
  CONSTRAINT `org_change_logs_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Setelah selesai:
--   1. Buka "Struktur Organisasi > Divisi/Departemen/Section/Jabatan" untuk
--      lengkapi divisi & section, lalu petakan karyawan.
--   2. Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
--
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_100101_create_divisions_table',<BATCH>),
-- ('2026_08_28_100102_create_sections_table',<BATCH>),
-- ('2026_08_28_100103_add_org_fields_to_departments_table',<BATCH>),
-- ('2026_08_28_100104_add_org_fields_to_positions_table',<BATCH>),
-- ('2026_08_28_100105_add_division_section_to_employees_table',<BATCH>),
-- ('2026_08_28_100106_create_org_change_logs_table',<BATCH>);
-- ============================================================================


-- =============================================================================
-- TAHAP 3 — branch-and-level-rank-manual.sql
-- =============================================================================
-- =============================================================================
-- Modul: Cabang (Branch) sebagai tingkat struktur organisasi + urutan Job Level
--        Company > Cabang > Divisi > Departemen > Section > Jabatan
--
-- Setara dengan migration:
--   2026_08_25_100000_add_rank_to_levels_table
--   2026_08_29_190000_create_branches_table
--   2026_08_29_191500_add_branch_id_to_positions_table
--
-- WAJIB backup database dulu sebelum menjalankan file ini.
-- Jalankan SETELAH master-organization-manual.sql (butuh divisions/sections/
-- positions.section_id sudah ada) dan master-data-manual.sql (butuh levels).
-- Urut dari atas.
-- =============================================================================

-- ── 1) Urutan tingkatan jabatan (levels.rank) ────────────────────────────────
-- rank 1 = paling senior. Dipakai Struktur Organisasi supaya level lebih tinggi
-- tampil di atas, bukan sejajar/abjad. Bisa diubah lagi lewat menu
-- Data Karyawan > Job Levels.

ALTER TABLE `levels`
  ADD COLUMN `rank` smallint(5) unsigned NOT NULL DEFAULT 99 AFTER `description`;

UPDATE `levels` SET `rank` = 1 WHERE `name` = 'Direksi';
UPDATE `levels` SET `rank` = 2 WHERE `name` = 'Manager';
UPDATE `levels` SET `rank` = 3 WHERE `name` = 'SPV';
UPDATE `levels` SET `rank` = 4 WHERE `name` = 'Senior Staff';
UPDATE `levels` SET `rank` = 5 WHERE `name` = 'Staff';
UPDATE `levels` SET `rank` = 6 WHERE `name` = 'Admin';

-- ── 2) Tabel Cabang / lokasi kerja ──────────────────────────────────────────
-- employees.branch (varchar, default 'HO') TETAP ada untuk kompat mundur;
-- branch_id adalah sumber kebenaran baru untuk bagan & master data.

CREATE TABLE `branches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `head_employee_id` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `branches_company_id_name_unique` (`company_id`,`name`),
  KEY `branches_head_employee_id_foreign` (`head_employee_id`),
  CONSTRAINT `branches_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `branches_head_employee_id_foreign` FOREIGN KEY (`head_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 3) employees.branch_id ─────────────────────────────────────────────────
ALTER TABLE `employees`
  ADD COLUMN `branch_id` bigint(20) unsigned DEFAULT NULL AFTER `branch`,
  ADD CONSTRAINT `employees_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;

-- ── 4) positions.branch_id ─────────────────────────────────────────────────
-- Untuk posisi tanpa Departemen (CEO/CFO, "Kepala Cabang X") supaya perhitungan
-- "jabatan kosong" per Cabang di bagan organisasi tidak mencampur pool posisi
-- lintas-cabang. Posisi yang sudah terikat Departemen tetap NULL.
ALTER TABLE `positions`
  ADD COLUMN `branch_id` bigint(20) unsigned DEFAULT NULL AFTER `section_id`,
  ADD CONSTRAINT `positions_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;

-- =============================================================================
-- Setelah selesai:
--   1. Isi data cabang lewat menu "Struktur Organisasi > Cabang", lalu petakan
--      karyawan & posisi non-departemen ke cabang-nya.
--      (atau: php artisan db:seed --class=BranchDireksiSeeder untuk contoh awal)
--   2. Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
--
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_25_100000_add_rank_to_levels_table',<BATCH>),
-- ('2026_08_29_190000_create_branches_table',<BATCH>),
-- ('2026_08_29_191500_add_branch_id_to_positions_table',<BATCH>);
-- =============================================================================


-- =============================================================================
-- TAHAP 4 — employee-data-tabs-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: Tab Data Karyawan (Employee Database — PRD HRIS v1.2 Bab 5.2)
--   NSSF/BPJS, Pendidikan, Pengalaman Kerja, Skill, Riwayat Organisasi,
--   Fasilitas, Rekening Bank, Kontrak.
--
-- Setara dengan migration:
--   2026_08_28_100201_create_employee_nssf_table
--   2026_08_28_100202_create_employee_educations_table
--   2026_08_28_100203_create_employee_work_experiences_table
--   2026_08_28_100204_create_employee_skills_table
--   2026_08_28_100205_create_employee_org_experiences_table
--   2026_08_28_100206_create_employee_facilities_table
--   2026_08_28_100207_create_employee_bank_accounts_table
--   2026_08_28_100208_create_employee_contracts_table
--
-- WAJIB backup dulu. Jalankan SETELAH master-data-manual.sql (butuh tabel
-- education_levels, education_majors, banks). Urut dari atas.
-- ============================================================================

CREATE TABLE `employee_nssf` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `health_registered` tinyint(1) NOT NULL DEFAULT 0,
  `health_number` varchar(50) DEFAULT NULL,
  `health_join_date` date DEFAULT NULL,
  `employment_registered` tinyint(1) NOT NULL DEFAULT 0,
  `employment_number` varchar(50) DEFAULT NULL,
  `employment_join_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_nssf_employee_id_unique` (`employee_id`),
  CONSTRAINT `employee_nssf_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_educations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `education_level_id` bigint(20) unsigned DEFAULT NULL,
  `education_major_id` bigint(20) unsigned DEFAULT NULL,
  `institution` varchar(200) DEFAULT NULL,
  `graduation_year` year(4) DEFAULT NULL,
  `gpa` decimal(4,2) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_educations_employee_id_foreign` (`employee_id`),
  KEY `employee_educations_education_level_id_foreign` (`education_level_id`),
  KEY `employee_educations_education_major_id_foreign` (`education_major_id`),
  CONSTRAINT `employee_educations_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_educations_education_level_id_foreign` FOREIGN KEY (`education_level_id`) REFERENCES `education_levels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_educations_education_major_id_foreign` FOREIGN KEY (`education_major_id`) REFERENCES `education_majors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_work_experiences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `company_city` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `end_job_title` varchar(150) DEFAULT NULL,
  `end_pay_rate` bigint(20) DEFAULT NULL,
  `job_description` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_work_experiences_employee_id_foreign` (`employee_id`),
  CONSTRAINT `employee_work_experiences_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_skills` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `proficiency` enum('basic','intermediate','advanced','expert') DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_skills_employee_id_foreign` (`employee_id`),
  CONSTRAINT `employee_skills_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_org_experiences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `position_id` bigint(20) unsigned DEFAULT NULL,
  `unit_name` varchar(200) DEFAULT NULL,
  `position_name` varchar(150) DEFAULT NULL,
  `change_type` enum('join','promotion','rotation','mutation','demotion','other') NOT NULL DEFAULT 'other',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_org_experiences_employee_id_foreign` (`employee_id`),
  KEY `employee_org_experiences_company_id_foreign` (`company_id`),
  KEY `employee_org_experiences_position_id_foreign` (`position_id`),
  CONSTRAINT `employee_org_experiences_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_org_experiences_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_org_experiences_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_facilities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `received_date` date DEFAULT NULL,
  `returned_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_facilities_employee_id_foreign` (`employee_id`),
  CONSTRAINT `employee_facilities_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_bank_accounts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `bank_id` bigint(20) unsigned NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `account_holder_name` varchar(150) NOT NULL,
  `branch_name` varchar(150) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_bank_accounts_employee_id_foreign` (`employee_id`),
  KEY `employee_bank_accounts_bank_id_foreign` (`bank_id`),
  CONSTRAINT `employee_bank_accounts_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_bank_accounts_bank_id_foreign` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_contracts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `contract_type` enum('pkwtt','pkwt','probation','magang','harian','other') NOT NULL DEFAULT 'pkwt',
  `number` varchar(100) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','expired','terminated','renewed') NOT NULL DEFAULT 'active',
  `document_path` varchar(500) DEFAULT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_contracts_employee_id_foreign` (`employee_id`),
  CONSTRAINT `employee_contracts_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Backfill kontrak dari kolom ringkasan employees.contract_end_date yang ada.
INSERT INTO `employee_contracts`
  (`employee_id`, `contract_type`, `start_date`, `end_date`, `status`, `notes`, `created_at`, `updated_at`)
SELECT
  `id`,
  IF(`employment_status` = 'probation', 'probation', 'pkwt'),
  COALESCE(`start_date`, `contract_end_date`),
  `contract_end_date`,
  'active',
  'Migrasi otomatis dari kolom contract_end_date.',
  NOW(), NOW()
FROM `employees`
WHERE `contract_end_date` IS NOT NULL;

-- ============================================================================
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
--
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_100201_create_employee_nssf_table',<BATCH>),
-- ('2026_08_28_100202_create_employee_educations_table',<BATCH>),
-- ('2026_08_28_100203_create_employee_work_experiences_table',<BATCH>),
-- ('2026_08_28_100204_create_employee_skills_table',<BATCH>),
-- ('2026_08_28_100205_create_employee_org_experiences_table',<BATCH>),
-- ('2026_08_28_100206_create_employee_facilities_table',<BATCH>),
-- ('2026_08_28_100207_create_employee_bank_accounts_table',<BATCH>),
-- ('2026_08_28_100208_create_employee_contracts_table',<BATCH>);
-- ============================================================================


-- =============================================================================
-- TAHAP 5 — approval-engine-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: Approval Engine generik (PRD HRIS v1.2 Bab 7)
--
-- Setara dengan migration 2026_08_28_1003xx:
--   approval_workflows, approval_workflow_steps, approval_requests,
--   approval_request_steps, approval_delegations,
--   reward_requests, punishment_requests,
--   promotion_rotation_requests, termination_requests
--
-- WAJIB backup dulu. Jalankan berurutan. Setelah selesai, jalankan seeder
-- workflow default:  php artisan db:seed --class=ApprovalWorkflowSeeder
-- (butuh role 'hr_manager', 'ceo', 'admin' sudah ada).
-- ============================================================================

CREATE TABLE `approval_workflows` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `approval_workflows_company_id_transaction_type_unique` (`company_id`,`transaction_type`),
  CONSTRAINT `approval_workflows_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `approval_workflow_steps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `approval_workflow_id` bigint(20) unsigned NOT NULL,
  `step_order` tinyint(3) unsigned NOT NULL,
  `approver_type` enum('direct_manager','section_head','department_head','division_head','specific_position','specific_role') NOT NULL,
  `approver_position_id` bigint(20) unsigned DEFAULT NULL,
  `approver_role` varchar(50) DEFAULT NULL,
  `conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`conditions`)),
  `escalate_after_days` smallint(5) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `approval_workflow_steps_approval_workflow_id_step_order_unique` (`approval_workflow_id`,`step_order`),
  KEY `approval_workflow_steps_approver_position_id_foreign` (`approver_position_id`),
  CONSTRAINT `approval_workflow_steps_approval_workflow_id_foreign` FOREIGN KEY (`approval_workflow_id`) REFERENCES `approval_workflows` (`id`) ON DELETE CASCADE,
  CONSTRAINT `approval_workflow_steps_approver_position_id_foreign` FOREIGN KEY (`approver_position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `approval_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `approvable_type` varchar(255) NOT NULL,
  `approvable_id` bigint(20) unsigned NOT NULL,
  `approval_workflow_id` bigint(20) unsigned DEFAULT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `summary` varchar(255) DEFAULT NULL,
  `requester_user_id` bigint(20) unsigned DEFAULT NULL,
  `subject_employee_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `current_step_order` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approval_requests_approvable_type_approvable_id_index` (`approvable_type`,`approvable_id`),
  KEY `approval_requests_status_transaction_type_index` (`status`,`transaction_type`),
  KEY `approval_requests_approval_workflow_id_foreign` (`approval_workflow_id`),
  KEY `approval_requests_company_id_foreign` (`company_id`),
  KEY `approval_requests_requester_user_id_foreign` (`requester_user_id`),
  KEY `approval_requests_subject_employee_id_foreign` (`subject_employee_id`),
  CONSTRAINT `approval_requests_approval_workflow_id_foreign` FOREIGN KEY (`approval_workflow_id`) REFERENCES `approval_workflows` (`id`) ON DELETE SET NULL,
  CONSTRAINT `approval_requests_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `approval_requests_requester_user_id_foreign` FOREIGN KEY (`requester_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `approval_requests_subject_employee_id_foreign` FOREIGN KEY (`subject_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `approval_request_steps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `approval_request_id` bigint(20) unsigned NOT NULL,
  `step_order` tinyint(3) unsigned NOT NULL,
  `approver_type` enum('direct_manager','section_head','department_head','division_head','specific_position','specific_role') NOT NULL,
  `approver_label` varchar(150) DEFAULT NULL,
  `approver_user_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('pending','approved','rejected','skipped') NOT NULL DEFAULT 'pending',
  `acted_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `acted_at` timestamp NULL DEFAULT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `due_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approval_request_steps_approver_user_id_status_index` (`approver_user_id`,`status`),
  KEY `approval_request_steps_approval_request_id_foreign` (`approval_request_id`),
  KEY `approval_request_steps_acted_by_user_id_foreign` (`acted_by_user_id`),
  CONSTRAINT `approval_request_steps_approval_request_id_foreign` FOREIGN KEY (`approval_request_id`) REFERENCES `approval_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `approval_request_steps_approver_user_id_foreign` FOREIGN KEY (`approver_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `approval_request_steps_acted_by_user_id_foreign` FOREIGN KEY (`acted_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `approval_delegations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `delegator_user_id` bigint(20) unsigned NOT NULL,
  `delegate_user_id` bigint(20) unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approval_delegations_delegator_user_id_foreign` (`delegator_user_id`),
  KEY `approval_delegations_delegate_user_id_foreign` (`delegate_user_id`),
  CONSTRAINT `approval_delegations_delegator_user_id_foreign` FOREIGN KEY (`delegator_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `approval_delegations_delegate_user_id_foreign` FOREIGN KEY (`delegate_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Transaksi HR baru (dijalankan lewat engine) ──────────────────────────

CREATE TABLE `reward_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `requested_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `reward_type` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `amount` bigint(20) DEFAULT NULL,
  `status` enum('draft','pending','approved','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reward_requests_employee_id_foreign` (`employee_id`),
  CONSTRAINT `reward_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reward_requests_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reward_requests_requested_by_user_id_foreign` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `punishment_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `requested_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `violation_type` varchar(150) NOT NULL,
  `sanction_level` enum('teguran_lisan','sp1','sp2','sp3','demosi','phk','other') NOT NULL DEFAULT 'sp1',
  `description` text DEFAULT NULL,
  `incident_date` date DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `status` enum('draft','pending','approved','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `punishment_requests_employee_id_foreign` (`employee_id`),
  CONSTRAINT `punishment_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `punishment_requests_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `punishment_requests_requested_by_user_id_foreign` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `promotion_rotation_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `requested_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `request_type` enum('promotion','rotation','mutation','demotion') NOT NULL DEFAULT 'promotion',
  `from_position_id` bigint(20) unsigned DEFAULT NULL,
  `to_position_id` bigint(20) unsigned DEFAULT NULL,
  `from_company_id` bigint(20) unsigned DEFAULT NULL,
  `to_company_id` bigint(20) unsigned DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('draft','pending','approved','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `promotion_rotation_requests_employee_id_foreign` (`employee_id`),
  CONSTRAINT `promotion_rotation_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `promotion_rotation_requests_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `promotion_rotation_requests_requested_by_user_id_foreign` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `promotion_rotation_requests_from_position_id_foreign` FOREIGN KEY (`from_position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `promotion_rotation_requests_to_position_id_foreign` FOREIGN KEY (`to_position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `promotion_rotation_requests_from_company_id_foreign` FOREIGN KEY (`from_company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `promotion_rotation_requests_to_company_id_foreign` FOREIGN KEY (`to_company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `termination_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `requested_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `termination_type` enum('resign','pkwt_end','dismissal','retirement','deceased','other') NOT NULL DEFAULT 'resign',
  `reason` text DEFAULT NULL,
  `last_working_date` date DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `status` enum('draft','pending','approved','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `termination_requests_employee_id_foreign` (`employee_id`),
  CONSTRAINT `termination_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `termination_requests_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `termination_requests_requested_by_user_id_foreign` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Setelah semua tabel dibuat:
--   php artisan db:seed --class=ApprovalWorkflowSeeder   (workflow default per PT)
-- lalu HR merapikan alur lewat menu "Persetujuan > Pengaturan Alur".
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_100301_create_approval_workflows_table',<BATCH>),
-- ('2026_08_28_100302_create_approval_workflow_steps_table',<BATCH>),
-- ('2026_08_28_100303_create_approval_requests_table',<BATCH>),
-- ('2026_08_28_100304_create_approval_request_steps_table',<BATCH>),
-- ('2026_08_28_100305_create_approval_delegations_table',<BATCH>),
-- ('2026_08_28_100311_create_reward_requests_table',<BATCH>),
-- ('2026_08_28_100312_create_punishment_requests_table',<BATCH>),
-- ('2026_08_28_100313_create_promotion_rotation_requests_table',<BATCH>),
-- ('2026_08_28_100314_create_termination_requests_table',<BATCH>);
-- ============================================================================


-- =============================================================================
-- TAHAP 6 — role-permission-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: Role & Permission granular per modul + per company (PRD HRIS v1.2 Bab 4)
--
-- Setara dengan migration 2026_08_28_100501_create_role_company_assignments_table.
--
-- Tabel `permissions`/`roles`/`model_has_roles`/`role_has_permissions` (spatie)
-- SUDAH ADA dari migrasi awal (2026_06_16_073113_create_permission_tables) — tidak
-- perlu CREATE TABLE lagi di sini, cuma diisi datanya lewat seeder di bawah.
--
-- WAJIB backup dulu. Jalankan berurutan.
-- ============================================================================

CREATE TABLE `role_company_assignments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `role_company_assignments_role_id_foreign` (`role_id`),
  KEY `role_company_assignments_company_id_foreign` (`company_id`),
  KEY `role_company_assignments_user_id_role_id_index` (`user_id`,`role_id`),
  CONSTRAINT `role_company_assignments_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_company_assignments_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_company_assignments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Setelah tabel dibuat, WAJIB jalankan seeder berikut (data permission tidak
-- praktis ditulis manual — ada di App\Support\PermissionCatalog + mapping role
-- lama->permission di database/seeders/PermissionCatalogSeeder.php):
--
--   php artisan db:seed --class=PermissionCatalogSeeder
--
-- Seeder ini idempoten (aman dijalankan ulang), dan otomatis:
--   1. generate semua baris `permissions` (module.action);
--   2. petakan 8 role lama -> permission (persis akses efektif sebelum migrasi);
--   3. tambah role baru PRD (super_admin, hr_group_admin, hr_admin, hr_payroll,
--      recruiter) dengan mapping default;
--   4. backfill role_company_assignments dari model_has_roles saat ini
--      (company_id NULL = semua company) supaya tidak ada user yang makin
--      terbatas dari kondisi sekarang.
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_100501_create_role_company_assignments_table',<BATCH>);
-- ============================================================================


-- =============================================================================
-- TAHAP 7 — payroll-pph21-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: PPh21 otomatis (TER) + tarif BPJS (PRD HRIS v1.2 Bab 9 — Payroll & Compensation)
--
-- Setara dengan migration 2026_08_28_1006xx.
--
-- ‼️ PENTING: angka batas & persentase TER di bawah ini PERKIRAAN/ILUSTRATIF (bentuk kurva
-- progresif yang benar, bukan salinan persis Lampiran PMK 168/2023). WAJIB divalidasi
-- paralel dengan jPayroll sebelum dipakai untuk penggajian resmi — bisa diedit langsung
-- lewat menu Master Data > Tarif PPh21 (TER), tidak perlu developer/migrasi ulang.
--
-- WAJIB backup dulu. Jalankan berurutan.
-- ============================================================================

CREATE TABLE `ter_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(5) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ter_categories_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ter_categories` (`id`,`code`,`name`,`description`,`created_at`,`updated_at`) VALUES
(1,'A','TER A','PTKP: TK/0, TK/1, K/0',NOW(),NOW()),
(2,'B','TER B','PTKP: TK/2, TK/3, K/1, K/2',NOW(),NOW()),
(3,'C','TER C','PTKP: K/3',NOW(),NOW());

CREATE TABLE `ter_brackets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ter_category_id` bigint(20) unsigned NOT NULL,
  `income_from` bigint(20) unsigned NOT NULL,
  `income_to` bigint(20) unsigned DEFAULT NULL,
  `rate_percent` decimal(5,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ter_brackets_ter_category_id_foreign` (`ter_category_id`),
  CONSTRAINT `ter_brackets_ter_category_id_foreign` FOREIGN KEY (`ter_category_id`) REFERENCES `ter_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ter_brackets` (`ter_category_id`,`income_from`,`income_to`,`rate_percent`,`created_at`,`updated_at`) VALUES
(1,0,5000000,0.00,NOW(),NOW()),
(1,5000000,10000000,2.00,NOW(),NOW()),
(1,10000000,15000000,4.00,NOW(),NOW()),
(1,15000000,20000000,6.00,NOW(),NOW()),
(1,20000000,25000000,8.00,NOW(),NOW()),
(1,25000000,30000000,10.00,NOW(),NOW()),
(1,30000000,40000000,12.00,NOW(),NOW()),
(1,40000000,50000000,15.00,NOW(),NOW()),
(1,50000000,75000000,17.00,NOW(),NOW()),
(1,75000000,100000000,20.00,NOW(),NOW()),
(1,100000000,150000000,23.00,NOW(),NOW()),
(1,150000000,200000000,25.00,NOW(),NOW()),
(1,200000000,500000000,27.00,NOW(),NOW()),
(1,500000000,1000000000,30.00,NOW(),NOW()),
(1,1000000000,NULL,32.00,NOW(),NOW()),
(2,0,5000000,0.00,NOW(),NOW()),
(2,5000000,10000000,3.00,NOW(),NOW()),
(2,10000000,15000000,6.00,NOW(),NOW()),
(2,15000000,20000000,8.00,NOW(),NOW()),
(2,20000000,25000000,11.00,NOW(),NOW()),
(2,25000000,30000000,13.00,NOW(),NOW()),
(2,30000000,40000000,15.00,NOW(),NOW()),
(2,40000000,50000000,18.00,NOW(),NOW()),
(2,50000000,75000000,20.00,NOW(),NOW()),
(2,75000000,100000000,23.00,NOW(),NOW()),
(2,100000000,150000000,25.00,NOW(),NOW()),
(2,150000000,200000000,27.00,NOW(),NOW()),
(2,200000000,500000000,29.00,NOW(),NOW()),
(2,500000000,1000000000,31.00,NOW(),NOW()),
(2,1000000000,NULL,33.00,NOW(),NOW()),
(3,0,5000000,0.00,NOW(),NOW()),
(3,5000000,10000000,5.00,NOW(),NOW()),
(3,10000000,15000000,8.00,NOW(),NOW()),
(3,15000000,20000000,11.00,NOW(),NOW()),
(3,20000000,25000000,14.00,NOW(),NOW()),
(3,25000000,30000000,16.00,NOW(),NOW()),
(3,30000000,40000000,18.00,NOW(),NOW()),
(3,40000000,50000000,21.00,NOW(),NOW()),
(3,50000000,75000000,23.00,NOW(),NOW()),
(3,75000000,100000000,25.00,NOW(),NOW()),
(3,100000000,150000000,28.00,NOW(),NOW()),
(3,150000000,200000000,30.00,NOW(),NOW()),
(3,200000000,500000000,32.00,NOW(),NOW()),
(3,500000000,1000000000,33.00,NOW(),NOW()),
(3,1000000000,NULL,34.00,NOW(),NOW());

-- Tambah calculation_type 'pph21_ter' ke salary_components, lalu alihkan komponen
-- "Potongan PPh 21" yang sudah ada (dulu manual) supaya otomatis. Nama komponen SENGAJA
-- tidak diganti — komponen "Tunjangan PPh 21" (gross-up, calc=mirror_pph21) mencari nominal
-- lewat nama "Potongan PPh 21", jadi harus tetap match.
ALTER TABLE `salary_components` MODIFY COLUMN `calculation_type` ENUM(
  'manual','percent_of_base','late_deduction','medical_claim','mirror_pph21',
  'position_fixed','position_daily','overtime','pph21_ter'
) NOT NULL DEFAULT 'manual';

UPDATE `salary_components` SET `calculation_type` = 'pph21_ter', `is_taxable` = 0
WHERE `name` = 'Potongan PPh 21';

-- ============================================================================
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_100601_create_ter_categories_table',<BATCH>),
-- ('2026_08_28_100602_create_ter_brackets_table',<BATCH>),
-- ('2026_08_28_100603_add_pph21_ter_to_salary_components',<BATCH>);
-- ============================================================================


-- =============================================================================
-- TAHAP 8 — kasbon-bonus-manual.sql
-- =============================================================================
-- =============================================================================
-- Modul: Kasbon/Pinjaman Karyawan + Bukti Potong PPh21 Tahunan + Bonus/Insentif
-- Fase 2 HRD — dikelola HR langsung (Kasbon/Bonus tanpa Approval Engine).
-- Setara dengan migration:
--   2026_08_29_170000_create_employee_loans_tables.php
--   2026_08_29_170100_add_tax_signer_to_companies.php
--   2026_08_29_170200_create_bonus_tables.php
-- WAJIB backup database dulu sebelum menjalankan file ini.
-- Jalankan SETELAH payroll-pph21-manual.sql (butuh salary_components sudah ada).
-- =============================================================================

-- ── A1. Kasbon / Pinjaman karyawan ──────────────────────────────────────────

CREATE TABLE `employee_loans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `loan_type` varchar(20) NOT NULL DEFAULT 'kasbon',
  `reference_no` varchar(50) DEFAULT NULL,
  `principal` bigint(20) NOT NULL,
  `installment_count` smallint(5) unsigned NOT NULL,
  `installment_amount` bigint(20) NOT NULL,
  `start_month` tinyint(3) unsigned NOT NULL,
  `start_year` smallint(5) unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_loans_employee_id_foreign` (`employee_id`),
  KEY `employee_loans_company_id_foreign` (`company_id`),
  KEY `employee_loans_created_by_foreign` (`created_by`),
  CONSTRAINT `employee_loans_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_loans_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_loans_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `loan_installments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_loan_id` bigint(20) unsigned NOT NULL,
  `payroll_slip_id` bigint(20) unsigned DEFAULT NULL,
  `period_month` tinyint(3) unsigned NOT NULL,
  `period_year` smallint(5) unsigned NOT NULL,
  `amount` bigint(20) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `deducted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `loan_installments_payroll_slip_id_foreign` (`payroll_slip_id`),
  KEY `loan_inst_period_idx` (`employee_loan_id`,`period_year`,`period_month`),
  CONSTRAINT `loan_installments_employee_loan_id_foreign` FOREIGN KEY (`employee_loan_id`) REFERENCES `employee_loans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loan_installments_payroll_slip_id_foreign` FOREIGN KEY (`payroll_slip_id`) REFERENCES `payroll_slips` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Komponen potongan cicilan — dihitung otomatis saat generate slip gaji
-- (lihat PayrollController::generate() -> calcLoanInstallment()).
ALTER TABLE salary_components MODIFY COLUMN calculation_type ENUM(
    'manual','percent_of_base','late_deduction','medical_claim','mirror_pph21',
    'position_fixed','position_daily','overtime','pph21_ter','loan_installment'
) NOT NULL DEFAULT 'manual';

INSERT INTO salary_components (company_id, name, type, calculation_type, is_taxable, is_active, sort_order, created_at, updated_at)
SELECT NULL, 'Potongan Kasbon/Pinjaman', 'deduction', 'loan_installment', 0, 1, 15, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM salary_components WHERE name = 'Potongan Kasbon/Pinjaman');

-- ── A2. Bukti Potong PPh21 Tahunan — identitas penandatangan ────────────────
-- companies.npwp sudah ada sejak awal, hanya tambah nama & NPWP penandatangan.

ALTER TABLE companies
  ADD COLUMN tax_signer_name varchar(150) DEFAULT NULL AFTER npwp,
  ADD COLUMN tax_signer_npwp varchar(30) DEFAULT NULL AFTER tax_signer_name;

-- ── A4. Bonus / Insentif ─────────────────────────────────────────────────────
-- Pola sama THR (thr-manual.sql) — one-off run standalone dari payroll_slips.

CREATE TABLE `bonus_periods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `name` varchar(120) NOT NULL,
  `bonus_type` varchar(20) NOT NULL DEFAULT 'bonus',
  `payment_date` date NOT NULL,
  `is_taxable` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `closed_by` bigint(20) unsigned DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bonus_periods_company_id_foreign` (`company_id`),
  KEY `bonus_periods_closed_by_foreign` (`closed_by`),
  CONSTRAINT `bonus_periods_closed_by_foreign` FOREIGN KEY (`closed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bonus_periods_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bonus_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bonus_period_id` bigint(20) unsigned NOT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `base_amount` bigint(20) DEFAULT NULL,
  `gross_amount` bigint(20) NOT NULL DEFAULT 0,
  `tax_amount` bigint(20) NOT NULL DEFAULT 0,
  `net_amount` bigint(20) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bonus_payments_bonus_period_id_employee_id_unique` (`bonus_period_id`,`employee_id`),
  KEY `bonus_payments_employee_id_foreign` (`employee_id`),
  CONSTRAINT `bonus_payments_bonus_period_id_foreign` FOREIGN KEY (`bonus_period_id`) REFERENCES `bonus_periods` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bonus_payments_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tidak perlu seeder baru — Kasbon & Bonus memakai permission `payroll.*` yang sudah ada.

-- Setelah migrasi manual di atas, tandai migration sebagai sudah dijalankan
-- (opsional, biar `php artisan migrate` di CI tidak mencoba jalanin ulang):
-- INSERT INTO migrations (migration, batch) VALUES
--   ('2026_08_29_170000_create_employee_loans_tables', <batch>),
--   ('2026_08_29_170100_add_tax_signer_to_companies', <batch>),
--   ('2026_08_29_170200_create_bonus_tables', <batch>);


-- =============================================================================
-- TAHAP 9 — thr-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: THR - Tunjangan Hari Raya (PRD HRIS v1.2 Bab 3 modul #9 — Payroll &
-- Compensation menyebut eksplisit "THR").
--
-- Setara dengan migration 2026_08_28_101201. Dihitung sesuai Permenaker No.
-- 6/2016: masa kerja >=1 bulan berhak THR proporsional (bulan kerja / 12,
-- dibatasi maks 12) x (Gaji Pokok + Tunjangan Jabatan).
--
-- WAJIB backup dulu. Jalankan setelah master-organization-manual.sql &
-- payroll (butuh companies/employees/positions/salary_components sudah ada).
-- ============================================================================

CREATE TABLE `thr_periods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `year` smallint(5) unsigned NOT NULL,
  `holiday_name` varchar(100) NOT NULL,
  `payment_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `closed_by` bigint(20) unsigned DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `thr_periods_company_id_foreign` (`company_id`),
  KEY `thr_periods_closed_by_foreign` (`closed_by`),
  CONSTRAINT `thr_periods_closed_by_foreign` FOREIGN KEY (`closed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `thr_periods_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `thr_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `thr_period_id` bigint(20) unsigned NOT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `base_salary` bigint(20) NOT NULL,
  `months_worked` tinyint(3) unsigned NOT NULL,
  `proration_ratio` decimal(4,3) NOT NULL,
  `thr_amount` bigint(20) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `thr_payments_thr_period_id_employee_id_unique` (`thr_period_id`,`employee_id`),
  KEY `thr_payments_employee_id_foreign` (`employee_id`),
  CONSTRAINT `thr_payments_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `thr_payments_thr_period_id_foreign` FOREIGN KEY (`thr_period_id`) REFERENCES `thr_periods` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Tidak perlu seeder baru — modul memakai permission `payroll.*` yang sudah ada.
--
-- Catatan sekaligus: batch ini juga menambah 2 method baru di
-- App\Http\Controllers\HR\PayrollController (mySlips/mySlipPdf) untuk ESS
-- "lihat slip gaji" (PRD Bab 4) — tidak perlu perubahan skema, cukup deploy kode.
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_101201_create_thr_periods_table',<BATCH>);
-- ============================================================================


-- =============================================================================
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
-- TAHAP 11 — manpower-planning-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: Manpower Planning (PRD HRIS v1.2 Bab 3 modul #2)
--
-- Setara dengan migration 2026_08_28_100701_create_manpower_plans_table.
--
-- WAJIB backup dulu. Jalankan setelah role-permission-manual.sql &
-- approval-engine-manual.sql (butuh company/departments/sections/positions/users sudah ada).
-- ============================================================================

CREATE TABLE `manpower_plans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `section_id` bigint(20) unsigned DEFAULT NULL,
  `position_id` bigint(20) unsigned DEFAULT NULL,
  `year` smallint(5) unsigned NOT NULL,
  `month` tinyint(3) unsigned DEFAULT NULL,
  `planned_headcount` int(10) unsigned NOT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `requested_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `notes_rejection` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `manpower_plans_company_id_foreign` (`company_id`),
  KEY `manpower_plans_department_id_foreign` (`department_id`),
  KEY `manpower_plans_section_id_foreign` (`section_id`),
  KEY `manpower_plans_position_id_foreign` (`position_id`),
  KEY `manpower_plans_requested_by_user_id_foreign` (`requested_by_user_id`),
  CONSTRAINT `manpower_plans_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `manpower_plans_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `manpower_plans_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `manpower_plans_requested_by_user_id_foreign` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `manpower_plans_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Setelah tabel dibuat, WAJIB jalankan ulang seeder berikut (idempoten):
--   php artisan db:seed --class=PermissionCatalogSeeder   (permission manpower-plan.*)
--   php artisan db:seed --class=ApprovalWorkflowSeeder    (workflow manpower_plan_request per company)
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_100701_create_manpower_plans_table',<BATCH>);
-- ============================================================================


-- =============================================================================
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
  `category` varchar(20) NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `onboarding_checklist_items_company_id_foreign` (`company_id`),
  CONSTRAINT `onboarding_checklist_items_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_onboarding_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `onboarding_checklist_item_id` bigint(20) unsigned NOT NULL,
  `is_done` tinyint(1) NOT NULL DEFAULT 0,
  `done_at` datetime DEFAULT NULL,
  `done_by_user_id` bigint(20) unsigned DEFAULT NULL,
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

-- ============================================================================
-- Setelah tabel dibuat, WAJIB jalankan ulang seeder berikut (idempoten):
--   php artisan db:seed --class=PermissionCatalogSeeder   (permission recruitment.*)
--   php artisan db:seed --class=ApprovalWorkflowSeeder    (workflow job_requisition per company)
-- Lalu isi template checklist onboarding lewat menu Rekrutmen > Onboarding > Template Checklist
-- (tidak ada seed default — beda-beda tiap perusahaan).
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_100801_create_job_requisitions_table',<BATCH>),
-- ('2026_08_28_100802_create_candidates_tables',<BATCH>),
-- ('2026_08_28_100803_create_onboarding_tables',<BATCH>);
-- ============================================================================


-- =============================================================================
-- TAHAP 13 — training-career-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: Training & Development + Career Management (PRD HRIS v1.2 Bab 3 modul #11/#12)
--
-- Setara dengan migration 2026_08_28_1009xx.
--
-- WAJIB backup dulu. Jalankan setelah role-permission-manual.sql (butuh
-- companies/positions/employees sudah ada).
-- ============================================================================

CREATE TABLE `training_programs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `provider` varchar(150) DEFAULT NULL,
  `duration_hours` int(10) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `training_programs_company_id_foreign` (`company_id`),
  CONSTRAINT `training_programs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `training_participants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `training_program_id` bigint(20) unsigned NOT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'planned',
  `score` varchar(20) DEFAULT NULL,
  `certificate_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `training_participants_training_program_id_foreign` (`training_program_id`),
  KEY `training_participants_employee_id_foreign` (`employee_id`),
  CONSTRAINT `training_participants_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `training_participants_training_program_id_foreign` FOREIGN KEY (`training_program_id`) REFERENCES `training_programs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `career_paths` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `career_paths_company_id_foreign` (`company_id`),
  CONSTRAINT `career_paths_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `career_path_steps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `career_path_id` bigint(20) unsigned NOT NULL,
  `position_id` bigint(20) unsigned NOT NULL,
  `step_order` int(10) unsigned NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `career_path_steps_career_path_id_foreign` (`career_path_id`),
  KEY `career_path_steps_position_id_foreign` (`position_id`),
  CONSTRAINT `career_path_steps_career_path_id_foreign` FOREIGN KEY (`career_path_id`) REFERENCES `career_paths` (`id`) ON DELETE CASCADE,
  CONSTRAINT `career_path_steps_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `employees`
  ADD COLUMN `career_path_id` bigint(20) unsigned DEFAULT NULL AFTER `level_id`,
  ADD CONSTRAINT `employees_career_path_id_foreign` FOREIGN KEY (`career_path_id`) REFERENCES `career_paths` (`id`) ON DELETE SET NULL;

-- ============================================================================
-- Setelah tabel dibuat, WAJIB jalankan ulang seeder berikut (idempoten):
--   php artisan db:seed --class=PermissionCatalogSeeder   (permission training.*, career.*)
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_100901_create_training_programs_table',<BATCH>),
-- ('2026_08_28_100902_create_training_participants_table',<BATCH>),
-- ('2026_08_28_100903_create_career_paths_table',<BATCH>),
-- ('2026_08_28_100904_create_career_path_steps_table',<BATCH>),
-- ('2026_08_28_100905_add_career_path_id_to_employees_table',<BATCH>);
-- ============================================================================


-- =============================================================================
-- TAHAP 14 — competency-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: Competency Framework (melengkapi PRD HRIS v1.2 Bab 3 modul #11
--        Training & Development — bagian "competency").
--
-- Setara dengan migration 2026_08_29_150000_create_competency_tables.
--
-- WAJIB backup dulu. Jalankan setelah training-career-manual.sql (butuh
-- companies/positions/employees/users sudah ada).
-- ============================================================================

CREATE TABLE `competencies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `competencies_code_unique` (`code`),
  KEY `competencies_company_id_foreign` (`company_id`),
  CONSTRAINT `competencies_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `position_competencies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `position_id` bigint(20) unsigned NOT NULL,
  `competency_id` bigint(20) unsigned NOT NULL,
  `required_level` tinyint(3) unsigned NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `position_competencies_position_id_competency_id_unique` (`position_id`,`competency_id`),
  KEY `position_competencies_competency_id_foreign` (`competency_id`),
  CONSTRAINT `position_competencies_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `position_competencies_competency_id_foreign` FOREIGN KEY (`competency_id`) REFERENCES `competencies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_competencies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `competency_id` bigint(20) unsigned NOT NULL,
  `actual_level` tinyint(3) unsigned NOT NULL,
  `assessed_on` date DEFAULT NULL,
  `assessor_user_id` bigint(20) unsigned DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_competencies_employee_id_competency_id_unique` (`employee_id`,`competency_id`),
  KEY `employee_competencies_competency_id_foreign` (`competency_id`),
  KEY `employee_competencies_assessor_user_id_foreign` (`assessor_user_id`),
  CONSTRAINT `employee_competencies_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_competencies_competency_id_foreign` FOREIGN KEY (`competency_id`) REFERENCES `competencies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_competencies_assessor_user_id_foreign` FOREIGN KEY (`assessor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Setelah tabel dibuat, WAJIB jalankan seeder berikut (idempoten):
--   php artisan db:seed --class=PermissionCatalogSeeder   (permission competency.*)
--   php artisan db:seed --class=CompetencySeeder          (~10 kompetensi default)
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_29_150000_create_competency_tables',<BATCH>);
-- ============================================================================


-- =============================================================================
-- TAHAP 15 — employee-administration-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: Employee Administration — surat, perubahan data (PRD HRIS v1.2 Bab 3
-- modul #7; "kontrak" di modul ini sudah ada sejak batch employee-data-tabs).
--
-- Setara dengan migration 2026_08_28_1010xx.
--
-- WAJIB backup dulu. Jalankan setelah role-permission-manual.sql &
-- approval-engine-manual.sql (butuh employees/users/companies sudah ada).
-- ============================================================================

CREATE TABLE `employee_data_change_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `requested_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `field_key` varchar(50) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text NOT NULL,
  `reason` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `notes_rejection` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_data_change_requests_employee_id_foreign` (`employee_id`),
  KEY `employee_data_change_requests_requested_by_user_id_foreign` (`requested_by_user_id`),
  CONSTRAINT `employee_data_change_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_data_change_requests_requested_by_user_id_foreign` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `letter_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'lainnya',
  `body` longtext NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `letter_templates_company_id_foreign` (`company_id`),
  CONSTRAINT `letter_templates_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_letters` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `letter_template_id` bigint(20) unsigned DEFAULT NULL,
  `letter_number` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` longtext NOT NULL,
  `issued_date` date NOT NULL,
  `issued_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_letters_employee_id_foreign` (`employee_id`),
  KEY `employee_letters_letter_template_id_foreign` (`letter_template_id`),
  KEY `employee_letters_issued_by_user_id_foreign` (`issued_by_user_id`),
  CONSTRAINT `employee_letters_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_letters_issued_by_user_id_foreign` FOREIGN KEY (`issued_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_letters_letter_template_id_foreign` FOREIGN KEY (`letter_template_id`) REFERENCES `letter_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Setelah tabel dibuat, WAJIB jalankan ulang seeder berikut (idempoten):
--   php artisan db:seed --class=ApprovalWorkflowSeeder  (workflow
--   employee_data_change_request per company)
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_101001_create_employee_data_change_requests_table',<BATCH>),
-- ('2026_08_28_101002_create_letter_templates_table',<BATCH>),
-- ('2026_08_28_101003_create_employee_letters_table',<BATCH>);
-- ============================================================================


-- =============================================================================
-- TAHAP 16 — appraisal-kpi-manual.sql
-- =============================================================================
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


-- =============================================================================
-- TAHAP 17 — approval-audit-region-manual.sql
-- =============================================================================
-- ============================================================================
-- Batch: 3 celah PRD terakhir.
--   1. Audit trail perubahan aturan approval (Bab 7.5)
--   2. Master Kecamatan & Kelurahan (Bab 5.1)
--   3. Dashboard Headcount (Bab 10) — TIDAK ADA perubahan skema (kode + view saja)
--
-- Setara dengan migration:
--   2026_08_29_160000_create_approval_workflow_change_logs_table
--   2026_08_29_161000_create_districts_and_villages_tables
--   2026_08_29_161100_add_district_village_fk_to_employees
--
-- WAJIB backup dulu. Jalankan setelah gap-closure-manual.sql & competency-manual.sql.
-- ============================================================================

-- ── 1) Audit perubahan alur persetujuan ─────────────────────────────────────
CREATE TABLE `approval_workflow_change_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `transaction_label` varchar(100) DEFAULT NULL,
  `action` varchar(20) NOT NULL,
  `before` longtext DEFAULT NULL CHECK (json_valid(`before`)),
  `after` longtext DEFAULT NULL CHECK (json_valid(`after`)),
  `changed_by` bigint(20) unsigned DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `awcl_company_type_idx` (`company_id`,`transaction_type`),
  KEY `awcl_changed_by_foreign` (`changed_by`),
  CONSTRAINT `awcl_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `awcl_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 2) Master Kecamatan & Kelurahan ─────────────────────────────────────────
CREATE TABLE `districts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `city_id` bigint(20) unsigned NOT NULL,
  `code` varchar(15) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `districts_code_unique` (`code`),
  KEY `districts_city_id_name_index` (`city_id`,`name`),
  CONSTRAINT `districts_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `villages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `district_id` bigint(20) unsigned NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(15) NOT NULL DEFAULT 'kelurahan',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `villages_code_unique` (`code`),
  KEY `villages_district_id_name_index` (`district_id`,`name`),
  CONSTRAINT `villages_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `employees`
  ADD COLUMN `domicile_district_id` bigint(20) unsigned DEFAULT NULL AFTER `domicile_district`,
  ADD COLUMN `domicile_village_id` bigint(20) unsigned DEFAULT NULL AFTER `domicile_subdistrict`,
  ADD COLUMN `ktp_district_id` bigint(20) unsigned DEFAULT NULL AFTER `ktp_district`,
  ADD COLUMN `ktp_village_id` bigint(20) unsigned DEFAULT NULL AFTER `ktp_subdistrict`,
  ADD CONSTRAINT `employees_domicile_district_id_foreign` FOREIGN KEY (`domicile_district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_domicile_village_id_foreign` FOREIGN KEY (`domicile_village_id`) REFERENCES `villages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_ktp_district_id_foreign` FOREIGN KEY (`ktp_district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_ktp_village_id_foreign` FOREIGN KEY (`ktp_village_id`) REFERENCES `villages` (`id`) ON DELETE SET NULL;

-- Backfill dari nilai teks lama (jalankan SETELAH RegionDistrictSeeder)
UPDATE `employees` e JOIN `districts` d ON LOWER(TRIM(e.`domicile_district`)) = LOWER(d.`name`) SET e.`domicile_district_id` = d.`id` WHERE e.`domicile_district_id` IS NULL;
UPDATE `employees` e JOIN `districts` d ON LOWER(TRIM(e.`ktp_district`))      = LOWER(d.`name`) SET e.`ktp_district_id`      = d.`id` WHERE e.`ktp_district_id` IS NULL;
UPDATE `employees` e JOIN `villages`  v ON LOWER(TRIM(e.`domicile_subdistrict`)) = LOWER(v.`name`) SET e.`domicile_village_id` = v.`id` WHERE e.`domicile_village_id` IS NULL;
UPDATE `employees` e JOIN `villages`  v ON LOWER(TRIM(e.`ktp_subdistrict`))      = LOWER(v.`name`) SET e.`ktp_village_id`      = v.`id` WHERE e.`ktp_village_id` IS NULL;

-- ============================================================================
-- Setelah tabel dibuat, jalankan seeder starter (idempoten):
--   php artisan db:seed --class=RegionDistrictSeeder
--   (kecamatan ~6 kota besar + kelurahan Jakarta Selatan/Pusat)
--
-- DATASET PENUH INDONESIA (~7rb kecamatan, ~83rb kelurahan) di-import terpisah
-- dari wilayah.id / Kemendagri — masukkan ke database/data/regions-districts.php
-- lalu jalankan ulang RegionDistrictSeeder (aman diulang).
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_08_29_160000_create_approval_workflow_change_logs_table',<BATCH>),
--   ('2026_08_29_161000_create_districts_and_villages_tables',<BATCH>),
--   ('2026_08_29_161100_add_district_village_fk_to_employees',<BATCH>);
-- ============================================================================

-- ###########################################################################
-- >>> STOP. DEPLOY KODE APLIKASI SEKARANG (sebelum TAHAP 18):
-- >>>   - upload kode rilis (app/ resources/ routes/ config/ lang/ public/ database/)
-- >>>   - composer install --no-dev --optimize-autoloader
-- >>>   - .env: APP_NAME=ProPeople  (APP_KEY JANGAN diubah)
-- >>>   - php artisan config:cache && php artisan route:cache && php artisan view:cache
-- >>> Kolom terenkripsi (TAHAP 18) butuh cast model yang baru ini.
-- ###########################################################################



-- =============================================================================
-- TAHAP 18 — gap-closure-manual.sql
-- =============================================================================
-- ============================================================================
-- Batch: Penutupan 9 gap PRD HRIS v1.2 (cross-module / NFR / reporting).
--
-- Setara dengan migration:
--   2026_08_29_140000_encrypt_employee_sensitive_data
--   2026_08_29_141000_drop_legacy_enum_columns_from_employees
--
-- Sebagian besar dari 9 item BUKAN perubahan skema (cuma kode) dan tidak butuh
-- SQL manual:
--   1. Notifikasi email approval generik      -> app/Services/ApprovalEngine.php + 2 Mailable baru
--   2. Audit trail (LogsActivity) 9 model     -> pakai tabel `activity_log` yang sudah ada
--   3. Escalation reminder terjadwal          -> app/Console/Commands/SendApprovalOverdueReminders.php
--                                                + Schedule di routes/console.php (butuh cron
--                                                `php artisan schedule:run` tiap menit di server)
--   5. Backup DB harian otomatis              -> app/Console/Commands/BackupDatabase.php + Schedule
--                                                (butuh binary `mysqldump` di PATH server)
--   6. Laporan Absensi & Cuti bulanan         -> LaporanController + view (route laporan.attendance-leave)
--   7. Laporan Payroll summary per periode    -> LaporanController + view (route laporan.payroll)
--   8. Org chart drag & drop reparenting      -> view + endpoint reparent (permission org-structure.edit)
--
-- Yang di bawah ini HANYA item 4 (enkripsi) & 9 (drop kolom lama).
--
-- WAJIB backup dulu.
-- ============================================================================

-- ── Item 4: Enkripsi data sensitif (PRD Bab 9 — NIK, NPWP, No. rekening) ─────
-- Lebarkan kolom ke TEXT (ciphertext Laravel jauh lebih panjang dari plaintext).
ALTER TABLE `employees`
  MODIFY `ktp_number`  text DEFAULT NULL,
  MODIFY `npwp_number` text DEFAULT NULL;

ALTER TABLE `employee_bank_accounts`
  MODIFY `account_number` text NOT NULL;

-- PENTING: setelah ALTER di atas, WAJIB jalankan command berikut di server untuk
-- mengenkripsi data yang sudah ada (SQL murni tidak bisa — butuh APP_KEY Laravel):
--
--     php artisan employees:encrypt-sensitive
--
-- Command ini idempotent (aman diulang). Cast 'encrypted' sudah ditambahkan di
-- App\Models\Employee & App\Models\EmployeeBankAccount — jangan deploy kode model
-- baru SEBELUM data lama dienkripsi, atau read via Eloquent akan melempar
-- DecryptException. Urutan aman: (a) ALTER TABLE, (b) deploy kode, (c) segera
-- jalankan `php artisan employees:encrypt-sensitive`.
--
-- CATATAN APP_KEY: nilai terenkripsi terikat ke APP_KEY. Jangan rotate APP_KEY
-- setelah data dienkripsi tanpa proses re-enkripsi (decrypt pakai key lama →
-- encrypt pakai key baru), atau data NIK/NPWP/rekening jadi tidak terbaca.

-- ── Item 9: Drop kolom string lama di `employees` (sudah digantikan FK master) ─
-- marital_status -> marital_status_id | religion_name -> religion_id | blood_type -> blood_type_id
-- (FK sudah lama diisi; tidak ada read-path tersisa ke kolom string ini).
-- contract_end_date SENGAJA TIDAK di-drop — masih aktif dipakai (contract reminder,
-- dashboard, laporan, notifikasi header).
ALTER TABLE `employees`
  DROP COLUMN `marital_status`,
  DROP COLUMN `religion_name`,
  DROP COLUMN `blood_type`;

-- ============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_08_29_140000_encrypt_employee_sensitive_data', <BATCH>),
--   ('2026_08_29_141000_drop_legacy_enum_columns_from_employees', <BATCH>);
-- ============================================================================

-- ###########################################################################
-- >>> WAJIB SEGERA setelah TAHAP 18 (dan kode sudah live):
-- >>>   php artisan employees:encrypt-sensitive
-- >>> Meng-enkripsi NIK/NPWP/no. rekening yang sudah ada. Idempotent.
-- >>> Tanpa ini, Eloquent lempar DecryptException di semua halaman karyawan.
-- ###########################################################################



-- =============================================================================
-- TAHAP 19 — approval-status-normalisasi-manual.sql
-- =============================================================================
-- =============================================================================
-- Modul: Normalisasi kolom status modul lama saat pindah ke Approval Engine
--        (Cuti / Perjalanan Dinas / Reimbursement)
--
-- Setara dengan migration:
--   2026_08_28_100401_migrate_leave_requests_to_approval_engine
--   2026_08_28_100402_migrate_perdin_requests_to_approval_engine
--   2026_08_28_100403_migrate_reimbursement_requests_to_approval_engine
--
-- WAJIB backup database dulu.
-- Jalankan SETELAH approval-engine-manual.sql (tabel approval_* & workflow seeder
-- sudah ada) dan SETELAH deploy kode baru.
--
-- CATATAN PENTING soal pengajuan yang masih berjalan (in-flight):
--   File SQL ini hanya (a) mengubah tipe kolom status enum -> varchar dan
--   (b) menormalkan NILAI status lama. Ia TIDAK membuat baris approval_requests
--   untuk pengajuan yang masih menunggu persetujuan (butuh logika PHP
--   ApprovalEngine::start()).
--
--   Pilihan:
--   A. Deploy saat tidak ada pengajuan berjalan (paling aman) — mis. minta
--      approver menuntaskan semua pengajuan pending dulu.
--   B. Jalankan hanya 3 migration ini via artisan supaya in-flight ikut
--      terdaftar ke engine:
--        php artisan migrate --path=database/migrations/2026_08_28_100401_migrate_leave_requests_to_approval_engine.php
--        php artisan migrate --path=database/migrations/2026_08_28_100402_migrate_perdin_requests_to_approval_engine.php
--        php artisan migrate --path=database/migrations/2026_08_28_100403_migrate_reimbursement_requests_to_approval_engine.php
--      (kalau pakai opsi B, JANGAN jalankan blok SQL di bawah — migration sudah
--       melakukannya.)
--   C. Jalankan SQL di bawah, lalu minta pemohon submit ulang pengajuan yang
--      masih pending.
-- =============================================================================

-- ── 1) Cuti (leave_requests) ────────────────────────────────────────────────
-- enum lama: draft / submitted / approved_manager / approved_hr / rejected
ALTER TABLE `leave_requests`
  MODIFY COLUMN `status` VARCHAR(30) NOT NULL DEFAULT 'draft';

UPDATE `leave_requests` SET `status` = 'pending'  WHERE `status` IN ('submitted', 'approved_manager');
UPDATE `leave_requests` SET `status` = 'approved' WHERE `status` = 'approved_hr';
-- 'rejected' & 'draft' dibiarkan apa adanya.

-- ── 2) Perjalanan Dinas (perdin_requests) ──────────────────────────────────
-- enum lama: draft / submitted / reviewed_manager / reviewed_hr / approved / rejected
ALTER TABLE `perdin_requests`
  MODIFY COLUMN `status` VARCHAR(30) NOT NULL DEFAULT 'draft';

UPDATE `perdin_requests` SET `status` = 'pending'
  WHERE `status` IN ('submitted', 'reviewed_manager', 'reviewed_hr');
-- 'approved' / 'rejected' / 'draft' dibiarkan apa adanya.

-- ── 3) Reimbursement (reimbursement_requests) ──────────────────────────────
-- kolom status sudah VARCHAR(20) sejak awal — cukup normalisasi nilai.
UPDATE `reimbursement_requests` SET `status` = 'pending' WHERE `status` = 'submitted';

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_100401_migrate_leave_requests_to_approval_engine',<BATCH>),
-- ('2026_08_28_100402_migrate_perdin_requests_to_approval_engine',<BATCH>),
-- ('2026_08_28_100403_migrate_reimbursement_requests_to_approval_engine',<BATCH>);
-- =============================================================================


-- =============================================================================
-- TAHAP 20 — shift-leave-policy-manual.sql
-- =============================================================================
-- =============================================================================
-- Modul: Shift & Roster + Kebijakan Cuti (carry-forward / kuota per golongan)
-- Fase 2 HRD. Setara dengan migration:
--   2026_08_29_180000_create_shift_and_roster_tables.php
--   2026_08_29_180100_create_leave_policies_table.php
-- WAJIB backup database dulu sebelum menjalankan file ini.
-- Jalankan SETELAH hr-module-manual.sql (butuh attendance_records & leave_balances ada).
-- =============================================================================

-- ── B1. Shift & Roster ───────────────────────────────────────────────────────

CREATE TABLE `shifts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(60) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `break_minutes` smallint(6) NOT NULL DEFAULT 0,
  `crosses_midnight` tinyint(1) NOT NULL DEFAULT 0,
  `late_grace_minutes` smallint(6) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shifts_company_id_foreign` (`company_id`),
  CONSTRAINT `shifts_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roster_entries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `work_date` date NOT NULL,
  `shift_id` bigint(20) unsigned DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roster_entries_employee_id_work_date_unique` (`employee_id`,`work_date`),
  KEY `roster_entries_company_id_foreign` (`company_id`),
  KEY `roster_entries_shift_id_foreign` (`shift_id`),
  CONSTRAINT `roster_entries_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `roster_entries_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `roster_entries_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE attendance_records
  ADD COLUMN shift_id bigint(20) unsigned DEFAULT NULL AFTER company_id,
  ADD COLUMN scheduled_start time DEFAULT NULL AFTER shift_id,
  ADD COLUMN scheduled_end time DEFAULT NULL AFTER scheduled_start,
  ADD CONSTRAINT attendance_records_shift_id_foreign FOREIGN KEY (shift_id) REFERENCES shifts (id) ON DELETE SET NULL;

-- Shift default (dipakai AttendanceController::import() sebagai fallback saat
-- karyawan tidak punya roster hari itu — cari code='PAGI').
INSERT INTO shifts (company_id, code, name, start_time, end_time, break_minutes, crosses_midnight, late_grace_minutes, is_active, created_at, updated_at) VALUES
  (NULL, 'PAGI',  'Pagi (08:00–17:00)',  '08:00:00', '17:00:00', 60, 0, 15, 1, NOW(), NOW()),
  (NULL, 'SIANG', 'Siang (15:00–23:00)', '15:00:00', '23:00:00', 60, 0, 15, 1, NOW(), NOW()),
  (NULL, 'MALAM', 'Malam (23:00–07:00)', '23:00:00', '07:00:00', 60, 1, 15, 1, NOW(), NOW());

-- ── B2. Kebijakan Cuti (carry-forward / hangus + kuota per golongan) ────────

CREATE TABLE `leave_policies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `leave_type_id` bigint(20) unsigned NOT NULL,
  `level_id` bigint(20) unsigned DEFAULT NULL,
  `min_years_service` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `quota_days` decimal(5,1) NOT NULL,
  `carry_forward_max_days` decimal(5,1) NOT NULL DEFAULT 0.0,
  `carry_forward_expire_month` tinyint(3) unsigned NOT NULL DEFAULT 3,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_policies_company_id_foreign` (`company_id`),
  KEY `leave_policies_level_id_foreign` (`level_id`),
  KEY `leave_policies_leave_type_id_level_id_index` (`leave_type_id`,`level_id`),
  CONSTRAINT `leave_policies_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_policies_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_policies_level_id_foreign` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE leave_balances
  ADD COLUMN carried_days decimal(5,1) NOT NULL DEFAULT 0.0 AFTER used,
  ADD COLUMN carried_expires_on date DEFAULT NULL AFTER carried_days;

-- Kebijakan default (LeavePolicySeeder): Cuti Tahunan 12 hari/tahun semua PT &
-- semua level, boleh dibawa maks 6 hari, hangus akhir Maret tahun berikutnya.
INSERT INTO leave_policies (company_id, leave_type_id, level_id, min_years_service, quota_days, carry_forward_max_days, carry_forward_expire_month, is_active, created_at, updated_at)
SELECT NULL, lt.id, NULL, 0, 12, 6, 3, 1, NOW(), NOW()
FROM leave_types lt WHERE lt.name = 'Cuti Tahunan'
  AND NOT EXISTS (
    SELECT 1 FROM leave_policies lp
    WHERE lp.leave_type_id = lt.id AND lp.company_id IS NULL AND lp.level_id IS NULL AND lp.min_years_service = 0
  );

-- Scheduler tambahan (tambahkan di server, lihat routes/console.php):
--   leave:year-end        -> yearlyOn(1, 1, '02:00')  — alokasi saldo cuti tahun baru + carry-forward
--   leave:expire-carry    -> dailyAt('02:30')          — hanguskan carry yang lewat tanggal kadaluarsa

-- INSERT INTO migrations (migration, batch) VALUES
--   ('2026_08_29_180000_create_shift_and_roster_tables', <batch>),
--   ('2026_08_29_180100_create_leave_policies_table', <batch>);


-- =============================================================================
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
-- TAHAP 22 — engagement-analytics-manual.sql
-- =============================================================================
-- =============================================================================
-- Modul: Pengumuman + Survey Engagement + HR Analytics (Biaya Rekrutmen)
-- Fase 2 HRD. Setara dengan migration:
--   2026_08_29_180400_create_announcements_tables.php
--   2026_08_29_180500_create_survey_tables.php
--   2026_08_29_180600_create_recruitment_costs_table.php
-- WAJIB backup database dulu sebelum menjalankan file ini.
-- Jalankan SETELAH master-organization-manual.sql & recruitment-manual.sql
-- (butuh companies, departments, job_requisitions ada).
-- =============================================================================

-- ── D1. Pengumuman / Broadcast / Papan Info ─────────────────────────────────

CREATE TABLE `announcements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `body` longtext NOT NULL,
  `category` varchar(20) NOT NULL DEFAULT 'info',
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `published_at` datetime DEFAULT NULL,
  `expires_at` date DEFAULT NULL,
  `attachment_path` varchar(500) DEFAULT NULL,
  `attachment_name` varchar(255) DEFAULT NULL,
  `created_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `announcements_company_id_foreign` (`company_id`),
  KEY `announcements_created_by_user_id_foreign` (`created_by_user_id`),
  CONSTRAINT `announcements_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `announcements_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `announcement_reads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `announcement_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `read_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `announcement_reads_announcement_id_user_id_unique` (`announcement_id`,`user_id`),
  KEY `announcement_reads_user_id_foreign` (`user_id`),
  CONSTRAINT `announcement_reads_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `announcement_reads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── D2. Survey Kepuasan / Engagement ─────────────────────────────────────────

CREATE TABLE `surveys` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `opens_at` date DEFAULT NULL,
  `closes_at` date DEFAULT NULL,
  `created_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `surveys_company_id_foreign` (`company_id`),
  KEY `surveys_created_by_user_id_foreign` (`created_by_user_id`),
  CONSTRAINT `surveys_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `surveys_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `survey_questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `survey_id` bigint(20) unsigned NOT NULL,
  `text` varchar(500) NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'scale',
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `survey_questions_survey_id_foreign` (`survey_id`),
  CONSTRAINT `survey_questions_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `survey_responses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `survey_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `employee_id` bigint(20) unsigned DEFAULT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `submitted_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `survey_responses_survey_id_foreign` (`survey_id`),
  KEY `survey_responses_user_id_foreign` (`user_id`),
  KEY `survey_responses_employee_id_foreign` (`employee_id`),
  KEY `survey_responses_company_id_foreign` (`company_id`),
  KEY `survey_responses_department_id_foreign` (`department_id`),
  CONSTRAINT `survey_responses_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `survey_responses_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `survey_responses_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `survey_responses_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE,
  CONSTRAINT `survey_responses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `survey_answers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `survey_response_id` bigint(20) unsigned NOT NULL,
  `survey_question_id` bigint(20) unsigned NOT NULL,
  `value` text DEFAULT NULL,
  `value_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`value_json`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `survey_answers_survey_response_id_foreign` (`survey_response_id`),
  KEY `survey_answers_survey_question_id_foreign` (`survey_question_id`),
  CONSTRAINT `survey_answers_survey_question_id_foreign` FOREIGN KEY (`survey_question_id`) REFERENCES `survey_questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `survey_answers_survey_response_id_foreign` FOREIGN KEY (`survey_response_id`) REFERENCES `survey_responses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── D3. HR Analytics — Biaya Rekrutmen (input metrik cost-per-hire) ─────────
-- Turnover rate, absenteeism rate, dan efektivitas training TIDAK butuh tabel
-- baru — dihitung langsung dari termination_requests, attendance_records, dan
-- training_participants yang sudah ada (lihat LaporanController::analytics()).

CREATE TABLE `recruitment_costs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `job_requisition_id` bigint(20) unsigned DEFAULT NULL,
  `category` varchar(40) NOT NULL,
  `amount` bigint(20) NOT NULL,
  `incurred_on` date NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recruitment_costs_company_id_foreign` (`company_id`),
  KEY `recruitment_costs_job_requisition_id_foreign` (`job_requisition_id`),
  KEY `recruitment_costs_created_by_foreign` (`created_by`),
  CONSTRAINT `recruitment_costs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `recruitment_costs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `recruitment_costs_job_requisition_id_foreign` FOREIGN KEY (`job_requisition_id`) REFERENCES `job_requisitions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tambah 4 modul baru ke tabel permissions (PermissionCatalogSeeder juga bisa
-- dijalankan ulang via `php artisan db:seed --class=PermissionCatalogSeeder`,
-- idempotent) — kalau server produksi tidak boleh jalankan artisan, insert manual:
INSERT IGNORE INTO permissions (name, guard_name, created_at, updated_at) VALUES
  ('shift.view','web',NOW(),NOW()), ('shift.create','web',NOW(),NOW()), ('shift.edit','web',NOW(),NOW()), ('shift.delete','web',NOW(),NOW()),
  ('offboarding.view','web',NOW(),NOW()), ('offboarding.create','web',NOW(),NOW()), ('offboarding.edit','web',NOW(),NOW()), ('offboarding.delete','web',NOW(),NOW()),
  ('announcement.view','web',NOW(),NOW()), ('announcement.create','web',NOW(),NOW()), ('announcement.edit','web',NOW(),NOW()), ('announcement.delete','web',NOW(),NOW()),
  ('survey.view','web',NOW(),NOW()), ('survey.create','web',NOW(),NOW()), ('survey.edit','web',NOW(),NOW()), ('survey.delete','web',NOW(),NOW());
-- Lalu assign ke role admin/hr_manager/hr_admin/super_admin lewat halaman
-- Role & Hak Akses (atau `php artisan db:seed --class=PermissionCatalogSeeder`).

-- INSERT INTO migrations (migration, batch) VALUES
--   ('2026_08_29_180400_create_announcements_tables', <batch>),
--   ('2026_08_29_180500_create_survey_tables', <batch>),
--   ('2026_08_29_180600_create_recruitment_costs_table', <batch>);


-- =============================================================================
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
-- TAHAP 24 — candidate-database-ats-manual.sql
-- =============================================================================
-- =============================================================================
-- Modul: Candidate Database (ATS) — expected salary, hasil assessment, dan CV
--        terstruktur kandidat (pendidikan / pengalaman / skill / sertifikasi).
--
-- Setara dengan migration:
--   2026_08_31_110000_create_candidate_profile_tables
--
-- WAJIB backup dulu. Jalankan SETELAH recruitment-manual.sql (butuh `candidates`).
-- Saat kandidat dikonversi jadi Employee, data pendidikan/pengalaman/skill ikut
-- disalin ke employee_educations / employee_work_experiences / employee_skills
-- (logika di CandidateController::carryOverProfile()).
-- =============================================================================

ALTER TABLE `candidates`
  ADD COLUMN `expected_salary` bigint(20) DEFAULT NULL AFTER `source`,
  ADD COLUMN `assessment_result` varchar(20) DEFAULT NULL AFTER `mcu_result`,   -- pass/hold/fail
  ADD COLUMN `assessment_score` decimal(5,2) DEFAULT NULL AFTER `assessment_result`,
  ADD COLUMN `assessment_notes` text DEFAULT NULL AFTER `assessment_score`;

CREATE TABLE `candidate_educations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `education_level` varchar(50) DEFAULT NULL,
  `major` varchar(150) DEFAULT NULL,
  `institution` varchar(200) DEFAULT NULL,
  `graduation_year` year(4) DEFAULT NULL,
  `gpa` decimal(4,2) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `candidate_educations_candidate_id_foreign` (`candidate_id`),
  CONSTRAINT `candidate_educations_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidate_experiences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `job_title` varchar(150) DEFAULT NULL,
  `company_city` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `last_salary` bigint(20) DEFAULT NULL,
  `job_description` text DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `candidate_experiences_candidate_id_foreign` (`candidate_id`),
  CONSTRAINT `candidate_experiences_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidate_skills` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `proficiency` enum('basic','intermediate','advanced','expert') DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `candidate_skills_candidate_id_foreign` (`candidate_id`),
  CONSTRAINT `candidate_skills_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidate_certifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `name` varchar(200) NOT NULL,
  `issuer` varchar(150) DEFAULT NULL,
  `issued_date` date DEFAULT NULL,
  `expires_date` date DEFAULT NULL,
  `credential_id` varchar(100) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `candidate_certifications_candidate_id_foreign` (`candidate_id`),
  CONSTRAINT `candidate_certifications_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_08_31_110000_create_candidate_profile_tables', <BATCH>);
-- =============================================================================


-- =============================================================================
-- TAHAP 25 — preemployment-manual.sql
-- =============================================================================
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

-- ###########################################################################
-- >>> SETELAH SEMUA SQL: jalankan seeder (butuh php artisan):
-- >>>   php artisan db:seed --class=RegionSeeder
-- >>>   php artisan db:seed --class=RegionDistrictSeeder
-- >>>   php artisan db:seed --class=ApprovalWorkflowSeeder
-- >>>   php artisan db:seed --class=CompetencySeeder
-- >>>   php artisan db:seed --class=LeavePolicySeeder
-- >>>   php artisan db:seed --class=OffboardingChecklistItemSeeder
-- >>>   php artisan db:seed --class=PreEmploymentChecklistItemSeeder   (skip kalau TAHAP 25 sudah insert)
-- >>>   php artisan db:seed --class=PermissionCatalogSeeder   (TERAKHIR)
-- >>> Lalu: cron `* * * * * php artisan schedule:run`, `php artisan up`.
-- ###########################################################################


SET FOREIGN_KEY_CHECKS = 1;
