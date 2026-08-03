-- Modul: Master Data Karyawan (Company, Department, Position)
-- + field profil personal karyawan + branch
-- Setara dengan migration:
--   2026_07_02_200000_create_companies_table
--   2026_07_02_200001_add_company_id_to_employees_table
--   2026_07_13_090000_create_departments_table
--   2026_07_13_090001_create_positions_table
--   2026_07_13_090002_add_personal_fields_to_employees_table
--   2026_07_13_090003_migrate_department_position_to_master_tables
--   2026_07_15_090000_add_branch_to_employees_table
--
-- WAJIB backup database dulu sebelum jalankan. Jalankan berurutan dari atas.
-- Cek dulu struktur employees saat ini: DESCRIBE employees;
-- Script ini asumsikan kolom `department` dan `position` di employees
-- MASIH varchar teks bebas (belum pernah diubah).

-- 1) Tabel companies
CREATE TABLE `companies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `short_name` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `npwp` varchar(30) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `companies_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `companies` (`code`, `name`, `short_name`, `is_active`, `created_at`, `updated_at`) VALUES
('proenergi', 'PT. Pro Energi',        'Pro Energi', 1, NOW(), NOW()),
('tds',       'PT. Tridaya Selaras',   'TDS',        1, NOW(), NOW()),
('pfr',       'PT. Pinnafore Staraya', 'PFR',        1, NOW(), NOW());

-- 2) Tambah company_id ke employees, semua karyawan lama ditandai Pro Energi
ALTER TABLE `employees`
  ADD COLUMN `company_id` bigint(20) unsigned DEFAULT NULL AFTER `id`,
  ADD KEY `employees_company_id_foreign` (`company_id`),
  ADD CONSTRAINT `employees_company_id_foreign`
    FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL;

UPDATE `employees`
SET `company_id` = (SELECT `id` FROM `companies` WHERE `code` = 'proenergi' LIMIT 1);

-- 3) Kolom branch (cabang)
ALTER TABLE `employees`
  ADD COLUMN `branch` varchar(50) DEFAULT 'HO' AFTER `company_id`;

-- 4) Tabel departments
CREATE TABLE `departments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_code_unique` (`code`),
  KEY `departments_company_id_foreign` (`company_id`),
  CONSTRAINT `departments_company_id_foreign`
    FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5) Tabel positions
CREATE TABLE `positions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `positions_code_unique` (`code`),
  KEY `positions_company_id_foreign` (`company_id`),
  KEY `positions_department_id_foreign` (`department_id`),
  CONSTRAINT `positions_company_id_foreign`
    FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `positions_department_id_foreign`
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6) Field profil personal karyawan (KTP, NPWP, alamat, kontak darurat, dst)
ALTER TABLE `employees`
  ADD COLUMN `photo` varchar(255) DEFAULT NULL AFTER `name`,
  ADD COLUMN `gender` enum('L','P') DEFAULT NULL AFTER `photo`,
  ADD COLUMN `birth_place` varchar(100) DEFAULT NULL AFTER `gender`,
  ADD COLUMN `birth_date` date DEFAULT NULL AFTER `birth_place`,
  ADD COLUMN `ktp_number` varchar(30) DEFAULT NULL AFTER `birth_date`,
  ADD COLUMN `npwp_number` varchar(30) DEFAULT NULL AFTER `ktp_number`,
  ADD COLUMN `npwp_city` varchar(100) DEFAULT NULL AFTER `npwp_number`,
  ADD COLUMN `npwp_date` date DEFAULT NULL AFTER `npwp_city`,
  ADD COLUMN `marital_status` enum('belum_kawin','kawin','cerai_hidup','cerai_mati') DEFAULT NULL AFTER `npwp_date`,
  ADD COLUMN `religion` varchar(30) DEFAULT NULL AFTER `marital_status`,
  ADD COLUMN `blood_type` enum('A','B','AB','O') DEFAULT NULL AFTER `religion`,
  ADD COLUMN `employee_type` enum('local','expat') NOT NULL DEFAULT 'local' AFTER `blood_type`,
  ADD COLUMN `finger_id` varchar(30) DEFAULT NULL AFTER `employee_type`,
  ADD COLUMN `email` varchar(150) DEFAULT NULL AFTER `finger_id`,
  ADD COLUMN `phone` varchar(30) DEFAULT NULL AFTER `email`,
  ADD COLUMN `home_phone` varchar(30) DEFAULT NULL AFTER `phone`,
  ADD COLUMN `domicile_address` text DEFAULT NULL AFTER `home_phone`,
  ADD COLUMN `domicile_city` varchar(100) DEFAULT NULL AFTER `domicile_address`,
  ADD COLUMN `domicile_district` varchar(100) DEFAULT NULL AFTER `domicile_city`,
  ADD COLUMN `domicile_subdistrict` varchar(100) DEFAULT NULL AFTER `domicile_district`,
  ADD COLUMN `ktp_address` text DEFAULT NULL AFTER `domicile_subdistrict`,
  ADD COLUMN `ktp_city` varchar(100) DEFAULT NULL AFTER `ktp_address`,
  ADD COLUMN `ktp_district` varchar(100) DEFAULT NULL AFTER `ktp_city`,
  ADD COLUMN `ktp_subdistrict` varchar(100) DEFAULT NULL AFTER `ktp_district`,
  ADD COLUMN `emergency_contact_name` varchar(150) DEFAULT NULL AFTER `ktp_subdistrict`,
  ADD COLUMN `emergency_contact_relation` varchar(100) DEFAULT NULL AFTER `emergency_contact_name`,
  ADD COLUMN `emergency_contact_phone` varchar(30) DEFAULT NULL AFTER `emergency_contact_relation`;

-- 7) Pindahkan department & position dari teks bebas ke master data.
--    Kode department/position dibuat otomatis (D1, D2, ... / P1, P2, ...)
--    supaya pasti unik — boleh dirapikan manual lewat menu Departemen/Jabatan
--    setelah ini selesai.

ALTER TABLE `employees`
  ADD COLUMN `department_id` bigint(20) unsigned DEFAULT NULL AFTER `department`,
  ADD COLUMN `position_id` bigint(20) unsigned DEFAULT NULL AFTER `position`,
  ADD KEY `employees_department_id_foreign` (`department_id`),
  ADD KEY `employees_position_id_foreign` (`position_id`),
  ADD CONSTRAINT `employees_department_id_foreign`
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_position_id_foreign`
    FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL;

-- Departemen: 1 baris per nilai unik employees.department yang ada
SET @d := 0;
INSERT INTO `departments` (`code`, `name`, `is_active`, `created_at`, `updated_at`)
SELECT CONCAT('D', (@d := @d + 1)), `department`, 1, NOW(), NOW()
FROM (SELECT DISTINCT `department` FROM `employees` WHERE `department` IS NOT NULL AND `department` != '') AS x
ORDER BY `department`;

UPDATE `employees` e
JOIN `departments` d ON d.`name` = e.`department`
SET e.`department_id` = d.`id`;

-- Jabatan: 1 baris per nilai unik employees.position yang ada
SET @p := 0;
INSERT INTO `positions` (`code`, `name`, `is_active`, `created_at`, `updated_at`)
SELECT CONCAT('P', (@p := @p + 1)), `position`, 1, NOW(), NOW()
FROM (SELECT DISTINCT `position` FROM `employees` WHERE `position` IS NOT NULL AND `position` != '') AS x
ORDER BY `position`;

UPDATE `employees` e
JOIN `positions` p ON p.`name` = e.`position`
SET e.`position_id` = p.`id`;

-- CEK dulu sebelum lanjut — pastikan department_id/position_id sudah
-- terisi benar untuk semua baris:
--   SELECT id, name, department, department_id, position, position_id FROM employees;
-- Baru setelah itu hapus kolom teks lama:

ALTER TABLE `employees`
  DROP COLUMN `department`,
  DROP COLUMN `position`;

-- ============================================================
-- Setelah semua di atas selesai:
--   1. Buka menu "Data Karyawan > Departemen" & "Jabatan" untuk rapikan
--      nama/kode hasil migrasi otomatis (kode D1/P1 dst boleh diganti).
--   2. Buka menu "Data Karyawan > Job Levels" sudah ada dari sebelumnya,
--      tidak berubah oleh script ini.
--   3. Cek beberapa data karyawan lewat form Edit untuk pastikan
--      company/branch/department/position sudah benar.
-- ============================================================

-- Opsional: daftarkan ke tabel migrations Laravel supaya `php artisan migrate`
-- tidak mencoba menjalankan ulang perubahan ini nanti. Cek dulu:
--   SELECT MAX(batch) FROM migrations;
-- lalu ganti angka <BATCH> di bawah dengan (hasil query di atas + 1).
--
-- INSERT INTO `migrations` (`migration`, `batch`) VALUES
-- ('2026_07_02_200000_create_companies_table', <BATCH>),
-- ('2026_07_02_200001_add_company_id_to_employees_table', <BATCH>),
-- ('2026_07_13_090000_create_departments_table', <BATCH>),
-- ('2026_07_13_090001_create_positions_table', <BATCH>),
-- ('2026_07_13_090002_add_personal_fields_to_employees_table', <BATCH>),
-- ('2026_07_13_090003_migrate_department_position_to_master_tables', <BATCH>),
-- ('2026_07_15_090000_add_branch_to_employees_table', <BATCH>);
