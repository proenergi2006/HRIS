-- Modul: Reimbursement — Periode Pembayaran (bulan gaji klaim dibayarkan)
-- Setara dengan migration:
--   2026_08_20_100000_add_payment_period_to_reimbursement_requests_table
--
-- WAJIB backup database dulu sebelum jalankan.
-- Cek dulu struktur reimbursement_requests saat ini: DESCRIBE reimbursement_requests;

ALTER TABLE `reimbursement_requests`
  ADD COLUMN `payment_month` tinyint(3) unsigned DEFAULT NULL AFTER `approved_at`,
  ADD COLUMN `payment_year`  smallint(5) unsigned DEFAULT NULL AFTER `payment_month`;

-- Opsional: daftarkan ke tabel migrations Laravel supaya `php artisan migrate`
-- tidak mencoba menjalankan ulang perubahan ini nanti. Cek dulu:
--   SELECT MAX(batch) FROM migrations;
-- lalu ganti angka <BATCH> di bawah dengan (hasil query di atas + 1).
--
-- INSERT INTO `migrations` (`migration`, `batch`) VALUES
-- ('2026_08_20_100000_add_payment_period_to_reimbursement_requests_table', <BATCH>);
