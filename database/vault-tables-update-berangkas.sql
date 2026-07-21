-- Update Modul: GA Barcode Dokumen Brankas — QR per Berangkas
-- Asumsi: tabel vault_document_categories, vault_documents, vault_document_transactions
-- SUDAH ADA di database (dari database/vault-tables.sql yang lama).
-- Jalankan berurutan.

-- 1) Tabel berangkas fisik. QR/barcode sekarang discan per berangkas
--    (bukan per dokumen); satu berangkas berisi banyak dokumen.
CREATE TABLE `vaults` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `barcode` varchar(30) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vaults_name_unique` (`name`),
  UNIQUE KEY `vaults_barcode_unique` (`barcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Tambah kolom vault_id ke vault_documents (nullable dulu supaya aman
--    untuk baris dokumen yang sudah ada; wajib diisi lewat form admin
--    setelah berangkas dibuat).
ALTER TABLE `vault_documents`
  ADD COLUMN `vault_id` bigint(20) unsigned DEFAULT NULL AFTER `category_id`,
  ADD KEY `vault_documents_vault_id_foreign` (`vault_id`),
  ADD CONSTRAINT `vault_documents_vault_id_foreign`
    FOREIGN KEY (`vault_id`) REFERENCES `vaults` (`id`) ON DELETE SET NULL;

-- Setelah dijalankan:
--   1. Buka menu "Berangkas" di admin, buat berangkas yang sesuai (misal
--      "Berangkas 1"). Barcode otomatis ter-generate.
--   2. Edit tiap dokumen yang sudah ada dan pilih berangkas-nya, karena
--      kolom vault_id masih NULL untuk data lama.

-- Opsional: daftarkan ke tabel migrations Laravel supaya `php artisan migrate`
-- tidak mencoba menjalankan ulang perubahan ini. Cek dulu: SELECT MAX(batch) FROM migrations;
-- lalu ganti angka batch di bawah ini.
--
-- INSERT INTO `migrations` (`migration`, `batch`) VALUES
-- ('2026_07_21_100000_create_vaults_table', 25),
-- ('2026_07_21_100001_add_vault_id_to_vault_documents_table', 25);
