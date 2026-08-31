-- =============================================================================
-- Modul: Normalisasi kolom status modul lama saat pindah ke Approval Engine
--        (Cuti / Perjalanan Dinas / Reimbursement)
--
-- Setara dengan migration:
--   2026_08_28_100401_migrate_leave_requests_to_approval_engine
--   2026_08_28_100402_migrate_perdin_requests_to_approval_engine
--   2026_08_28_100403_migrate_reimbursement_requests_to_approval_engine
--
-- WAJIB backup database dulu.
-- Jalankan SETELAH approval-engine-manual.sql (tabel approval_* & workflow seeder
-- sudah ada) dan SETELAH deploy kode baru.
--
-- CATATAN PENTING soal pengajuan yang masih berjalan (in-flight):
--   File SQL ini hanya (a) mengubah tipe kolom status enum -> varchar dan
--   (b) menormalkan NILAI status lama. Ia TIDAK membuat baris approval_requests
--   untuk pengajuan yang masih menunggu persetujuan (butuh logika PHP
--   ApprovalEngine::start()).
--
--   Pilihan:
--   A. Deploy saat tidak ada pengajuan berjalan (paling aman) — mis. minta
--      approver menuntaskan semua pengajuan pending dulu.
--   B. Jalankan hanya 3 migration ini via artisan supaya in-flight ikut
--      terdaftar ke engine:
--        php artisan migrate --path=database/migrations/2026_08_28_100401_migrate_leave_requests_to_approval_engine.php
--        php artisan migrate --path=database/migrations/2026_08_28_100402_migrate_perdin_requests_to_approval_engine.php
--        php artisan migrate --path=database/migrations/2026_08_28_100403_migrate_reimbursement_requests_to_approval_engine.php
--      (kalau pakai opsi B, JANGAN jalankan blok SQL di bawah — migration sudah
--       melakukannya.)
--   C. Jalankan SQL di bawah, lalu minta pemohon submit ulang pengajuan yang
--      masih pending.
-- =============================================================================

-- ── 1) Cuti (leave_requests) ────────────────────────────────────────────────
-- enum lama: draft / submitted / approved_manager / approved_hr / rejected
ALTER TABLE `leave_requests`
  MODIFY COLUMN `status` VARCHAR(30) NOT NULL DEFAULT 'draft';

UPDATE `leave_requests` SET `status` = 'pending'  WHERE `status` IN ('submitted', 'approved_manager');
UPDATE `leave_requests` SET `status` = 'approved' WHERE `status` = 'approved_hr';
-- 'rejected' & 'draft' dibiarkan apa adanya.

-- ── 2) Perjalanan Dinas (perdin_requests) ──────────────────────────────────
-- enum lama: draft / submitted / reviewed_manager / reviewed_hr / approved / rejected
ALTER TABLE `perdin_requests`
  MODIFY COLUMN `status` VARCHAR(30) NOT NULL DEFAULT 'draft';

UPDATE `perdin_requests` SET `status` = 'pending'
  WHERE `status` IN ('submitted', 'reviewed_manager', 'reviewed_hr');
-- 'approved' / 'rejected' / 'draft' dibiarkan apa adanya.

-- ── 3) Reimbursement (reimbursement_requests) ──────────────────────────────
-- kolom status sudah VARCHAR(20) sejak awal — cukup normalisasi nilai.
UPDATE `reimbursement_requests` SET `status` = 'pending' WHERE `status` = 'submitted';

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_100401_migrate_leave_requests_to_approval_engine',<BATCH>),
-- ('2026_08_28_100402_migrate_perdin_requests_to_approval_engine',<BATCH>),
-- ('2026_08_28_100403_migrate_reimbursement_requests_to_approval_engine',<BATCH>);
-- =============================================================================
