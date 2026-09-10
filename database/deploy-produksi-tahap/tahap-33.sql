-- Dipecah dari database/deploy-all-produksi.sql — jalankan HANYA tahap yang belum ada di prod.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- TAHAP 33 — fix-level-rank-manual.sql
-- =============================================================================
-- =============================================================================
-- Perbaikan data: hierarki levels.rank supaya Bagan Organisasi urut benar.
-- Org chart sort tier pakai `rank` ASC (1 = paling senior, lihat placeholder
-- form Edit Level). Data awal Direksi/Manager/SPV/Admin semua rank=99 (seri) --
-- akibatnya CEO (Direksi) tidak tampil lebih tinggi dari Manager/SPV di bagan.
--
-- Setara dengan migration:
--   2026_09_08_100000_fix_level_rank_hierarchy
--
-- Aman dijalankan berkali-kali (idempoten). Cocokkan dulu nama Level di server
-- (kalau namanya beda dari default, sesuaikan WHERE name=... di bawah).
-- =============================================================================

UPDATE `levels` SET `rank` = 1 WHERE `name` = 'Direksi';
UPDATE `levels` SET `rank` = 2 WHERE `name` = 'Manager';
UPDATE `levels` SET `rank` = 3 WHERE `name` = 'SPV';
UPDATE `levels` SET `rank` = 4 WHERE `name` = 'Senior Staff';
UPDATE `levels` SET `rank` = 5 WHERE `name` = 'Staff';
UPDATE `levels` SET `rank` = 6 WHERE `name` = 'Admin';

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_09_08_100000_fix_level_rank_hierarchy', <BATCH>);
-- =============================================================================


-- =============================================================================

SET FOREIGN_KEY_CHECKS = 1;
