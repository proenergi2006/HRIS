-- Dipecah dari database/deploy-all-produksi.sql — jalankan HANYA tahap yang belum ada di prod.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

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

SET FOREIGN_KEY_CHECKS = 1;
