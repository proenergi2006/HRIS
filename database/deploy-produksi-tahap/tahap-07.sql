-- Dipecah dari database/deploy-all-produksi.sql — jalankan HANYA tahap yang belum ada di prod.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- TAHAP 7 — payroll-pph21-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: PPh21 otomatis (TER) + tarif BPJS (PRD HRIS v1.2 Bab 9 — Payroll & Compensation)
--
-- Setara dengan migration 2026_08_28_1006xx.
--
-- ‼️ PENTING: batas penghasilan & persentase TER A/B/C di bawah adalah rekonstruksi
-- tabel resmi Lampiran PMK 168/2023 dari pengetahuan training model (31 Agu 2026,
-- BUKAN ditarik langsung dari dokumen PDF resmi — sesi kerja saat itu tak punya akses
-- internet). Kategori A relatif sering dikutip publik jadi keyakinan lebih tinggi;
-- B & C punya risiko selisih lebih besar di titik batas tengah. WAJIB dicocokkan
-- Finance/Tax ke Lampiran resmi (atau kalkulator TER pajak.go.id) sebelum dipakai
-- penggajian sungguhan — bisa diedit langsung lewat menu Master Data > Tarif PPh21
-- (TER), tidak perlu developer/migrasi ulang. Lihat juga
-- database/pph21-ter-rate-correction-manual.sql (kalau instalasi lama masih pakai
-- kurva ilustratif versi sebelumnya, jalankan file koreksi itu, bukan INSERT di bawah).
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
(1,0,5400000,0.00,NOW(),NOW()),
(1,5400000,5650000,0.25,NOW(),NOW()),
(1,5650000,5950000,0.50,NOW(),NOW()),
(1,5950000,6300000,0.75,NOW(),NOW()),
(1,6300000,6750000,1.00,NOW(),NOW()),
(1,6750000,7500000,1.25,NOW(),NOW()),
(1,7500000,8550000,1.50,NOW(),NOW()),
(1,8550000,9650000,1.75,NOW(),NOW()),
(1,9650000,10050000,2.00,NOW(),NOW()),
(1,10050000,10350000,2.25,NOW(),NOW()),
(1,10350000,10700000,2.50,NOW(),NOW()),
(1,10700000,11050000,3.00,NOW(),NOW()),
(1,11050000,11600000,3.50,NOW(),NOW()),
(1,11600000,12500000,4.00,NOW(),NOW()),
(1,12500000,13750000,5.00,NOW(),NOW()),
(1,13750000,15100000,6.00,NOW(),NOW()),
(1,15100000,16950000,7.00,NOW(),NOW()),
(1,16950000,19750000,8.00,NOW(),NOW()),
(1,19750000,24150000,9.00,NOW(),NOW()),
(1,24150000,26450000,10.00,NOW(),NOW()),
(1,26450000,28000000,11.00,NOW(),NOW()),
(1,28000000,30050000,12.00,NOW(),NOW()),
(1,30050000,32400000,13.00,NOW(),NOW()),
(1,32400000,35400000,14.00,NOW(),NOW()),
(1,35400000,39100000,15.00,NOW(),NOW()),
(1,39100000,43850000,16.00,NOW(),NOW()),
(1,43850000,47800000,17.00,NOW(),NOW()),
(1,47800000,51400000,18.00,NOW(),NOW()),
(1,51400000,56300000,19.00,NOW(),NOW()),
(1,56300000,62200000,20.00,NOW(),NOW()),
(1,62200000,68600000,21.00,NOW(),NOW()),
(1,68600000,77500000,22.00,NOW(),NOW()),
(1,77500000,89000000,23.00,NOW(),NOW()),
(1,89000000,103000000,24.00,NOW(),NOW()),
(1,103000000,125000000,25.00,NOW(),NOW()),
(1,125000000,157000000,26.00,NOW(),NOW()),
(1,157000000,206000000,27.00,NOW(),NOW()),
(1,206000000,337000000,28.00,NOW(),NOW()),
(1,337000000,454000000,29.00,NOW(),NOW()),
(1,454000000,550000000,30.00,NOW(),NOW()),
(1,550000000,695000000,31.00,NOW(),NOW()),
(1,695000000,910000000,32.00,NOW(),NOW()),
(1,910000000,1400000000,33.00,NOW(),NOW()),
(1,1400000000,NULL,34.00,NOW(),NOW()),
(2,0,6200000,0.00,NOW(),NOW()),
(2,6200000,6500000,0.25,NOW(),NOW()),
(2,6500000,6850000,0.50,NOW(),NOW()),
(2,6850000,7300000,0.75,NOW(),NOW()),
(2,7300000,9200000,1.00,NOW(),NOW()),
(2,9200000,10750000,1.50,NOW(),NOW()),
(2,10750000,11250000,2.00,NOW(),NOW()),
(2,11250000,11600000,2.50,NOW(),NOW()),
(2,11600000,12600000,3.00,NOW(),NOW()),
(2,12600000,13600000,4.00,NOW(),NOW()),
(2,13600000,14950000,5.00,NOW(),NOW()),
(2,14950000,16400000,6.00,NOW(),NOW()),
(2,16400000,18450000,7.00,NOW(),NOW()),
(2,18450000,21850000,8.00,NOW(),NOW()),
(2,21850000,26000000,9.00,NOW(),NOW()),
(2,26000000,27700000,10.00,NOW(),NOW()),
(2,27700000,29350000,11.00,NOW(),NOW()),
(2,29350000,31450000,12.00,NOW(),NOW()),
(2,31450000,33950000,13.00,NOW(),NOW()),
(2,33950000,37100000,14.00,NOW(),NOW()),
(2,37100000,41100000,15.00,NOW(),NOW()),
(2,41100000,45800000,16.00,NOW(),NOW()),
(2,45800000,49500000,17.00,NOW(),NOW()),
(2,49500000,53800000,18.00,NOW(),NOW()),
(2,53800000,58500000,19.00,NOW(),NOW()),
(2,58500000,64000000,20.00,NOW(),NOW()),
(2,64000000,71000000,21.00,NOW(),NOW()),
(2,71000000,80000000,22.00,NOW(),NOW()),
(2,80000000,93000000,23.00,NOW(),NOW()),
(2,93000000,109000000,24.00,NOW(),NOW()),
(2,109000000,129000000,25.00,NOW(),NOW()),
(2,129000000,163000000,26.00,NOW(),NOW()),
(2,163000000,211000000,27.00,NOW(),NOW()),
(2,211000000,374000000,28.00,NOW(),NOW()),
(2,374000000,459000000,29.00,NOW(),NOW()),
(2,459000000,555000000,30.00,NOW(),NOW()),
(2,555000000,704000000,31.00,NOW(),NOW()),
(2,704000000,957000000,32.00,NOW(),NOW()),
(2,957000000,1405000000,33.00,NOW(),NOW()),
(2,1405000000,NULL,34.00,NOW(),NOW()),
(3,0,6600000,0.00,NOW(),NOW()),
(3,6600000,6950000,0.25,NOW(),NOW()),
(3,6950000,7350000,0.50,NOW(),NOW()),
(3,7350000,7800000,0.75,NOW(),NOW()),
(3,7800000,8850000,1.00,NOW(),NOW()),
(3,8850000,9800000,1.25,NOW(),NOW()),
(3,9800000,10950000,1.50,NOW(),NOW()),
(3,10950000,11200000,1.75,NOW(),NOW()),
(3,11200000,12050000,2.00,NOW(),NOW()),
(3,12050000,12950000,3.00,NOW(),NOW()),
(3,12950000,14150000,4.00,NOW(),NOW()),
(3,14150000,15550000,5.00,NOW(),NOW()),
(3,15550000,17050000,6.00,NOW(),NOW()),
(3,17050000,19500000,7.00,NOW(),NOW()),
(3,19500000,22700000,8.00,NOW(),NOW()),
(3,22700000,26600000,9.00,NOW(),NOW()),
(3,26600000,28100000,10.00,NOW(),NOW()),
(3,28100000,30100000,11.00,NOW(),NOW()),
(3,30100000,32600000,12.00,NOW(),NOW()),
(3,32600000,35400000,13.00,NOW(),NOW()),
(3,35400000,38900000,14.00,NOW(),NOW()),
(3,38900000,43000000,15.00,NOW(),NOW()),
(3,43000000,47400000,16.00,NOW(),NOW()),
(3,47400000,51200000,17.00,NOW(),NOW()),
(3,51200000,55800000,18.00,NOW(),NOW()),
(3,55800000,60400000,19.00,NOW(),NOW()),
(3,60400000,66700000,20.00,NOW(),NOW()),
(3,66700000,74500000,21.00,NOW(),NOW()),
(3,74500000,83200000,22.00,NOW(),NOW()),
(3,83200000,95600000,23.00,NOW(),NOW()),
(3,95600000,110000000,24.00,NOW(),NOW()),
(3,110000000,134000000,25.00,NOW(),NOW()),
(3,134000000,169000000,26.00,NOW(),NOW()),
(3,169000000,221000000,27.00,NOW(),NOW()),
(3,221000000,390000000,28.00,NOW(),NOW()),
(3,390000000,463000000,29.00,NOW(),NOW()),
(3,463000000,561000000,30.00,NOW(),NOW()),
(3,561000000,709000000,31.00,NOW(),NOW()),
(3,709000000,965000000,32.00,NOW(),NOW()),
(3,965000000,1419000000,33.00,NOW(),NOW()),
(3,1419000000,NULL,34.00,NOW(),NOW());

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

SET FOREIGN_KEY_CHECKS = 1;
