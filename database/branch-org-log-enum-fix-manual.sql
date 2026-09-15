-- =============================================================================
-- Fix urgent 15 Sep 2026: org_change_logs.unit_type belum kenal 'branch'
-- =============================================================================
-- Tanpa ini, klik "Tambah" di menu Cabang GAGAL (500) dengan error:
--   SQLSTATE[01000]: Warning: 1265 Data truncated for column 'unit_type'
-- karena BranchController mencatat log dgn unit_type='branch', tapi kolom
-- org_change_logs.unit_type ENUM cuma kenal division/department/section/position.
--
-- Setara migration: 2026_09_15_110000_add_branch_to_org_change_logs_unit_type
-- WAJIB backup dulu (walau cuma ALTER enum, bukan DROP data).
-- =============================================================================

ALTER TABLE `org_change_logs` MODIFY COLUMN `unit_type`
  ENUM('branch','division','department','section','position') NOT NULL;

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_09_15_110000_add_branch_to_org_change_logs_unit_type', <BATCH>);
-- =============================================================================
