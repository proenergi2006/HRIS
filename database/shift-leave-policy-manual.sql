-- =============================================================================
-- Modul: Shift & Roster + Kebijakan Cuti (carry-forward / kuota per golongan)
-- Fase 2 HRD. Setara dengan migration:
--   2026_08_29_180000_create_shift_and_roster_tables.php
--   2026_08_29_180100_create_leave_policies_table.php
-- WAJIB backup database dulu sebelum menjalankan file ini.
-- Jalankan SETELAH hr-module-manual.sql (butuh attendance_records & leave_balances ada).
-- =============================================================================

-- ── B1. Shift & Roster ───────────────────────────────────────────────────────

CREATE TABLE `shifts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(60) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `break_minutes` smallint(6) NOT NULL DEFAULT 0,
  `crosses_midnight` tinyint(1) NOT NULL DEFAULT 0,
  `late_grace_minutes` smallint(6) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shifts_company_id_foreign` (`company_id`),
  CONSTRAINT `shifts_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roster_entries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `work_date` date NOT NULL,
  `shift_id` bigint(20) unsigned DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roster_entries_employee_id_work_date_unique` (`employee_id`,`work_date`),
  KEY `roster_entries_company_id_foreign` (`company_id`),
  KEY `roster_entries_shift_id_foreign` (`shift_id`),
  CONSTRAINT `roster_entries_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `roster_entries_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `roster_entries_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE attendance_records
  ADD COLUMN shift_id bigint(20) unsigned DEFAULT NULL AFTER company_id,
  ADD COLUMN scheduled_start time DEFAULT NULL AFTER shift_id,
  ADD COLUMN scheduled_end time DEFAULT NULL AFTER scheduled_start,
  ADD CONSTRAINT attendance_records_shift_id_foreign FOREIGN KEY (shift_id) REFERENCES shifts (id) ON DELETE SET NULL;

-- Shift default (dipakai AttendanceController::import() sebagai fallback saat
-- karyawan tidak punya roster hari itu — cari code='PAGI').
INSERT INTO shifts (company_id, code, name, start_time, end_time, break_minutes, crosses_midnight, late_grace_minutes, is_active, created_at, updated_at) VALUES
  (NULL, 'PAGI',  'Pagi (08:00–17:00)',  '08:00:00', '17:00:00', 60, 0, 15, 1, NOW(), NOW()),
  (NULL, 'SIANG', 'Siang (15:00–23:00)', '15:00:00', '23:00:00', 60, 0, 15, 1, NOW(), NOW()),
  (NULL, 'MALAM', 'Malam (23:00–07:00)', '23:00:00', '07:00:00', 60, 1, 15, 1, NOW(), NOW());

-- ── B2. Kebijakan Cuti (carry-forward / hangus + kuota per golongan) ────────

CREATE TABLE `leave_policies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `leave_type_id` bigint(20) unsigned NOT NULL,
  `level_id` bigint(20) unsigned DEFAULT NULL,
  `min_years_service` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `quota_days` decimal(5,1) NOT NULL,
  `carry_forward_max_days` decimal(5,1) NOT NULL DEFAULT 0.0,
  `carry_forward_expire_month` tinyint(3) unsigned NOT NULL DEFAULT 3,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_policies_company_id_foreign` (`company_id`),
  KEY `leave_policies_level_id_foreign` (`level_id`),
  KEY `leave_policies_leave_type_id_level_id_index` (`leave_type_id`,`level_id`),
  CONSTRAINT `leave_policies_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_policies_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_policies_level_id_foreign` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE leave_balances
  ADD COLUMN carried_days decimal(5,1) NOT NULL DEFAULT 0.0 AFTER used,
  ADD COLUMN carried_expires_on date DEFAULT NULL AFTER carried_days;

-- Kebijakan default (LeavePolicySeeder): Cuti Tahunan 12 hari/tahun semua PT &
-- semua level, boleh dibawa maks 6 hari, hangus akhir Maret tahun berikutnya.
INSERT INTO leave_policies (company_id, leave_type_id, level_id, min_years_service, quota_days, carry_forward_max_days, carry_forward_expire_month, is_active, created_at, updated_at)
SELECT NULL, lt.id, NULL, 0, 12, 6, 3, 1, NOW(), NOW()
FROM leave_types lt WHERE lt.name = 'Cuti Tahunan'
  AND NOT EXISTS (
    SELECT 1 FROM leave_policies lp
    WHERE lp.leave_type_id = lt.id AND lp.company_id IS NULL AND lp.level_id IS NULL AND lp.min_years_service = 0
  );

-- Scheduler tambahan (tambahkan di server, lihat routes/console.php):
--   leave:year-end        -> yearlyOn(1, 1, '02:00')  — alokasi saldo cuti tahun baru + carry-forward
--   leave:expire-carry    -> dailyAt('02:30')          — hanguskan carry yang lewat tanggal kadaluarsa

-- INSERT INTO migrations (migration, batch) VALUES
--   ('2026_08_29_180000_create_shift_and_roster_tables', <batch>),
--   ('2026_08_29_180100_create_leave_policies_table', <batch>);
