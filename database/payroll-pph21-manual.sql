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
