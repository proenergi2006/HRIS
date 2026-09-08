-- =============================================================================
-- Isi Jabatan dasar utk Divisi BOD/Commercial/Logistik milik PT. Tridaya
-- Selaras (TDS) — sebelumnya kosong (0 Jabatan), jadi bagan organisasi tidak
-- bisa menampilkan "Commercial/Logistik lapor ke BOD" karena tidak ada
-- Jabatan sama sekali utk direkrut/ditempatkan.
--
-- Setara dengan migration:
--   2026_09_08_110000_add_tds_bod_commercial_logistik_positions
--
-- WAJIB backup dulu. Sesuaikan dulu nama Company/Departemen/Level di server
-- kalau beda dari default ('PT. Tridaya Selaras', 'BOD', 'COMMERCIAL TDS',
-- 'LOGISTIK TDS', 'Direksi', 'Manager', 'SPV', 'Senior Staff', 'Staff') —
-- query di bawah pakai subquery by name, BUKAN id hardcode, supaya aman
-- dijalankan di server walau id-nya beda dari lokal.
-- =============================================================================

SET @company_id = (SELECT id FROM companies WHERE name = 'PT. Tridaya Selaras' LIMIT 1);
SET @dept_bod = (SELECT id FROM departments WHERE company_id = @company_id AND name = 'BOD' LIMIT 1);
SET @dept_commercial = (SELECT id FROM departments WHERE company_id = @company_id AND name = 'COMMERCIAL TDS' LIMIT 1);
SET @dept_logistik = (SELECT id FROM departments WHERE company_id = @company_id AND name = 'LOGISTIK TDS' LIMIT 1);

SET @lvl_direksi = (SELECT id FROM levels WHERE name = 'Direksi' LIMIT 1);
SET @lvl_manager = (SELECT id FROM levels WHERE name = 'Manager' LIMIT 1);
SET @lvl_spv = (SELECT id FROM levels WHERE name = 'SPV' LIMIT 1);
SET @lvl_senior_staff = (SELECT id FROM levels WHERE name = 'Senior Staff' LIMIT 1);
SET @lvl_staff = (SELECT id FROM levels WHERE name = 'Staff' LIMIT 1);

INSERT INTO `positions` (`company_id`, `department_id`, `level_id`, `code`, `name`, `tunjangan_jabatan`, `is_active`, `created_at`, `updated_at`)
SELECT * FROM (SELECT
    @company_id, @dept_bod, @lvl_direksi, 'BOD-DIR', 'Direktur Utama', 20000000, 1, NOW(), NOW()
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `positions` WHERE `company_id` = @company_id AND `code` = 'BOD-DIR');

INSERT INTO `positions` (`company_id`, `department_id`, `level_id`, `code`, `name`, `tunjangan_jabatan`, `is_active`, `created_at`, `updated_at`)
SELECT * FROM (SELECT @company_id, @dept_commercial, @lvl_manager, 'COMM-TDS-MGR', 'Manager Commercial TDS', 5000000, 1, NOW(), NOW()) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `positions` WHERE `company_id` = @company_id AND `code` = 'COMM-TDS-MGR');

INSERT INTO `positions` (`company_id`, `department_id`, `level_id`, `code`, `name`, `tunjangan_jabatan`, `is_active`, `created_at`, `updated_at`)
SELECT * FROM (SELECT @company_id, @dept_commercial, @lvl_spv, 'COMM-TDS-SPV', 'SPV Commercial TDS', 2500000, 1, NOW(), NOW()) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `positions` WHERE `company_id` = @company_id AND `code` = 'COMM-TDS-SPV');

INSERT INTO `positions` (`company_id`, `department_id`, `level_id`, `code`, `name`, `tunjangan_jabatan`, `is_active`, `created_at`, `updated_at`)
SELECT * FROM (SELECT @company_id, @dept_commercial, @lvl_senior_staff, 'COMM-TDS-SST', 'Senior Staff Commercial TDS', 1200000, 1, NOW(), NOW()) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `positions` WHERE `company_id` = @company_id AND `code` = 'COMM-TDS-SST');

INSERT INTO `positions` (`company_id`, `department_id`, `level_id`, `code`, `name`, `tunjangan_jabatan`, `is_active`, `created_at`, `updated_at`)
SELECT * FROM (SELECT @company_id, @dept_commercial, @lvl_staff, 'COMM-TDS-STF', 'Staff Commercial TDS', 500000, 1, NOW(), NOW()) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `positions` WHERE `company_id` = @company_id AND `code` = 'COMM-TDS-STF');

INSERT INTO `positions` (`company_id`, `department_id`, `level_id`, `code`, `name`, `tunjangan_jabatan`, `is_active`, `created_at`, `updated_at`)
SELECT * FROM (SELECT @company_id, @dept_logistik, @lvl_manager, 'LOG-TDS-MGR', 'Manager Logistik TDS', 5000000, 1, NOW(), NOW()) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `positions` WHERE `company_id` = @company_id AND `code` = 'LOG-TDS-MGR');

INSERT INTO `positions` (`company_id`, `department_id`, `level_id`, `code`, `name`, `tunjangan_jabatan`, `is_active`, `created_at`, `updated_at`)
SELECT * FROM (SELECT @company_id, @dept_logistik, @lvl_spv, 'LOG-TDS-SPV', 'SPV Logistik TDS', 2500000, 1, NOW(), NOW()) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `positions` WHERE `company_id` = @company_id AND `code` = 'LOG-TDS-SPV');

INSERT INTO `positions` (`company_id`, `department_id`, `level_id`, `code`, `name`, `tunjangan_jabatan`, `is_active`, `created_at`, `updated_at`)
SELECT * FROM (SELECT @company_id, @dept_logistik, @lvl_senior_staff, 'LOG-TDS-SST', 'Senior Staff Logistik TDS', 1200000, 1, NOW(), NOW()) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `positions` WHERE `company_id` = @company_id AND `code` = 'LOG-TDS-SST');

INSERT INTO `positions` (`company_id`, `department_id`, `level_id`, `code`, `name`, `tunjangan_jabatan`, `is_active`, `created_at`, `updated_at`)
SELECT * FROM (SELECT @company_id, @dept_logistik, @lvl_staff, 'LOG-TDS-STF', 'Staff Logistik TDS', 500000, 1, NOW(), NOW()) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `positions` WHERE `company_id` = @company_id AND `code` = 'LOG-TDS-STF');

-- Manager Commercial TDS & Manager Logistik TDS lapor ke Direktur Utama
-- (dipakai halaman Jabatan; garis di bagan organisasi sendiri ikut
-- Employee.manager_id saat masing2 posisi ini direkrut/diisi).
UPDATE `positions`
SET `reports_to_position_id` = (SELECT id FROM `positions` WHERE `company_id` = @company_id AND `code` = 'BOD-DIR')
WHERE `company_id` = @company_id AND `code` IN ('COMM-TDS-MGR', 'LOG-TDS-MGR');

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_09_08_110000_add_tds_bod_commercial_logistik_positions', <BATCH>);
-- =============================================================================
