-- =============================================================================
-- Tambah 'mitra' ke employees.employment_status ("Status Kontrak" di tab
-- Employee Information — BEDA dari employee_contracts.contract_type di tab
-- Kontrak Kerja, yang sudah dapat 'mitra' duluan di
-- database/mitra-contract-family-limit-manual.sql).
-- =============================================================================
-- Setara migration: 2026_09_15_120000_add_mitra_to_employees_employment_status
-- WAJIB backup dulu.
-- =============================================================================

ALTER TABLE `employees` MODIFY COLUMN `employment_status`
  ENUM('permanent','contract','probation','mitra') NOT NULL DEFAULT 'permanent';

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_09_15_120000_add_mitra_to_employees_employment_status', <BATCH>);
-- =============================================================================
