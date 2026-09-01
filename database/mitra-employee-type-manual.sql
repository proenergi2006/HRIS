-- =============================================================================
-- Tambah tipe karyawan "Mitra" ke master employee_types — TIDAK ada perubahan
-- skema (kolom), cuma 1 baris data. Dropdown "Tipe Karyawan" di form Tambah/
-- Edit Karyawan sudah dinamis dari tabel ini, jadi tidak perlu deploy kode.
--
-- Setara dengan migration:
--   2026_09_01_120000_add_mitra_employee_type
--
-- WAJIB backup dulu. Jalankan SETELAH master-data-manual.sql (butuh tabel
-- `employee_types` sudah ada).
-- =============================================================================

INSERT INTO `employee_types` (`code`, `name`, `legacy_key`, `sort_order`, `is_active`, `created_at`, `updated_at`)
VALUES ('MITRA', 'Mitra', NULL, 7, 1, NOW(), NOW());

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_09_01_120000_add_mitra_employee_type', <BATCH>);
-- =============================================================================
