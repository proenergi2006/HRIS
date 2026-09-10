-- DEPLOY PRODUKSI HR — BAGIAN 1: TAHAP 7–17 (sebelum deploy kode)
-- Prasyarat: TAHAP 1–6 sudah ada. GA & whistleblower tidak disentuh.
-- WAJIB backup + `php artisan down` dulu.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;



-- =============================================================================
-- TAHAP 7 — payroll-pph21-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: PPh21 otomatis (TER) + tarif BPJS (PRD HRIS v1.2 Bab 9 — Payroll & Compensation)
--
-- Setara dengan migration 2026_08_28_1006xx.
--
-- ‼️ PENTING: batas penghasilan & persentase TER A/B/C di bawah adalah rekonstruksi
-- tabel resmi Lampiran PMK 168/2023 dari pengetahuan training model (31 Agu 2026,
-- BUKAN ditarik langsung dari dokumen PDF resmi — sesi kerja saat itu tak punya akses
-- internet). Kategori A relatif sering dikutip publik jadi keyakinan lebih tinggi;
-- B & C punya risiko selisih lebih besar di titik batas tengah. WAJIB dicocokkan
-- Finance/Tax ke Lampiran resmi (atau kalkulator TER pajak.go.id) sebelum dipakai
-- penggajian sungguhan — bisa diedit langsung lewat menu Master Data > Tarif PPh21
-- (TER), tidak perlu developer/migrasi ulang. Lihat juga
-- database/pph21-ter-rate-correction-manual.sql (kalau instalasi lama masih pakai
-- kurva ilustratif versi sebelumnya, jalankan file koreksi itu, bukan INSERT di bawah).
--
-- WAJIB backup dulu. Jalankan berurutan.
-- ============================================================================

CREATE TABLE `ter_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(5) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ter_categories_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ter_categories` (`id`,`code`,`name`,`description`,`created_at`,`updated_at`) VALUES
(1,'A','TER A','PTKP: TK/0, TK/1, K/0',NOW(),NOW()),
(2,'B','TER B','PTKP: TK/2, TK/3, K/1, K/2',NOW(),NOW()),
(3,'C','TER C','PTKP: K/3',NOW(),NOW());

CREATE TABLE `ter_brackets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ter_category_id` bigint(20) unsigned NOT NULL,
  `income_from` bigint(20) unsigned NOT NULL,
  `income_to` bigint(20) unsigned DEFAULT NULL,
  `rate_percent` decimal(5,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ter_brackets_ter_category_id_foreign` (`ter_category_id`),
  CONSTRAINT `ter_brackets_ter_category_id_foreign` FOREIGN KEY (`ter_category_id`) REFERENCES `ter_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ter_brackets` (`ter_category_id`,`income_from`,`income_to`,`rate_percent`,`created_at`,`updated_at`) VALUES
(1,0,5400000,0.00,NOW(),NOW()),
(1,5400000,5650000,0.25,NOW(),NOW()),
(1,5650000,5950000,0.50,NOW(),NOW()),
(1,5950000,6300000,0.75,NOW(),NOW()),
(1,6300000,6750000,1.00,NOW(),NOW()),
(1,6750000,7500000,1.25,NOW(),NOW()),
(1,7500000,8550000,1.50,NOW(),NOW()),
(1,8550000,9650000,1.75,NOW(),NOW()),
(1,9650000,10050000,2.00,NOW(),NOW()),
(1,10050000,10350000,2.25,NOW(),NOW()),
(1,10350000,10700000,2.50,NOW(),NOW()),
(1,10700000,11050000,3.00,NOW(),NOW()),
(1,11050000,11600000,3.50,NOW(),NOW()),
(1,11600000,12500000,4.00,NOW(),NOW()),
(1,12500000,13750000,5.00,NOW(),NOW()),
(1,13750000,15100000,6.00,NOW(),NOW()),
(1,15100000,16950000,7.00,NOW(),NOW()),
(1,16950000,19750000,8.00,NOW(),NOW()),
(1,19750000,24150000,9.00,NOW(),NOW()),
(1,24150000,26450000,10.00,NOW(),NOW()),
(1,26450000,28000000,11.00,NOW(),NOW()),
(1,28000000,30050000,12.00,NOW(),NOW()),
(1,30050000,32400000,13.00,NOW(),NOW()),
(1,32400000,35400000,14.00,NOW(),NOW()),
(1,35400000,39100000,15.00,NOW(),NOW()),
(1,39100000,43850000,16.00,NOW(),NOW()),
(1,43850000,47800000,17.00,NOW(),NOW()),
(1,47800000,51400000,18.00,NOW(),NOW()),
(1,51400000,56300000,19.00,NOW(),NOW()),
(1,56300000,62200000,20.00,NOW(),NOW()),
(1,62200000,68600000,21.00,NOW(),NOW()),
(1,68600000,77500000,22.00,NOW(),NOW()),
(1,77500000,89000000,23.00,NOW(),NOW()),
(1,89000000,103000000,24.00,NOW(),NOW()),
(1,103000000,125000000,25.00,NOW(),NOW()),
(1,125000000,157000000,26.00,NOW(),NOW()),
(1,157000000,206000000,27.00,NOW(),NOW()),
(1,206000000,337000000,28.00,NOW(),NOW()),
(1,337000000,454000000,29.00,NOW(),NOW()),
(1,454000000,550000000,30.00,NOW(),NOW()),
(1,550000000,695000000,31.00,NOW(),NOW()),
(1,695000000,910000000,32.00,NOW(),NOW()),
(1,910000000,1400000000,33.00,NOW(),NOW()),
(1,1400000000,NULL,34.00,NOW(),NOW()),
(2,0,6200000,0.00,NOW(),NOW()),
(2,6200000,6500000,0.25,NOW(),NOW()),
(2,6500000,6850000,0.50,NOW(),NOW()),
(2,6850000,7300000,0.75,NOW(),NOW()),
(2,7300000,9200000,1.00,NOW(),NOW()),
(2,9200000,10750000,1.50,NOW(),NOW()),
(2,10750000,11250000,2.00,NOW(),NOW()),
(2,11250000,11600000,2.50,NOW(),NOW()),
(2,11600000,12600000,3.00,NOW(),NOW()),
(2,12600000,13600000,4.00,NOW(),NOW()),
(2,13600000,14950000,5.00,NOW(),NOW()),
(2,14950000,16400000,6.00,NOW(),NOW()),
(2,16400000,18450000,7.00,NOW(),NOW()),
(2,18450000,21850000,8.00,NOW(),NOW()),
(2,21850000,26000000,9.00,NOW(),NOW()),
(2,26000000,27700000,10.00,NOW(),NOW()),
(2,27700000,29350000,11.00,NOW(),NOW()),
(2,29350000,31450000,12.00,NOW(),NOW()),
(2,31450000,33950000,13.00,NOW(),NOW()),
(2,33950000,37100000,14.00,NOW(),NOW()),
(2,37100000,41100000,15.00,NOW(),NOW()),
(2,41100000,45800000,16.00,NOW(),NOW()),
(2,45800000,49500000,17.00,NOW(),NOW()),
(2,49500000,53800000,18.00,NOW(),NOW()),
(2,53800000,58500000,19.00,NOW(),NOW()),
(2,58500000,64000000,20.00,NOW(),NOW()),
(2,64000000,71000000,21.00,NOW(),NOW()),
(2,71000000,80000000,22.00,NOW(),NOW()),
(2,80000000,93000000,23.00,NOW(),NOW()),
(2,93000000,109000000,24.00,NOW(),NOW()),
(2,109000000,129000000,25.00,NOW(),NOW()),
(2,129000000,163000000,26.00,NOW(),NOW()),
(2,163000000,211000000,27.00,NOW(),NOW()),
(2,211000000,374000000,28.00,NOW(),NOW()),
(2,374000000,459000000,29.00,NOW(),NOW()),
(2,459000000,555000000,30.00,NOW(),NOW()),
(2,555000000,704000000,31.00,NOW(),NOW()),
(2,704000000,957000000,32.00,NOW(),NOW()),
(2,957000000,1405000000,33.00,NOW(),NOW()),
(2,1405000000,NULL,34.00,NOW(),NOW()),
(3,0,6600000,0.00,NOW(),NOW()),
(3,6600000,6950000,0.25,NOW(),NOW()),
(3,6950000,7350000,0.50,NOW(),NOW()),
(3,7350000,7800000,0.75,NOW(),NOW()),
(3,7800000,8850000,1.00,NOW(),NOW()),
(3,8850000,9800000,1.25,NOW(),NOW()),
(3,9800000,10950000,1.50,NOW(),NOW()),
(3,10950000,11200000,1.75,NOW(),NOW()),
(3,11200000,12050000,2.00,NOW(),NOW()),
(3,12050000,12950000,3.00,NOW(),NOW()),
(3,12950000,14150000,4.00,NOW(),NOW()),
(3,14150000,15550000,5.00,NOW(),NOW()),
(3,15550000,17050000,6.00,NOW(),NOW()),
(3,17050000,19500000,7.00,NOW(),NOW()),
(3,19500000,22700000,8.00,NOW(),NOW()),
(3,22700000,26600000,9.00,NOW(),NOW()),
(3,26600000,28100000,10.00,NOW(),NOW()),
(3,28100000,30100000,11.00,NOW(),NOW()),
(3,30100000,32600000,12.00,NOW(),NOW()),
(3,32600000,35400000,13.00,NOW(),NOW()),
(3,35400000,38900000,14.00,NOW(),NOW()),
(3,38900000,43000000,15.00,NOW(),NOW()),
(3,43000000,47400000,16.00,NOW(),NOW()),
(3,47400000,51200000,17.00,NOW(),NOW()),
(3,51200000,55800000,18.00,NOW(),NOW()),
(3,55800000,60400000,19.00,NOW(),NOW()),
(3,60400000,66700000,20.00,NOW(),NOW()),
(3,66700000,74500000,21.00,NOW(),NOW()),
(3,74500000,83200000,22.00,NOW(),NOW()),
(3,83200000,95600000,23.00,NOW(),NOW()),
(3,95600000,110000000,24.00,NOW(),NOW()),
(3,110000000,134000000,25.00,NOW(),NOW()),
(3,134000000,169000000,26.00,NOW(),NOW()),
(3,169000000,221000000,27.00,NOW(),NOW()),
(3,221000000,390000000,28.00,NOW(),NOW()),
(3,390000000,463000000,29.00,NOW(),NOW()),
(3,463000000,561000000,30.00,NOW(),NOW()),
(3,561000000,709000000,31.00,NOW(),NOW()),
(3,709000000,965000000,32.00,NOW(),NOW()),
(3,965000000,1419000000,33.00,NOW(),NOW()),
(3,1419000000,NULL,34.00,NOW(),NOW());

-- Tambah calculation_type 'pph21_ter' ke salary_components, lalu alihkan komponen
-- "Potongan PPh 21" yang sudah ada (dulu manual) supaya otomatis. Nama komponen SENGAJA
-- tidak diganti — komponen "Tunjangan PPh 21" (gross-up, calc=mirror_pph21) mencari nominal
-- lewat nama "Potongan PPh 21", jadi harus tetap match.
ALTER TABLE `salary_components` MODIFY COLUMN `calculation_type` ENUM(
  'manual','percent_of_base','late_deduction','medical_claim','mirror_pph21',
  'position_fixed','position_daily','overtime','pph21_ter'
) NOT NULL DEFAULT 'manual';

UPDATE `salary_components` SET `calculation_type` = 'pph21_ter', `is_taxable` = 0
WHERE `name` = 'Potongan PPh 21';

-- ============================================================================
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_100601_create_ter_categories_table',<BATCH>),
-- ('2026_08_28_100602_create_ter_brackets_table',<BATCH>),
-- ('2026_08_28_100603_add_pph21_ter_to_salary_components',<BATCH>);
-- ============================================================================


-- =============================================================================
-- TAHAP 8 — kasbon-bonus-manual.sql
-- =============================================================================
-- =============================================================================
-- Modul: Kasbon/Pinjaman Karyawan + Bukti Potong PPh21 Tahunan + Bonus/Insentif
-- Fase 2 HRD — dikelola HR langsung (Kasbon/Bonus tanpa Approval Engine).
-- Setara dengan migration:
--   2026_08_29_170000_create_employee_loans_tables.php
--   2026_08_29_170100_add_tax_signer_to_companies.php
--   2026_08_29_170200_create_bonus_tables.php
-- WAJIB backup database dulu sebelum menjalankan file ini.
-- Jalankan SETELAH payroll-pph21-manual.sql (butuh salary_components sudah ada).
-- =============================================================================

-- ── A1. Kasbon / Pinjaman karyawan ──────────────────────────────────────────

CREATE TABLE `employee_loans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `loan_type` varchar(20) NOT NULL DEFAULT 'kasbon',
  `reference_no` varchar(50) DEFAULT NULL,
  `principal` bigint(20) NOT NULL,
  `installment_count` smallint(5) unsigned NOT NULL,
  `installment_amount` bigint(20) NOT NULL,
  `start_month` tinyint(3) unsigned NOT NULL,
  `start_year` smallint(5) unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_loans_employee_id_foreign` (`employee_id`),
  KEY `employee_loans_company_id_foreign` (`company_id`),
  KEY `employee_loans_created_by_foreign` (`created_by`),
  CONSTRAINT `employee_loans_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_loans_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_loans_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `loan_installments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_loan_id` bigint(20) unsigned NOT NULL,
  `payroll_slip_id` bigint(20) unsigned DEFAULT NULL,
  `period_month` tinyint(3) unsigned NOT NULL,
  `period_year` smallint(5) unsigned NOT NULL,
  `amount` bigint(20) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `deducted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `loan_installments_payroll_slip_id_foreign` (`payroll_slip_id`),
  KEY `loan_inst_period_idx` (`employee_loan_id`,`period_year`,`period_month`),
  CONSTRAINT `loan_installments_employee_loan_id_foreign` FOREIGN KEY (`employee_loan_id`) REFERENCES `employee_loans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loan_installments_payroll_slip_id_foreign` FOREIGN KEY (`payroll_slip_id`) REFERENCES `payroll_slips` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Komponen potongan cicilan — dihitung otomatis saat generate slip gaji
-- (lihat PayrollController::generate() -> calcLoanInstallment()).
ALTER TABLE salary_components MODIFY COLUMN calculation_type ENUM(
    'manual','percent_of_base','late_deduction','medical_claim','mirror_pph21',
    'position_fixed','position_daily','overtime','pph21_ter','loan_installment'
) NOT NULL DEFAULT 'manual';

INSERT INTO salary_components (company_id, name, type, calculation_type, is_taxable, is_active, sort_order, created_at, updated_at)
SELECT NULL, 'Potongan Kasbon/Pinjaman', 'deduction', 'loan_installment', 0, 1, 15, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM salary_components WHERE name = 'Potongan Kasbon/Pinjaman');

-- ── A2. Bukti Potong PPh21 Tahunan — identitas penandatangan ────────────────
-- companies.npwp sudah ada sejak awal, hanya tambah nama & NPWP penandatangan.

ALTER TABLE companies
  ADD COLUMN tax_signer_name varchar(150) DEFAULT NULL AFTER npwp,
  ADD COLUMN tax_signer_npwp varchar(30) DEFAULT NULL AFTER tax_signer_name;

-- ── A4. Bonus / Insentif ─────────────────────────────────────────────────────
-- Pola sama THR (thr-manual.sql) — one-off run standalone dari payroll_slips.

CREATE TABLE `bonus_periods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `name` varchar(120) NOT NULL,
  `bonus_type` varchar(20) NOT NULL DEFAULT 'bonus',
  `payment_date` date NOT NULL,
  `is_taxable` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `closed_by` bigint(20) unsigned DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bonus_periods_company_id_foreign` (`company_id`),
  KEY `bonus_periods_closed_by_foreign` (`closed_by`),
  CONSTRAINT `bonus_periods_closed_by_foreign` FOREIGN KEY (`closed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bonus_periods_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bonus_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bonus_period_id` bigint(20) unsigned NOT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `base_amount` bigint(20) DEFAULT NULL,
  `gross_amount` bigint(20) NOT NULL DEFAULT 0,
  `tax_amount` bigint(20) NOT NULL DEFAULT 0,
  `net_amount` bigint(20) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bonus_payments_bonus_period_id_employee_id_unique` (`bonus_period_id`,`employee_id`),
  KEY `bonus_payments_employee_id_foreign` (`employee_id`),
  CONSTRAINT `bonus_payments_bonus_period_id_foreign` FOREIGN KEY (`bonus_period_id`) REFERENCES `bonus_periods` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bonus_payments_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tidak perlu seeder baru — Kasbon & Bonus memakai permission `payroll.*` yang sudah ada.

-- Setelah migrasi manual di atas, tandai migration sebagai sudah dijalankan
-- (opsional, biar `php artisan migrate` di CI tidak mencoba jalanin ulang):
-- INSERT INTO migrations (migration, batch) VALUES
--   ('2026_08_29_170000_create_employee_loans_tables', <batch>),
--   ('2026_08_29_170100_add_tax_signer_to_companies', <batch>),
--   ('2026_08_29_170200_create_bonus_tables', <batch>);


-- =============================================================================
-- TAHAP 9 — thr-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: THR - Tunjangan Hari Raya (PRD HRIS v1.2 Bab 3 modul #9 — Payroll &
-- Compensation menyebut eksplisit "THR").
--
-- Setara dengan migration 2026_08_28_101201. Dihitung sesuai Permenaker No.
-- 6/2016: masa kerja >=1 bulan berhak THR proporsional (bulan kerja / 12,
-- dibatasi maks 12) x (Gaji Pokok + Tunjangan Jabatan).
--
-- WAJIB backup dulu. Jalankan setelah master-organization-manual.sql &
-- payroll (butuh companies/employees/positions/salary_components sudah ada).
-- ============================================================================

CREATE TABLE `thr_periods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `year` smallint(5) unsigned NOT NULL,
  `holiday_name` varchar(100) NOT NULL,
  `payment_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `closed_by` bigint(20) unsigned DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `thr_periods_company_id_foreign` (`company_id`),
  KEY `thr_periods_closed_by_foreign` (`closed_by`),
  CONSTRAINT `thr_periods_closed_by_foreign` FOREIGN KEY (`closed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `thr_periods_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `thr_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `thr_period_id` bigint(20) unsigned NOT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `base_salary` bigint(20) NOT NULL,
  `months_worked` tinyint(3) unsigned NOT NULL,
  `proration_ratio` decimal(4,3) NOT NULL,
  `thr_amount` bigint(20) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `thr_payments_thr_period_id_employee_id_unique` (`thr_period_id`,`employee_id`),
  KEY `thr_payments_employee_id_foreign` (`employee_id`),
  CONSTRAINT `thr_payments_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `thr_payments_thr_period_id_foreign` FOREIGN KEY (`thr_period_id`) REFERENCES `thr_periods` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Tidak perlu seeder baru — modul memakai permission `payroll.*` yang sudah ada.
--
-- Catatan sekaligus: batch ini juga menambah 2 method baru di
-- App\Http\Controllers\HR\PayrollController (mySlips/mySlipPdf) untuk ESS
-- "lihat slip gaji" (PRD Bab 4) — tidak perlu perubahan skema, cukup deploy kode.
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_101201_create_thr_periods_table',<BATCH>);
-- ============================================================================


-- =============================================================================
-- TAHAP 10 — overtime-request-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: Pengajuan Lembur self-service (PRD HRIS v1.2 Bab 3 modul #8 — Attendance
-- & Leave; Bab 3.2 secara eksplisit menyebut Overtime butuh Dynamic Approval
-- Workflow).
--
-- Setara dengan migration 2026_08_28_101101. Transaction type 'overtime_request'
-- SUDAH ADA di approval_workflows sejak batch approval-engine-manual.sql (default:
-- direct_manager) — file ini cuma menambah tabel modelnya, tidak perlu ubah
-- approval_workflows/approval_workflow_steps.
--
-- WAJIB backup dulu. Jalankan setelah approval-engine-manual.sql.
-- ============================================================================

CREATE TABLE `overtime_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `requested_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `date` date NOT NULL,
  `planned_hours` decimal(4,1) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `overtime_requests_employee_id_foreign` (`employee_id`),
  KEY `overtime_requests_company_id_foreign` (`company_id`),
  KEY `overtime_requests_requested_by_user_id_foreign` (`requested_by_user_id`),
  CONSTRAINT `overtime_requests_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `overtime_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `overtime_requests_requested_by_user_id_foreign` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Begitu disetujui, otomatis menulis ke attendance_records.overtime_minutes
-- (sumber data yang sama dipakai halaman HR > Lembur & Tunjangan Lembur payroll)
-- — tidak perlu migrasi data tambahan.
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_101101_create_overtime_requests_table',<BATCH>);
-- ============================================================================


-- =============================================================================
-- TAHAP 11 — manpower-planning-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: Manpower Planning (PRD HRIS v1.2 Bab 3 modul #2)
--
-- Setara dengan migration 2026_08_28_100701_create_manpower_plans_table.
--
-- WAJIB backup dulu. Jalankan setelah role-permission-manual.sql &
-- approval-engine-manual.sql (butuh company/departments/sections/positions/users sudah ada).
-- ============================================================================

CREATE TABLE `manpower_plans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `section_id` bigint(20) unsigned DEFAULT NULL,
  `position_id` bigint(20) unsigned DEFAULT NULL,
  `year` smallint(5) unsigned NOT NULL,
  `month` tinyint(3) unsigned DEFAULT NULL,
  `planned_headcount` int(10) unsigned NOT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `requested_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `notes_rejection` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `manpower_plans_company_id_foreign` (`company_id`),
  KEY `manpower_plans_department_id_foreign` (`department_id`),
  KEY `manpower_plans_section_id_foreign` (`section_id`),
  KEY `manpower_plans_position_id_foreign` (`position_id`),
  KEY `manpower_plans_requested_by_user_id_foreign` (`requested_by_user_id`),
  CONSTRAINT `manpower_plans_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `manpower_plans_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `manpower_plans_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `manpower_plans_requested_by_user_id_foreign` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `manpower_plans_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Setelah tabel dibuat, WAJIB jalankan ulang seeder berikut (idempoten):
--   php artisan db:seed --class=PermissionCatalogSeeder   (permission manpower-plan.*)
--   php artisan db:seed --class=ApprovalWorkflowSeeder    (workflow manpower_plan_request per company)
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_100701_create_manpower_plans_table',<BATCH>);
-- ============================================================================


-- =============================================================================
-- TAHAP 12 — recruitment-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: Recruitment + Pre-Employment + Onboarding (PRD HRIS v1.2 Bab 3 modul #3/4/5)
--
-- Setara dengan migration 2026_08_28_1008xx. Career-site publik TIDAK termasuk
-- (form internal saja, sesuai PRD 3.3 Out of Scope Fase 1).
--
-- WAJIB backup dulu. Jalankan setelah role-permission-manual.sql &
-- approval-engine-manual.sql (butuh company/departments/sections/positions/employees/
-- employee_types/users sudah ada).
-- ============================================================================

CREATE TABLE `job_requisitions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `section_id` bigint(20) unsigned DEFAULT NULL,
  `position_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `reason` text DEFAULT NULL,
  `headcount_requested` int(10) unsigned NOT NULL DEFAULT 1,
  `employment_type_id` bigint(20) unsigned DEFAULT NULL,
  `target_join_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `requested_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `notes_rejection` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_requisitions_company_id_foreign` (`company_id`),
  KEY `job_requisitions_department_id_foreign` (`department_id`),
  KEY `job_requisitions_section_id_foreign` (`section_id`),
  KEY `job_requisitions_position_id_foreign` (`position_id`),
  KEY `job_requisitions_employment_type_id_foreign` (`employment_type_id`),
  KEY `job_requisitions_requested_by_user_id_foreign` (`requested_by_user_id`),
  CONSTRAINT `job_requisitions_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `job_requisitions_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `job_requisitions_employment_type_id_foreign` FOREIGN KEY (`employment_type_id`) REFERENCES `employee_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `job_requisitions_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `job_requisitions_requested_by_user_id_foreign` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `job_requisitions_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `job_requisition_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'applied',
  `mcu_result` varchar(20) DEFAULT NULL,
  `converted_employee_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `candidates_job_requisition_id_foreign` (`job_requisition_id`),
  KEY `candidates_converted_employee_id_foreign` (`converted_employee_id`),
  CONSTRAINT `candidates_converted_employee_id_foreign` FOREIGN KEY (`converted_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `candidates_job_requisition_id_foreign` FOREIGN KEY (`job_requisition_id`) REFERENCES `job_requisitions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidate_interviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `stage` varchar(100) NOT NULL,
  `scheduled_at` datetime DEFAULT NULL,
  `interviewer_employee_id` bigint(20) unsigned DEFAULT NULL,
  `result` varchar(20) NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `candidate_interviews_candidate_id_foreign` (`candidate_id`),
  KEY `candidate_interviews_interviewer_employee_id_foreign` (`interviewer_employee_id`),
  CONSTRAINT `candidate_interviews_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `candidate_interviews_interviewer_employee_id_foreign` FOREIGN KEY (`interviewer_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidate_offers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `position_id` bigint(20) unsigned DEFAULT NULL,
  `offered_salary` bigint(20) DEFAULT NULL,
  `start_date_offered` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `candidate_offers_candidate_id_foreign` (`candidate_id`),
  KEY `candidate_offers_position_id_foreign` (`position_id`),
  CONSTRAINT `candidate_offers_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `candidate_offers_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidate_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `doc_type` varchar(60) NOT NULL,
  `title` varchar(200) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `candidate_documents_candidate_id_foreign` (`candidate_id`),
  CONSTRAINT `candidate_documents_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `onboarding_checklist_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `label` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `material_path` varchar(500) DEFAULT NULL,
  `material_original_name` varchar(255) DEFAULT NULL,
  `material_url` varchar(500) DEFAULT NULL,
  `category` varchar(20) NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `requires_acknowledgement` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `onboarding_checklist_items_company_id_foreign` (`company_id`),
  CONSTRAINT `onboarding_checklist_items_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- (kalau tabel sudah ada dari rilis lama, jalankan ALTER berikut:)
-- ALTER TABLE `onboarding_checklist_items`
--   ADD COLUMN `description` text DEFAULT NULL AFTER `label`,
--   ADD COLUMN `material_path` varchar(500) DEFAULT NULL AFTER `description`,
--   ADD COLUMN `material_original_name` varchar(255) DEFAULT NULL AFTER `material_path`,
--   ADD COLUMN `material_url` varchar(500) DEFAULT NULL AFTER `material_original_name`,
--   ADD COLUMN `requires_acknowledgement` tinyint(1) NOT NULL DEFAULT 0 AFTER `is_required`;

CREATE TABLE `employee_onboarding_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `onboarding_checklist_item_id` bigint(20) unsigned NOT NULL,
  `is_done` tinyint(1) NOT NULL DEFAULT 0,
  `done_at` datetime DEFAULT NULL,
  `done_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `acknowledged_at` datetime DEFAULT NULL,
  `acknowledgement_note` varchar(500) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_onboarding_task_unique` (`employee_id`,`onboarding_checklist_item_id`),
  KEY `employee_onboarding_tasks_onboarding_checklist_item_id_foreign` (`onboarding_checklist_item_id`),
  KEY `employee_onboarding_tasks_done_by_user_id_foreign` (`done_by_user_id`),
  CONSTRAINT `employee_onboarding_tasks_done_by_user_id_foreign` FOREIGN KEY (`done_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_onboarding_tasks_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_onboarding_tasks_onboarding_checklist_item_id_foreign` FOREIGN KEY (`onboarding_checklist_item_id`) REFERENCES `onboarding_checklist_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Item checklist onboarding default (global) ───────────────────────────────
-- Setara OnboardingChecklistItemSeeder. Bisa ditambah/nonaktifkan/dilampiri materi
-- per PT lewat menu Rekrutmen > Onboarding > Template Checklist.
-- Kategori dokumen/akun/aset = diceklis HR. Kategori induction (requires_acknowledgement=1)
-- = dibaca & dikonfirmasi KARYAWAN lewat menu "Onboarding Saya".
-- Saat konversi kandidat, item NIP + Perjanjian kerja (+ Email & akun bila buat akun)
-- otomatis ditandai selesai.
INSERT INTO `onboarding_checklist_items`
(`company_id`,`label`,`description`,`category`,`is_required`,`requires_acknowledgement`,`sort_order`,`is_active`,`created_at`,`updated_at`) VALUES
(NULL,'Background check / verifikasi latar belakang',NULL,'dokumen',1,0,10,1,NOW(),NOW()),
(NULL,'Perjanjian kerja ditandatangani',NULL,'dokumen',1,0,20,1,NOW(),NOW()),
(NULL,'Nomor Induk Karyawan (NIP) diterbitkan',NULL,'akun',1,0,30,1,NOW(),NOW()),
(NULL,'Email & akun sistem dibuat',NULL,'akun',1,0,40,1,NOW(),NOW()),
(NULL,'Kartu akses / kartu absensi',NULL,'aset',1,0,50,1,NOW(),NOW()),
(NULL,'Laptop / perangkat kerja',NULL,'aset',1,0,60,1,NOW(),NOW()),
(NULL,'Meja kerja / workstation',NULL,'aset',1,0,70,1,NOW(),NOW()),
(NULL,'Seragam / APD',NULL,'aset',0,0,80,1,NOW(),NOW()),
(NULL,'Welcome','Sambutan manajemen & gambaran umum hari pertama.','induction',1,1,100,1,NOW(),NOW()),
(NULL,'Company Introduction / Orientation','Sejarah, visi-misi, nilai perusahaan, lini bisnis, dan lokasi kerja.','induction',1,1,110,1,NOW(),NOW()),
(NULL,'Organization','Struktur organisasi, jenjang jabatan, dan alur pelaporan.','induction',1,1,120,1,NOW(),NOW()),
(NULL,'HR Procedure','Kehadiran, cuti, lembur, penilaian kinerja, tata tertib, dan sanksi.','induction',1,1,130,1,NOW(),NOW()),
(NULL,'Fakta Integritas','Pernyataan integritas, benturan kepentingan, anti-suap & gratifikasi. Wajib dibaca dan disetujui.','induction',1,1,140,1,NOW(),NOW()),
(NULL,'HR Operation & Incentive','Penggajian, komponen upah, THR/bonus, insentif, BPJS, dan reimbursement.','induction',1,1,150,1,NOW(),NOW()),
(NULL,'GA Procedure','Fasilitas kantor, aset, kendaraan, perjalanan dinas, kebersihan & keamanan.','induction',1,1,160,1,NOW(),NOW()),
(NULL,'Vopak Procedure','Prosedur operasi & HSSE terminal Vopak yang berlaku di area kerja.','induction',1,1,170,1,NOW(),NOW()),
(NULL,'Logistic & Operational Procedure','Alur logistik, penerimaan/pengiriman, dan SOP operasional lapangan.','induction',0,1,180,1,NOW(),NOW()),
(NULL,'Sales Administration & Finance Procedure','Administrasi penjualan, invoicing, penagihan, dan pelaporan keuangan.','induction',0,1,190,1,NOW(),NOW()),
(NULL,'Legal & Collection Procedure','Kontrak, kepatuhan hukum, dan prosedur penagihan piutang.','induction',0,1,200,1,NOW(),NOW()),
(NULL,'Business Overview (Commercial)','Peta pasar, pelanggan utama, dan strategi komersial.','induction',0,1,210,1,NOW(),NOW()),
(NULL,'Selling Skill & Product Knowledge','Pengetahuan produk dan keterampilan penjualan dasar.','induction',0,1,220,1,NOW(),NOW()),
(NULL,'Procurement Procedure','Permintaan pembelian, vendor, dan proses pengadaan.','induction',0,1,230,1,NOW(),NOW());

-- ============================================================================
-- Setelah tabel dibuat, WAJIB jalankan ulang seeder berikut (idempoten):
--   php artisan db:seed --class=PermissionCatalogSeeder   (permission recruitment.*)
--   php artisan db:seed --class=ApprovalWorkflowSeeder    (workflow job_requisition per company)
--   php artisan db:seed --class=OnboardingChecklistItemSeeder  (kalau tidak pakai INSERT di atas)
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_100801_create_job_requisitions_table',<BATCH>),
-- ('2026_08_28_100802_create_candidates_tables',<BATCH>),
-- ('2026_08_28_100803_create_onboarding_tables',<BATCH>);
-- ============================================================================


-- =============================================================================
-- TAHAP 13 — training-career-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: Training & Development + Career Management (PRD HRIS v1.2 Bab 3 modul #11/#12)
--
-- Setara dengan migration 2026_08_28_1009xx.
--
-- WAJIB backup dulu. Jalankan setelah role-permission-manual.sql (butuh
-- companies/positions/employees sudah ada).
-- ============================================================================

CREATE TABLE `training_programs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `provider` varchar(150) DEFAULT NULL,
  `duration_hours` int(10) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `training_programs_company_id_foreign` (`company_id`),
  CONSTRAINT `training_programs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `training_participants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `training_program_id` bigint(20) unsigned NOT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'planned',
  `score` varchar(20) DEFAULT NULL,
  `certificate_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `training_participants_training_program_id_foreign` (`training_program_id`),
  KEY `training_participants_employee_id_foreign` (`employee_id`),
  CONSTRAINT `training_participants_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `training_participants_training_program_id_foreign` FOREIGN KEY (`training_program_id`) REFERENCES `training_programs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `career_paths` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `career_paths_company_id_foreign` (`company_id`),
  CONSTRAINT `career_paths_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `career_path_steps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `career_path_id` bigint(20) unsigned NOT NULL,
  `position_id` bigint(20) unsigned NOT NULL,
  `step_order` int(10) unsigned NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `career_path_steps_career_path_id_foreign` (`career_path_id`),
  KEY `career_path_steps_position_id_foreign` (`position_id`),
  CONSTRAINT `career_path_steps_career_path_id_foreign` FOREIGN KEY (`career_path_id`) REFERENCES `career_paths` (`id`) ON DELETE CASCADE,
  CONSTRAINT `career_path_steps_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `employees`
  ADD COLUMN `career_path_id` bigint(20) unsigned DEFAULT NULL AFTER `level_id`,
  ADD CONSTRAINT `employees_career_path_id_foreign` FOREIGN KEY (`career_path_id`) REFERENCES `career_paths` (`id`) ON DELETE SET NULL;

-- ============================================================================
-- Setelah tabel dibuat, WAJIB jalankan ulang seeder berikut (idempoten):
--   php artisan db:seed --class=PermissionCatalogSeeder   (permission training.*, career.*)
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_100901_create_training_programs_table',<BATCH>),
-- ('2026_08_28_100902_create_training_participants_table',<BATCH>),
-- ('2026_08_28_100903_create_career_paths_table',<BATCH>),
-- ('2026_08_28_100904_create_career_path_steps_table',<BATCH>),
-- ('2026_08_28_100905_add_career_path_id_to_employees_table',<BATCH>);
-- ============================================================================


-- =============================================================================
-- TAHAP 14 — competency-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: Competency Framework (melengkapi PRD HRIS v1.2 Bab 3 modul #11
--        Training & Development — bagian "competency").
--
-- Setara dengan migration 2026_08_29_150000_create_competency_tables.
--
-- WAJIB backup dulu. Jalankan setelah training-career-manual.sql (butuh
-- companies/positions/employees/users sudah ada).
-- ============================================================================

CREATE TABLE `competencies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `competencies_code_unique` (`code`),
  KEY `competencies_company_id_foreign` (`company_id`),
  CONSTRAINT `competencies_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `position_competencies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `position_id` bigint(20) unsigned NOT NULL,
  `competency_id` bigint(20) unsigned NOT NULL,
  `required_level` tinyint(3) unsigned NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `position_competencies_position_id_competency_id_unique` (`position_id`,`competency_id`),
  KEY `position_competencies_competency_id_foreign` (`competency_id`),
  CONSTRAINT `position_competencies_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `position_competencies_competency_id_foreign` FOREIGN KEY (`competency_id`) REFERENCES `competencies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_competencies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `competency_id` bigint(20) unsigned NOT NULL,
  `actual_level` tinyint(3) unsigned NOT NULL,
  `assessed_on` date DEFAULT NULL,
  `assessor_user_id` bigint(20) unsigned DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_competencies_employee_id_competency_id_unique` (`employee_id`,`competency_id`),
  KEY `employee_competencies_competency_id_foreign` (`competency_id`),
  KEY `employee_competencies_assessor_user_id_foreign` (`assessor_user_id`),
  CONSTRAINT `employee_competencies_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_competencies_competency_id_foreign` FOREIGN KEY (`competency_id`) REFERENCES `competencies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_competencies_assessor_user_id_foreign` FOREIGN KEY (`assessor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Setelah tabel dibuat, WAJIB jalankan seeder berikut (idempoten):
--   php artisan db:seed --class=PermissionCatalogSeeder   (permission competency.*)
--   php artisan db:seed --class=CompetencySeeder          (~10 kompetensi default)
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_29_150000_create_competency_tables',<BATCH>);
-- ============================================================================


-- =============================================================================
-- TAHAP 15 — employee-administration-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: Employee Administration — surat, perubahan data (PRD HRIS v1.2 Bab 3
-- modul #7; "kontrak" di modul ini sudah ada sejak batch employee-data-tabs).
--
-- Setara dengan migration 2026_08_28_1010xx.
--
-- WAJIB backup dulu. Jalankan setelah role-permission-manual.sql &
-- approval-engine-manual.sql (butuh employees/users/companies sudah ada).
-- ============================================================================

CREATE TABLE `employee_data_change_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `requested_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `field_key` varchar(50) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text NOT NULL,
  `reason` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `notes_rejection` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_data_change_requests_employee_id_foreign` (`employee_id`),
  KEY `employee_data_change_requests_requested_by_user_id_foreign` (`requested_by_user_id`),
  CONSTRAINT `employee_data_change_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_data_change_requests_requested_by_user_id_foreign` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `letter_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'lainnya',
  `body` longtext NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `letter_templates_company_id_foreign` (`company_id`),
  CONSTRAINT `letter_templates_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_letters` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `letter_template_id` bigint(20) unsigned DEFAULT NULL,
  `letter_number` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` longtext NOT NULL,
  `issued_date` date NOT NULL,
  `issued_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_letters_employee_id_foreign` (`employee_id`),
  KEY `employee_letters_letter_template_id_foreign` (`letter_template_id`),
  KEY `employee_letters_issued_by_user_id_foreign` (`issued_by_user_id`),
  CONSTRAINT `employee_letters_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_letters_issued_by_user_id_foreign` FOREIGN KEY (`issued_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_letters_letter_template_id_foreign` FOREIGN KEY (`letter_template_id`) REFERENCES `letter_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Setelah tabel dibuat, WAJIB jalankan ulang seeder berikut (idempoten):
--   php artisan db:seed --class=ApprovalWorkflowSeeder  (workflow
--   employee_data_change_request per company)
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_101001_create_employee_data_change_requests_table',<BATCH>),
-- ('2026_08_28_101002_create_letter_templates_table',<BATCH>),
-- ('2026_08_28_101003_create_employee_letters_table',<BATCH>);
-- ============================================================================


-- =============================================================================
-- TAHAP 16 — appraisal-kpi-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: Performance Management — rebuild Appraisal dari model "aspek + bobot"
-- (BS/B/C/K, atau 4-penilai self/atasan1/atasan2/HO) ke model KPI/objective-
-- based, dan migrasi approval dari state machine 2-step (appraisal_flow_configs)
-- ke Approval Engine generik (approval_workflows/approval_requests) — sama
-- seperti Cuti/Perdin/Reimbursement/Reward/Punishment/Promosi/Termination/
-- Manpower Plan/Job Requisition/Overtime/dll.
--
-- Setara dengan migration 2026_08_28_101301_rebuild_appraisal_to_kpi_model.
--
-- PERINGATAN — DESTRUKTIF: DROP 5 tabel appraisal lama beserta isinya
-- (appraisal_approvals, appraisal_items, appraisal_aspect_weights,
-- appraisal_aspects, appraisal_flow_configs). WAJIB BACKUP DULU kalau ada
-- data transaksi appraisal nyata di tabel-tabel ini — keputusan produk
-- (28 Agu 2026) adalah hapus & ganti total, bukan migrasi data lama.
--
-- Jalankan setelah approval-engine-manual.sql (butuh approval_workflows/
-- approval_requests/approval_request_steps sudah ada) & master-organization
-- (butuh departments/positions sudah ada, dipakai ITDemoSeeder versi baru).
-- ============================================================================

DROP TABLE IF EXISTS `appraisal_approvals`;
DROP TABLE IF EXISTS `appraisal_items`;
DROP TABLE IF EXISTS `appraisal_aspect_weights`;
DROP TABLE IF EXISTS `appraisal_aspects`;
DROP TABLE IF EXISTS `appraisal_flow_configs`;

-- ── appraisal_templates: buang scoring_type (cuma 1 model skor sekarang) ────
ALTER TABLE `appraisal_templates`
  DROP COLUMN `scoring_type`;

-- ── appraisal_template_objectives: KPI starter per template (opsional,      ─
--    otomatis disalin ke appraisal_objectives saat appraisal baru dibuat) ──
CREATE TABLE `appraisal_template_objectives` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `appraisal_template_id` bigint(20) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `weight_pct` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `order` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `appraisal_template_objectives_appraisal_template_id_foreign` (`appraisal_template_id`),
  CONSTRAINT `appraisal_template_objectives_appraisal_template_id_foreign` FOREIGN KEY (`appraisal_template_id`) REFERENCES `appraisal_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── appraisals: buang kolom model lama, sesuaikan status/total_score ke ─────
--    konvensi Approval Engine (draft/pending/approved/rejected/cancelled) ──
ALTER TABLE `appraisals`
  DROP COLUMN `score_self`,
  DROP COLUMN `score_atasan1`,
  DROP COLUMN `score_atasan2`,
  DROP COLUMN `score_ho`,
  DROP COLUMN `avg_late_per_month`,
  DROP COLUMN `avg_leave_per_month`,
  DROP COLUMN `warning_letter`,
  DROP COLUMN `sp_level`,
  DROP COLUMN `decision`,
  DROP COLUMN `individual_development_plan`;

ALTER TABLE `appraisals`
  MODIFY `status` varchar(20) NOT NULL DEFAULT 'draft',
  MODIFY `total_score` decimal(6,2) NOT NULL DEFAULT 0.00,
  -- template sekarang opsional ("Tanpa Template — isi KPI dari nol"), FK
  -- aslinya NOT NULL sejak tabel dibuat (create_appraisals_table).
  MODIFY `appraisal_template_id` bigint(20) unsigned DEFAULT NULL,
  ADD COLUMN `development_notes` text DEFAULT NULL AFTER `strength_points`;

ALTER TABLE `appraisals`
  CHANGE COLUMN `strength_points` `strengths` text DEFAULT NULL;

ALTER TABLE `appraisals`
  DROP COLUMN `development_need`;

-- ── appraisal_objectives: KPI/objective aktual per appraisal (pengganti ─────
--    appraisal_aspects + appraisal_items) ───────────────────────────────────
CREATE TABLE `appraisal_objectives` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `appraisal_id` bigint(20) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `weight_pct` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `target` text DEFAULT NULL,
  `actual` text DEFAULT NULL,
  `achievement_pct` decimal(6,2) DEFAULT NULL,
  `score` decimal(6,2) DEFAULT NULL,
  `order` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `appraisal_objectives_appraisal_id_foreign` (`appraisal_id`),
  CONSTRAINT `appraisal_objectives_appraisal_id_foreign` FOREIGN KEY (`appraisal_id`) REFERENCES `appraisals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Approval workflow default untuk transaction_type='appraisal' (specific_role
-- hr_manager -> specific_role ceo, per company) sudah didaftarkan lewat
-- ApprovalWorkflowSeeder — kalau approval-engine-manual.sql sudah pernah
-- dijalankan sebelum patch ini, jalankan ulang seeder tsb (atau INSERT manual
-- ke approval_workflows/approval_workflow_steps mengikuti pola company lain).
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_101301_rebuild_appraisal_to_kpi_model',<BATCH>);
-- ============================================================================


-- =============================================================================
-- TAHAP 17 — approval-audit-region-manual.sql
-- =============================================================================
-- ============================================================================
-- Batch: 3 celah PRD terakhir.
--   1. Audit trail perubahan aturan approval (Bab 7.5)
--   2. Master Kecamatan & Kelurahan (Bab 5.1)
--   3. Dashboard Headcount (Bab 10) — TIDAK ADA perubahan skema (kode + view saja)
--
-- Setara dengan migration:
--   2026_08_29_160000_create_approval_workflow_change_logs_table
--   2026_08_29_161000_create_districts_and_villages_tables
--   2026_08_29_161100_add_district_village_fk_to_employees
--
-- WAJIB backup dulu. Jalankan setelah gap-closure-manual.sql & competency-manual.sql.
-- ============================================================================

-- ── 1) Audit perubahan alur persetujuan ─────────────────────────────────────
CREATE TABLE `approval_workflow_change_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `transaction_label` varchar(100) DEFAULT NULL,
  `action` varchar(20) NOT NULL,
  `before` longtext DEFAULT NULL CHECK (json_valid(`before`)),
  `after` longtext DEFAULT NULL CHECK (json_valid(`after`)),
  `changed_by` bigint(20) unsigned DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `awcl_company_type_idx` (`company_id`,`transaction_type`),
  KEY `awcl_changed_by_foreign` (`changed_by`),
  CONSTRAINT `awcl_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `awcl_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 2) Master Kecamatan & Kelurahan ─────────────────────────────────────────
CREATE TABLE `districts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `city_id` bigint(20) unsigned NOT NULL,
  `code` varchar(15) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `districts_code_unique` (`code`),
  KEY `districts_city_id_name_index` (`city_id`,`name`),
  CONSTRAINT `districts_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `villages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `district_id` bigint(20) unsigned NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(15) NOT NULL DEFAULT 'kelurahan',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `villages_code_unique` (`code`),
  KEY `villages_district_id_name_index` (`district_id`,`name`),
  CONSTRAINT `villages_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `employees`
  ADD COLUMN `domicile_district_id` bigint(20) unsigned DEFAULT NULL AFTER `domicile_district`,
  ADD COLUMN `domicile_village_id` bigint(20) unsigned DEFAULT NULL AFTER `domicile_subdistrict`,
  ADD COLUMN `ktp_district_id` bigint(20) unsigned DEFAULT NULL AFTER `ktp_district`,
  ADD COLUMN `ktp_village_id` bigint(20) unsigned DEFAULT NULL AFTER `ktp_subdistrict`,
  ADD CONSTRAINT `employees_domicile_district_id_foreign` FOREIGN KEY (`domicile_district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_domicile_village_id_foreign` FOREIGN KEY (`domicile_village_id`) REFERENCES `villages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_ktp_district_id_foreign` FOREIGN KEY (`ktp_district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_ktp_village_id_foreign` FOREIGN KEY (`ktp_village_id`) REFERENCES `villages` (`id`) ON DELETE SET NULL;

-- Backfill dari nilai teks lama (jalankan SETELAH RegionDistrictSeeder)
UPDATE `employees` e JOIN `districts` d ON LOWER(TRIM(e.`domicile_district`)) = LOWER(d.`name`) SET e.`domicile_district_id` = d.`id` WHERE e.`domicile_district_id` IS NULL;
UPDATE `employees` e JOIN `districts` d ON LOWER(TRIM(e.`ktp_district`))      = LOWER(d.`name`) SET e.`ktp_district_id`      = d.`id` WHERE e.`ktp_district_id` IS NULL;
UPDATE `employees` e JOIN `villages`  v ON LOWER(TRIM(e.`domicile_subdistrict`)) = LOWER(v.`name`) SET e.`domicile_village_id` = v.`id` WHERE e.`domicile_village_id` IS NULL;
UPDATE `employees` e JOIN `villages`  v ON LOWER(TRIM(e.`ktp_subdistrict`))      = LOWER(v.`name`) SET e.`ktp_village_id`      = v.`id` WHERE e.`ktp_village_id` IS NULL;

-- ============================================================================
-- Setelah tabel dibuat, jalankan seeder starter (idempoten):
--   php artisan db:seed --class=RegionDistrictSeeder
--   (kecamatan ~6 kota besar + kelurahan Jakarta Selatan/Pusat)
--
-- DATASET PENUH INDONESIA (~7rb kecamatan, ~83rb kelurahan) di-import terpisah
-- dari wilayah.id / Kemendagri — masukkan ke database/data/regions-districts.php
-- lalu jalankan ulang RegionDistrictSeeder (aman diulang).
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_08_29_160000_create_approval_workflow_change_logs_table',<BATCH>),
--   ('2026_08_29_161000_create_districts_and_villages_tables',<BATCH>),
--   ('2026_08_29_161100_add_district_village_fk_to_employees',<BATCH>);
-- ============================================================================

-- ###########################################################################

SET FOREIGN_KEY_CHECKS = 1;
