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
