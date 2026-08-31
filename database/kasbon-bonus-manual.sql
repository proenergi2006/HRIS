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
