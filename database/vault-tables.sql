-- Modul: GA Barcode Dokumen Brankas
-- Jalankan berurutan (categories -> vaults -> documents -> transactions) karena ada foreign key.

CREATE TABLE `vault_document_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vault_document_categories_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Berangkas fisik. QR/barcode discan per berangkas (bukan per dokumen);
-- satu berangkas berisi banyak dokumen.
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

CREATE TABLE `vault_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `vault_id` bigint(20) unsigned DEFAULT NULL,
  `barcode` varchar(30) NOT NULL,
  `detail` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vault_documents_barcode_unique` (`barcode`),
  KEY `vault_documents_category_id_foreign` (`category_id`),
  KEY `vault_documents_vault_id_foreign` (`vault_id`),
  CONSTRAINT `vault_documents_category_id_foreign`
    FOREIGN KEY (`category_id`) REFERENCES `vault_document_categories` (`id`),
  CONSTRAINT `vault_documents_vault_id_foreign`
    FOREIGN KEY (`vault_id`) REFERENCES `vaults` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `vault_document_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `document_id` bigint(20) unsigned NOT NULL,
  `status` enum('pengambilan','pengembalian') NOT NULL,
  `transaction_date` date NOT NULL,
  `nama` varchar(100) NOT NULL,
  `keperluan` enum('pinjam','jual','pengembalian_jaminan','pengambilan_dokumen') NOT NULL,
  `photo_handover` varchar(255) NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vault_document_transactions_document_id_foreign` (`document_id`),
  KEY `vault_document_transactions_created_by_foreign` (`created_by`),
  CONSTRAINT `vault_document_transactions_created_by_foreign`
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vault_document_transactions_document_id_foreign`
    FOREIGN KEY (`document_id`) REFERENCES `vault_documents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Opsional: daftarkan ke tabel migrations Laravel supaya `php artisan migrate`
-- tidak mencoba membuat ulang tabel ini. Cek dulu: SELECT MAX(batch) FROM migrations;
-- lalu ganti angka batch di bawah ini.
--
-- INSERT INTO `migrations` (`migration`, `batch`) VALUES
-- ('2026_07_12_090000_create_vault_document_categories_table', 24),
-- ('2026_07_12_090001_create_vault_documents_table', 24),
-- ('2026_07_12_090002_create_vault_document_transactions_table', 24),
-- ('2026_07_21_100000_create_vaults_table', 25),
-- ('2026_07_21_100001_add_vault_id_to_vault_documents_table', 25);
