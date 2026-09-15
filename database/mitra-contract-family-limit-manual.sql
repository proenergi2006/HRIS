-- =============================================================================
-- Perbaikan 15 Sep 2026: nama PT, enum Mitra di Kontrak Kerja, Level Mitra/Intern
-- =============================================================================
-- WAJIB backup dulu.
-- =============================================================================

-- -----------------------------------------------------------------------------
-- 1) Perbaiki nama perusahaan yang salah ketik di master (data saja, tanpa
--    ubah kode/relasi — kode 'pfr' tetap, cuma kolom `name` yang diperbaiki).
--    Cek dulu barisnya:
--      SELECT id, code, name FROM companies WHERE code = 'pfr';
-- -----------------------------------------------------------------------------
UPDATE `companies` SET `name` = 'PT. Pinnafore Staraya'
WHERE `name` = 'PT. Pinna Foresta Raya';

-- -----------------------------------------------------------------------------
-- 2) Tambah 'mitra' ke enum employee_contracts.contract_type — supaya "Mitra"
--    bisa dipilih di tab Kontrak Kerja karyawan (sebelumnya cuma ada di Tipe
--    Karyawan / employee_types). Setara migration:
--      2026_09_15_100000_add_mitra_to_employee_contracts_contract_type
-- -----------------------------------------------------------------------------
ALTER TABLE `employee_contracts` MODIFY COLUMN `contract_type`
  ENUM('pkwtt','pkwt','probation','magang','harian','mitra','other') NOT NULL DEFAULT 'pkwt';

-- -----------------------------------------------------------------------------
-- 3) Level Jabatan "Mitra" & "Intern" — OPSIONAL, data saja (tidak perlu SQL
--    sama sekali kalau mau: menu Data Karyawan > Level Jabatan > Tambah Level
--    sudah bisa dipakai langsung). Rank 90/91 = di bawah Admin(6), tidak
--    ikut hierarki Direksi..Admin di Bagan Organisasi.
-- -----------------------------------------------------------------------------
INSERT INTO `levels` (`name`, `description`, `rank`, `created_at`, `updated_at`)
SELECT 'Mitra', 'Non-jenjang — mitra kerja', 90, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `levels` WHERE `name` = 'Mitra');

INSERT INTO `levels` (`name`, `description`, `rank`, `created_at`, `updated_at`)
SELECT 'Intern', 'Non-jenjang — magang/intern', 91, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `levels` WHERE `name` = 'Intern');

-- =============================================================================
-- Opsional daftarkan migration #2 ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_09_15_100000_add_mitra_to_employee_contracts_contract_type', <BATCH>);
-- =============================================================================
