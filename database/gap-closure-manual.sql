-- ============================================================================
-- Batch: Penutupan 9 gap PRD HRIS v1.2 (cross-module / NFR / reporting).
--
-- Setara dengan migration:
--   2026_08_29_140000_encrypt_employee_sensitive_data
--   2026_08_29_141000_drop_legacy_enum_columns_from_employees
--
-- Sebagian besar dari 9 item BUKAN perubahan skema (cuma kode) dan tidak butuh
-- SQL manual:
--   1. Notifikasi email approval generik      -> app/Services/ApprovalEngine.php + 2 Mailable baru
--   2. Audit trail (LogsActivity) 9 model     -> pakai tabel `activity_log` yang sudah ada
--   3. Escalation reminder terjadwal          -> app/Console/Commands/SendApprovalOverdueReminders.php
--                                                + Schedule di routes/console.php (butuh cron
--                                                `php artisan schedule:run` tiap menit di server)
--   5. Backup DB harian otomatis              -> app/Console/Commands/BackupDatabase.php + Schedule
--                                                (butuh binary `mysqldump` di PATH server)
--   6. Laporan Absensi & Cuti bulanan         -> LaporanController + view (route laporan.attendance-leave)
--   7. Laporan Payroll summary per periode    -> LaporanController + view (route laporan.payroll)
--   8. Org chart drag & drop reparenting      -> view + endpoint reparent (permission org-structure.edit)
--
-- Yang di bawah ini HANYA item 4 (enkripsi) & 9 (drop kolom lama).
--
-- WAJIB backup dulu.
-- ============================================================================

-- ── Item 4: Enkripsi data sensitif (PRD Bab 9 — NIK, NPWP, No. rekening) ─────
-- Lebarkan kolom ke TEXT (ciphertext Laravel jauh lebih panjang dari plaintext).
ALTER TABLE `employees`
  MODIFY `ktp_number`  text DEFAULT NULL,
  MODIFY `npwp_number` text DEFAULT NULL;

ALTER TABLE `employee_bank_accounts`
  MODIFY `account_number` text NOT NULL;

-- PENTING: setelah ALTER di atas, WAJIB jalankan command berikut di server untuk
-- mengenkripsi data yang sudah ada (SQL murni tidak bisa — butuh APP_KEY Laravel):
--
--     php artisan employees:encrypt-sensitive
--
-- Command ini idempotent (aman diulang). Cast 'encrypted' sudah ditambahkan di
-- App\Models\Employee & App\Models\EmployeeBankAccount — jangan deploy kode model
-- baru SEBELUM data lama dienkripsi, atau read via Eloquent akan melempar
-- DecryptException. Urutan aman: (a) ALTER TABLE, (b) deploy kode, (c) segera
-- jalankan `php artisan employees:encrypt-sensitive`.
--
-- CATATAN APP_KEY: nilai terenkripsi terikat ke APP_KEY. Jangan rotate APP_KEY
-- setelah data dienkripsi tanpa proses re-enkripsi (decrypt pakai key lama →
-- encrypt pakai key baru), atau data NIK/NPWP/rekening jadi tidak terbaca.

-- ── Item 9: Drop kolom string lama di `employees` (sudah digantikan FK master) ─
-- marital_status -> marital_status_id | religion_name -> religion_id | blood_type -> blood_type_id
-- (FK sudah lama diisi; tidak ada read-path tersisa ke kolom string ini).
-- contract_end_date SENGAJA TIDAK di-drop — masih aktif dipakai (contract reminder,
-- dashboard, laporan, notifikasi header).
ALTER TABLE `employees`
  DROP COLUMN `marital_status`,
  DROP COLUMN `religion_name`,
  DROP COLUMN `blood_type`;

-- ============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_08_29_140000_encrypt_employee_sensitive_data', <BATCH>),
--   ('2026_08_29_141000_drop_legacy_enum_columns_from_employees', <BATCH>);
-- ============================================================================
