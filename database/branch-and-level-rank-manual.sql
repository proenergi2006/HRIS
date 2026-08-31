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
