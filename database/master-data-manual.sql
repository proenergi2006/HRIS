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
