-- DEPLOY PRODUKSI HR — BAGIAN 2: TAHAP 18–34 (SETELAH deploy kode aplikasi)
-- Jalankan setelah: git pull main + composer install + config/route/view cache.
-- SEGERA setelah file ini selesai: `php artisan employees:encrypt-sensitive`
-- lalu seeder (PermissionCatalogSeeder terakhir), lalu `php artisan up`.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- TAHAP 18 — gap-closure-manual.sql
-- =============================================================================
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

-- ###########################################################################
-- >>> WAJIB SEGERA setelah TAHAP 18 (dan kode sudah live):
-- >>>   php artisan employees:encrypt-sensitive
-- >>> Meng-enkripsi NIK/NPWP/no. rekening yang sudah ada. Idempotent.
-- >>> Tanpa ini, Eloquent lempar DecryptException di semua halaman karyawan.
-- ###########################################################################



-- =============================================================================
-- TAHAP 19 — approval-status-normalisasi-manual.sql
-- =============================================================================
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


-- =============================================================================
-- TAHAP 20 — shift-leave-policy-manual.sql
-- =============================================================================
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


-- =============================================================================
-- TAHAP 21 — offboarding-letter-request-manual.sql
-- =============================================================================
-- =============================================================================
-- Modul: Clearance Resign (Offboarding) + Permintaan Surat Self-Service
-- Fase 2 HRD — keduanya dikelola HR langsung (tanpa Approval Engine).
-- Setara dengan migration:
--   2026_08_29_180200_create_offboarding_tables.php
--   2026_08_29_180300_create_letter_requests_table.php
-- WAJIB backup database dulu sebelum menjalankan file ini.
-- Jalankan SETELAH gap-closure-manual.sql (perlu employee_facilities, termination_requests)
-- dan letter templates sudah ada (employee-administration-manual.sql).
-- =============================================================================

-- ── C2. Clearance Resign (Offboarding) ──────────────────────────────────────
-- Mirror onboarding_checklist_items/employee_onboarding_tasks. Task
-- dimaterialisasi otomatis saat TerminationRequest dibuat (lihat
-- HrRequestController::store()) atau manual via "Mulai Clearance".

CREATE TABLE `offboarding_checklist_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `label` varchar(200) NOT NULL,
  `category` varchar(20) NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `offboarding_checklist_items_company_id_foreign` (`company_id`),
  CONSTRAINT `offboarding_checklist_items_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_offboarding_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `offboarding_checklist_item_id` bigint(20) unsigned NOT NULL,
  `is_done` tinyint(1) NOT NULL DEFAULT 0,
  `done_at` datetime DEFAULT NULL,
  `done_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_offboarding_task_unique` (`employee_id`,`offboarding_checklist_item_id`),
  KEY `employee_offboarding_tasks_offboarding_checklist_item_id_foreign` (`offboarding_checklist_item_id`),
  KEY `employee_offboarding_tasks_done_by_user_id_foreign` (`done_by_user_id`),
  CONSTRAINT `employee_offboarding_tasks_done_by_user_id_foreign` FOREIGN KEY (`done_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_offboarding_tasks_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_offboarding_tasks_offboarding_checklist_item_id_foreign` FOREIGN KEY (`offboarding_checklist_item_id`) REFERENCES `offboarding_checklist_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Item checklist default (OffboardingChecklistItemSeeder) — berlaku semua PT.
INSERT INTO offboarding_checklist_items (company_id, label, category, is_required, sort_order, is_active, created_at, updated_at) VALUES
  (NULL, 'Kembalikan laptop/aset kantor',     'aset',     1, 1, 1, NOW(), NOW()),
  (NULL, 'Kembalikan ID card & akses gedung', 'aset',     1, 2, 1, NOW(), NOW()),
  (NULL, 'Nonaktifkan email & akun sistem',   'akun',     1, 1, 1, NOW(), NOW()),
  (NULL, 'Serah terima pekerjaan',             'exit',     1, 1, 1, NOW(), NOW()),
  (NULL, 'Exit interview',                     'exit',     1, 2, 1, NOW(), NOW()),
  (NULL, 'Pelunasan kasbon/pinjaman',         'keuangan', 1, 1, 1, NOW(), NOW()),
  (NULL, 'Surat pengalaman kerja',             'dokumen',  1, 1, 1, NOW(), NOW());

-- ── C3. Permintaan Surat Self-Service ────────────────────────────────────────

ALTER TABLE letter_templates
  ADD COLUMN self_service tinyint(1) NOT NULL DEFAULT 0 AFTER is_active;

CREATE TABLE `letter_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `requested_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `letter_template_id` bigint(20) unsigned DEFAULT NULL,
  `purpose` varchar(60) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `employee_letter_id` bigint(20) unsigned DEFAULT NULL,
  `handled_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `handled_at` datetime DEFAULT NULL,
  `rejection_note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `letter_requests_employee_id_foreign` (`employee_id`),
  KEY `letter_requests_requested_by_user_id_foreign` (`requested_by_user_id`),
  KEY `letter_requests_letter_template_id_foreign` (`letter_template_id`),
  KEY `letter_requests_employee_letter_id_foreign` (`employee_letter_id`),
  KEY `letter_requests_handled_by_user_id_foreign` (`handled_by_user_id`),
  CONSTRAINT `letter_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `letter_requests_employee_letter_id_foreign` FOREIGN KEY (`employee_letter_id`) REFERENCES `employee_letters` (`id`) ON DELETE SET NULL,
  CONSTRAINT `letter_requests_handled_by_user_id_foreign` FOREIGN KEY (`handled_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `letter_requests_letter_template_id_foreign` FOREIGN KEY (`letter_template_id`) REFERENCES `letter_templates` (`id`) ON DELETE SET NULL,
  CONSTRAINT `letter_requests_requested_by_user_id_foreign` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Setelah ini, HR perlu mencentang "self_service" pada minimal 1 template surat
-- (mis. "Surat Keterangan Kerja") lewat halaman Template Surat supaya muncul
-- di form permintaan mandiri karyawan.

-- INSERT INTO migrations (migration, batch) VALUES
--   ('2026_08_29_180200_create_offboarding_tables', <batch>),
--   ('2026_08_29_180300_create_letter_requests_table', <batch>);


-- =============================================================================
-- TAHAP 22 — engagement-analytics-manual.sql
-- =============================================================================
-- =============================================================================
-- Modul: Pengumuman + Survey Engagement + HR Analytics (Biaya Rekrutmen)
-- Fase 2 HRD. Setara dengan migration:
--   2026_08_29_180400_create_announcements_tables.php
--   2026_08_29_180500_create_survey_tables.php
--   2026_08_29_180600_create_recruitment_costs_table.php
-- WAJIB backup database dulu sebelum menjalankan file ini.
-- Jalankan SETELAH master-organization-manual.sql & recruitment-manual.sql
-- (butuh companies, departments, job_requisitions ada).
-- =============================================================================

-- ── D1. Pengumuman / Broadcast / Papan Info ─────────────────────────────────

CREATE TABLE `announcements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `body` longtext NOT NULL,
  `category` varchar(20) NOT NULL DEFAULT 'info',
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `published_at` datetime DEFAULT NULL,
  `expires_at` date DEFAULT NULL,
  `attachment_path` varchar(500) DEFAULT NULL,
  `attachment_name` varchar(255) DEFAULT NULL,
  `created_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `announcements_company_id_foreign` (`company_id`),
  KEY `announcements_created_by_user_id_foreign` (`created_by_user_id`),
  CONSTRAINT `announcements_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `announcements_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `announcement_reads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `announcement_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `read_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `announcement_reads_announcement_id_user_id_unique` (`announcement_id`,`user_id`),
  KEY `announcement_reads_user_id_foreign` (`user_id`),
  CONSTRAINT `announcement_reads_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `announcement_reads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── D2. Survey Kepuasan / Engagement ─────────────────────────────────────────

CREATE TABLE `surveys` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `opens_at` date DEFAULT NULL,
  `closes_at` date DEFAULT NULL,
  `created_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `surveys_company_id_foreign` (`company_id`),
  KEY `surveys_created_by_user_id_foreign` (`created_by_user_id`),
  CONSTRAINT `surveys_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `surveys_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `survey_questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `survey_id` bigint(20) unsigned NOT NULL,
  `text` varchar(500) NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'scale',
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `survey_questions_survey_id_foreign` (`survey_id`),
  CONSTRAINT `survey_questions_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `survey_responses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `survey_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `employee_id` bigint(20) unsigned DEFAULT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `submitted_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `survey_responses_survey_id_foreign` (`survey_id`),
  KEY `survey_responses_user_id_foreign` (`user_id`),
  KEY `survey_responses_employee_id_foreign` (`employee_id`),
  KEY `survey_responses_company_id_foreign` (`company_id`),
  KEY `survey_responses_department_id_foreign` (`department_id`),
  CONSTRAINT `survey_responses_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `survey_responses_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `survey_responses_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `survey_responses_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE,
  CONSTRAINT `survey_responses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `survey_answers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `survey_response_id` bigint(20) unsigned NOT NULL,
  `survey_question_id` bigint(20) unsigned NOT NULL,
  `value` text DEFAULT NULL,
  `value_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`value_json`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `survey_answers_survey_response_id_foreign` (`survey_response_id`),
  KEY `survey_answers_survey_question_id_foreign` (`survey_question_id`),
  CONSTRAINT `survey_answers_survey_question_id_foreign` FOREIGN KEY (`survey_question_id`) REFERENCES `survey_questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `survey_answers_survey_response_id_foreign` FOREIGN KEY (`survey_response_id`) REFERENCES `survey_responses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── D3. HR Analytics — Biaya Rekrutmen (input metrik cost-per-hire) ─────────
-- Turnover rate, absenteeism rate, dan efektivitas training TIDAK butuh tabel
-- baru — dihitung langsung dari termination_requests, attendance_records, dan
-- training_participants yang sudah ada (lihat LaporanController::analytics()).

CREATE TABLE `recruitment_costs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `job_requisition_id` bigint(20) unsigned DEFAULT NULL,
  `category` varchar(40) NOT NULL,
  `amount` bigint(20) NOT NULL,
  `incurred_on` date NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recruitment_costs_company_id_foreign` (`company_id`),
  KEY `recruitment_costs_job_requisition_id_foreign` (`job_requisition_id`),
  KEY `recruitment_costs_created_by_foreign` (`created_by`),
  CONSTRAINT `recruitment_costs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `recruitment_costs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `recruitment_costs_job_requisition_id_foreign` FOREIGN KEY (`job_requisition_id`) REFERENCES `job_requisitions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tambah 4 modul baru ke tabel permissions (PermissionCatalogSeeder juga bisa
-- dijalankan ulang via `php artisan db:seed --class=PermissionCatalogSeeder`,
-- idempotent) — kalau server produksi tidak boleh jalankan artisan, insert manual:
INSERT IGNORE INTO permissions (name, guard_name, created_at, updated_at) VALUES
  ('shift.view','web',NOW(),NOW()), ('shift.create','web',NOW(),NOW()), ('shift.edit','web',NOW(),NOW()), ('shift.delete','web',NOW(),NOW()),
  ('offboarding.view','web',NOW(),NOW()), ('offboarding.create','web',NOW(),NOW()), ('offboarding.edit','web',NOW(),NOW()), ('offboarding.delete','web',NOW(),NOW()),
  ('announcement.view','web',NOW(),NOW()), ('announcement.create','web',NOW(),NOW()), ('announcement.edit','web',NOW(),NOW()), ('announcement.delete','web',NOW(),NOW()),
  ('survey.view','web',NOW(),NOW()), ('survey.create','web',NOW(),NOW()), ('survey.edit','web',NOW(),NOW()), ('survey.delete','web',NOW(),NOW());
-- Lalu assign ke role admin/hr_manager/hr_admin/super_admin lewat halaman
-- Role & Hak Akses (atau `php artisan db:seed --class=PermissionCatalogSeeder`).

-- INSERT INTO migrations (migration, batch) VALUES
--   ('2026_08_29_180400_create_announcements_tables', <batch>),
--   ('2026_08_29_180500_create_survey_tables', <batch>),
--   ('2026_08_29_180600_create_recruitment_costs_table', <batch>);


-- =============================================================================
-- TAHAP 23 — job-requisition-budget-control-manual.sql
-- =============================================================================
-- =============================================================================
-- Modul: Job Requisition — tipe permintaan + tautan Manpower Plan + Budget Control
--
-- Setara dengan migration:
--   2026_08_31_100000_add_request_type_and_mpp_to_job_requisitions
--
-- WAJIB backup dulu. Jalankan SETELAH recruitment-manual.sql (butuh job_requisitions)
-- dan manpower-planning-manual.sql (butuh manpower_plans).
--
-- Budget Control ada di kode (JobRequisition::budgetViolation()) — file ini hanya
-- menambah 3 kolom. Requisition tipe 'additional'/'new_position' hanya bisa diajukan
-- bila kuota Manpower Plan yang disetujui masih cukup
-- (planned_headcount − aktual − sedang direkrut ≥ headcount_requested).
-- =============================================================================

ALTER TABLE `job_requisitions`
  ADD COLUMN `request_type` varchar(20) NOT NULL DEFAULT 'replacement' AFTER `title`,
  ADD COLUMN `manpower_plan_id` bigint(20) unsigned DEFAULT NULL AFTER `request_type`,
  ADD COLUMN `replaces_employee_id` bigint(20) unsigned DEFAULT NULL AFTER `manpower_plan_id`,
  ADD CONSTRAINT `job_requisitions_manpower_plan_id_foreign`
      FOREIGN KEY (`manpower_plan_id`) REFERENCES `manpower_plans` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `job_requisitions_replaces_employee_id_foreign`
      FOREIGN KEY (`replaces_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

-- request_type: 'replacement' (pengganti, tidak menambah headcount)
--             | 'additional' (tambahan headcount — butuh kuota MPP)
--             | 'new_position' (posisi baru — butuh kuota MPP)
-- Baris lama otomatis 'replacement' (default) — aman, tidak kena Budget Control.

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_08_31_100000_add_request_type_and_mpp_to_job_requisitions', <BATCH>);
-- =============================================================================


-- =============================================================================
-- TAHAP 24 — candidate-database-ats-manual.sql
-- =============================================================================
-- =============================================================================
-- Modul: Candidate Database (ATS) — expected salary, hasil assessment, dan CV
--        terstruktur kandidat (pendidikan / pengalaman / skill / sertifikasi).
--
-- Setara dengan migration:
--   2026_08_31_110000_create_candidate_profile_tables
--
-- WAJIB backup dulu. Jalankan SETELAH recruitment-manual.sql (butuh `candidates`).
-- Saat kandidat dikonversi jadi Employee, data pendidikan/pengalaman/skill ikut
-- disalin ke employee_educations / employee_work_experiences / employee_skills
-- (logika di CandidateController::carryOverProfile()).
-- =============================================================================

ALTER TABLE `candidates`
  ADD COLUMN `expected_salary` bigint(20) DEFAULT NULL AFTER `source`,
  ADD COLUMN `assessment_result` varchar(20) DEFAULT NULL AFTER `mcu_result`,   -- pass/hold/fail
  ADD COLUMN `assessment_score` decimal(5,2) DEFAULT NULL AFTER `assessment_result`,
  ADD COLUMN `assessment_notes` text DEFAULT NULL AFTER `assessment_score`;

CREATE TABLE `candidate_educations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `education_level` varchar(50) DEFAULT NULL,
  `major` varchar(150) DEFAULT NULL,
  `institution` varchar(200) DEFAULT NULL,
  `graduation_year` year(4) DEFAULT NULL,
  `gpa` decimal(4,2) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `candidate_educations_candidate_id_foreign` (`candidate_id`),
  CONSTRAINT `candidate_educations_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidate_experiences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `job_title` varchar(150) DEFAULT NULL,
  `company_city` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `last_salary` bigint(20) DEFAULT NULL,
  `job_description` text DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `candidate_experiences_candidate_id_foreign` (`candidate_id`),
  CONSTRAINT `candidate_experiences_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidate_skills` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `proficiency` enum('basic','intermediate','advanced','expert') DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `candidate_skills_candidate_id_foreign` (`candidate_id`),
  CONSTRAINT `candidate_skills_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidate_certifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `name` varchar(200) NOT NULL,
  `issuer` varchar(150) DEFAULT NULL,
  `issued_date` date DEFAULT NULL,
  `expires_date` date DEFAULT NULL,
  `credential_id` varchar(100) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `candidate_certifications_candidate_id_foreign` (`candidate_id`),
  CONSTRAINT `candidate_certifications_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_08_31_110000_create_candidate_profile_tables', <BATCH>);
-- =============================================================================


-- =============================================================================
-- TAHAP 25 — preemployment-manual.sql
-- =============================================================================
-- =============================================================================
-- Modul: Pre-Employment (PRD Bab 3 modul #4) — data terstruktur + checklist
--        wajib yang dilengkapi SEBELUM kandidat "accepted" dikonversi jadi
--        Employee. Saat convert, data dipindah ke employees + employee_bank_accounts
--        + employee_nssf (logika di CandidateController).
--
-- Setara dengan migration:
--   2026_08_31_120000_create_preemployment_tables
--
-- WAJIB backup dulu. Jalankan SETELAH:
--   - candidate-database-ats-manual.sql (butuh `candidates`)
--   - master-data-manual.sql (butuh marital_statuses / religions / blood_types / banks)
-- =============================================================================

CREATE TABLE `candidate_preemployment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `gender` varchar(10) DEFAULT NULL,            -- L / P (sejajar employees.gender)
  `birth_place` varchar(100) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `marital_status_id` bigint(20) unsigned DEFAULT NULL,
  `religion_id` bigint(20) unsigned DEFAULT NULL,
  `blood_type_id` bigint(20) unsigned DEFAULT NULL,
  `ktp_number` text DEFAULT NULL,               -- ciphertext (cast encrypted)
  `npwp_number` text DEFAULT NULL,              -- ciphertext
  `ktp_address` varchar(255) DEFAULT NULL,
  `ktp_city` varchar(100) DEFAULT NULL,
  `domicile_address` varchar(255) DEFAULT NULL,
  `domicile_city` varchar(100) DEFAULT NULL,
  `bank_id` bigint(20) unsigned DEFAULT NULL,
  `bank_account_number` text DEFAULT NULL,      -- ciphertext
  `bank_account_holder` varchar(150) DEFAULT NULL,
  `bpjs_health_number` varchar(50) DEFAULT NULL,
  `bpjs_health_date` date DEFAULT NULL,
  `bpjs_employment_number` varchar(50) DEFAULT NULL,
  `bpjs_employment_date` date DEFAULT NULL,
  `emergency_contact_name` varchar(120) DEFAULT NULL,
  `emergency_contact_relation` varchar(60) DEFAULT NULL,
  `emergency_contact_phone` varchar(30) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `candidate_preemployment_candidate_id_unique` (`candidate_id`),
  KEY `candidate_preemployment_marital_status_id_foreign` (`marital_status_id`),
  KEY `candidate_preemployment_religion_id_foreign` (`religion_id`),
  KEY `candidate_preemployment_blood_type_id_foreign` (`blood_type_id`),
  KEY `candidate_preemployment_bank_id_foreign` (`bank_id`),
  CONSTRAINT `candidate_preemployment_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `candidate_preemployment_marital_status_id_foreign` FOREIGN KEY (`marital_status_id`) REFERENCES `marital_statuses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `candidate_preemployment_religion_id_foreign` FOREIGN KEY (`religion_id`) REFERENCES `religions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `candidate_preemployment_blood_type_id_foreign` FOREIGN KEY (`blood_type_id`) REFERENCES `blood_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `candidate_preemployment_bank_id_foreign` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `preemployment_checklist_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,   -- NULL = global
  `label` varchar(200) NOT NULL,
  `category` varchar(20) NOT NULL DEFAULT 'dokumen', -- dokumen/data/verifikasi
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `preemployment_checklist_items_company_id_foreign` (`company_id`),
  CONSTRAINT `preemployment_checklist_items_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidate_preemployment_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `preemployment_checklist_item_id` bigint(20) unsigned NOT NULL,
  `is_done` tinyint(1) NOT NULL DEFAULT 0,
  `done_at` datetime DEFAULT NULL,
  `done_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpt_candidate_item_unique` (`candidate_id`,`preemployment_checklist_item_id`),
  KEY `candidate_preemployment_tasks_preemployment_checklist_item_id_foreign` (`preemployment_checklist_item_id`),
  KEY `candidate_preemployment_tasks_done_by_user_id_foreign` (`done_by_user_id`),
  CONSTRAINT `candidate_preemployment_tasks_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `candidate_preemployment_tasks_item_foreign` FOREIGN KEY (`preemployment_checklist_item_id`) REFERENCES `preemployment_checklist_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `candidate_preemployment_tasks_done_by_user_id_foreign` FOREIGN KEY (`done_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Item checklist default (global) ─────────────────────────────────────────
INSERT INTO `preemployment_checklist_items` (`company_id`,`label`,`category`,`is_required`,`sort_order`,`is_active`,`created_at`,`updated_at`) VALUES
(NULL,'Offer diterima & ditandatangani','verifikasi',1,10,1,NOW(),NOW()),
(NULL,'Data pribadi lengkap (TTL, alamat, dll)','data',1,20,1,NOW(),NOW()),
(NULL,'KTP','dokumen',1,30,1,NOW(),NOW()),
(NULL,'NPWP','dokumen',1,40,1,NOW(),NOW()),
(NULL,'Kartu Keluarga','dokumen',1,50,1,NOW(),NOW()),
(NULL,'Rekening bank (bank, no. rek, a.n.)','data',1,60,1,NOW(),NOW()),
(NULL,'Data BPJS Kesehatan','data',0,70,1,NOW(),NOW()),
(NULL,'Data BPJS Ketenagakerjaan','data',0,80,1,NOW(),NOW()),
(NULL,'Ijazah & transkrip nilai','dokumen',1,90,1,NOW(),NOW()),
(NULL,'SKCK','dokumen',0,100,1,NOW(),NOW()),
(NULL,'Surat pengalaman kerja / paklaring','dokumen',0,110,1,NOW(),NOW()),
(NULL,'Hasil Medical Check-Up (MCU)','dokumen',1,120,1,NOW(),NOW()),
(NULL,'Pas foto','dokumen',1,130,1,NOW(),NOW());

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_08_31_120000_create_preemployment_tables', <BATCH>);
-- =============================================================================


-- =============================================================================
-- TAHAP 26 — employee-document-management-manual.sql
-- =============================================================================
-- =============================================================================
-- Modul: Employee Document Management (Employee Digital File) + Exit Process
--
-- Setara dengan migration:
--   2026_08_31_140000_add_group_to_employee_documents
--   2026_08_31_150000_add_exit_process_to_termination_requests
--
-- WAJIB backup dulu. Jalankan SETELAH tabel `employee_documents` (baseline lama,
-- lihat employee-master-data-manual.sql) dan `termination_requests`
-- (approval-engine-manual.sql) sudah ada.
--
-- Katalog doc_type baru (KK, Bank Rekening, Job Description, Amandemen Kontrak,
-- Surat Promosi/Mutasi/Peringatan, Sertifikat Training/Kompetensi, Hasil Assessment,
-- Surat Pengunduran Diri, Berita Acara Clearance, Final Settlement) HANYA di kode
-- (App\Models\EmployeeDocument::$docTypes) — tidak perlu tabel referensi, doc_type
-- tetap varchar bebas seperti sebelumnya.
-- =============================================================================

-- ── Employee Digital File — kelompokkan dokumen ke 5 folder ─────────────────
ALTER TABLE `employee_documents`
  ADD COLUMN `group` varchar(20) NOT NULL DEFAULT 'lainnya' AFTER `doc_type`;

UPDATE `employee_documents` SET `group` = 'personal'
  WHERE `doc_type` IN ('KTP','KK','NPWP','BPJS Kesehatan','BPJS TK','Bank Rekening','SIM','Paspor','KITAS','IMTA','CV');
UPDATE `employee_documents` SET `group` = 'employment'
  WHERE `doc_type` IN ('Kontrak Kerja','Job Description','Amandemen Kontrak','SK Pengangkatan','SK Perpanjangan');
UPDATE `employee_documents` SET `group` = 'movement'
  WHERE `doc_type` IN ('Surat Promosi','Surat Mutasi','Surat Peringatan');
UPDATE `employee_documents` SET `group` = 'development'
  WHERE `doc_type` IN ('Ijazah','Transkrip','Sertifikasi','Sertifikat Training','Sertifikat Kompetensi','Hasil Assessment');
UPDATE `employee_documents` SET `group` = 'exit'
  WHERE `doc_type` IN ('Surat Pengunduran Diri','Berita Acara Clearance','Final Settlement');

-- ── Exit Process — Exit Interview & Final Settlement per proses keluar ───────
ALTER TABLE `termination_requests`
  ADD COLUMN `exit_interview_date` date DEFAULT NULL AFTER `effective_date`,
  ADD COLUMN `exit_interview_notes` text DEFAULT NULL AFTER `exit_interview_date`,
  ADD COLUMN `exit_interview_by_user_id` bigint(20) unsigned DEFAULT NULL AFTER `exit_interview_notes`,
  ADD COLUMN `final_settlement_amount` bigint(20) DEFAULT NULL AFTER `exit_interview_by_user_id`,
  ADD COLUMN `final_settlement_date` date DEFAULT NULL AFTER `final_settlement_amount`,
  ADD COLUMN `final_settlement_notes` text DEFAULT NULL AFTER `final_settlement_date`,
  ADD CONSTRAINT `termination_requests_exit_interview_by_user_id_foreign`
      FOREIGN KEY (`exit_interview_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- =============================================================================
-- Catatan: document expiry alert (command `documents:remind`, kolom
-- employee_documents.expires_at) SUDAH ADA sejak sebelumnya — tidak berubah.
--
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_08_31_140000_add_group_to_employee_documents', <BATCH>),
--   ('2026_08_31_150000_add_exit_process_to_termination_requests', <BATCH>);
-- =============================================================================


-- =============================================================================
-- TAHAP 27 — performance-management-extras-manual.sql
-- =============================================================================
-- =============================================================================
-- Modul: Performance Management — pelengkap (Continuous Feedback/1-on-1,
--        OKR cascading, 360° Feedback), di luar appraisal formal KPI yang sudah ada.
--
-- Setara dengan migration:
--   2026_08_31_170000_create_performance_extras_tables
--
-- WAJIB backup dulu. Jalankan SETELAH appraisal-kpi-manual.sql (butuh tabel
-- `appraisal_objectives`) dan master-organization-manual.sql (butuh `departments`).
-- =============================================================================

-- ── 1-on-1 / Continuous Feedback ─────────────────────────────────────────────
CREATE TABLE `performance_checkins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `created_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `checkin_date` date NOT NULL,
  `notes` text NOT NULL,
  `action_items` text DEFAULT NULL,
  `employee_comment` text DEFAULT NULL,
  `next_checkin_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `performance_checkins_employee_id_foreign` (`employee_id`),
  KEY `performance_checkins_created_by_user_id_foreign` (`created_by_user_id`),
  CONSTRAINT `performance_checkins_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `performance_checkins_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── OKR — Sasaran Perusahaan/Departemen berjenjang ──────────────────────────
CREATE TABLE `company_objectives` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `parent_objective_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `year` smallint(5) unsigned NOT NULL,
  `quarter` tinyint(3) unsigned DEFAULT NULL,
  `owner_employee_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company_objectives_company_id_foreign` (`company_id`),
  KEY `company_objectives_department_id_foreign` (`department_id`),
  KEY `company_objectives_parent_objective_id_foreign` (`parent_objective_id`),
  KEY `company_objectives_owner_employee_id_foreign` (`owner_employee_id`),
  KEY `company_objectives_created_by_user_id_foreign` (`created_by_user_id`),
  CONSTRAINT `company_objectives_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `company_objectives_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `company_objectives_parent_objective_id_foreign` FOREIGN KEY (`parent_objective_id`) REFERENCES `company_objectives` (`id`) ON DELETE SET NULL,
  CONSTRAINT `company_objectives_owner_employee_id_foreign` FOREIGN KEY (`owner_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `company_objectives_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `appraisal_objectives`
  ADD COLUMN `company_objective_id` bigint(20) unsigned DEFAULT NULL AFTER `appraisal_id`,
  ADD CONSTRAINT `appraisal_objectives_company_objective_id_foreign`
      FOREIGN KEY (`company_objective_id`) REFERENCES `company_objectives` (`id`) ON DELETE SET NULL;
-- Catatan: kolom ini SUDAH bisa dipakai lewat query/API, tapi form Edit Appraisal
-- (resources/views/appraisal/appraisal/edit.blade.php) BELUM ada dropdown-nya —
-- OKR sekarang berjalan sebagai tracker berjenjang berdiri sendiri (progres per
-- sasaran = "Belum ada KPI tertaut" sampai penautan lewat UI dikerjakan menyusul).

-- ── 360° Feedback ────────────────────────────────────────────────────────────
CREATE TABLE `feedback_360_cycles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `title` varchar(150) NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `created_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `feedback_360_cycles_company_id_foreign` (`company_id`),
  KEY `feedback_360_cycles_created_by_user_id_foreign` (`created_by_user_id`),
  CONSTRAINT `feedback_360_cycles_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `feedback_360_cycles_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `feedback_360_reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cycle_id` bigint(20) unsigned NOT NULL,
  `subject_employee_id` bigint(20) unsigned NOT NULL,
  `rater_employee_id` bigint(20) unsigned NOT NULL,
  `relation_type` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `submitted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `f360_review_unique` (`cycle_id`,`subject_employee_id`,`rater_employee_id`),
  KEY `feedback_360_reviews_subject_employee_id_foreign` (`subject_employee_id`),
  KEY `feedback_360_reviews_rater_employee_id_foreign` (`rater_employee_id`),
  CONSTRAINT `feedback_360_reviews_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `feedback_360_cycles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `feedback_360_reviews_subject_employee_id_foreign` FOREIGN KEY (`subject_employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `feedback_360_reviews_rater_employee_id_foreign` FOREIGN KEY (`rater_employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `feedback_360_answers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `review_id` bigint(20) unsigned NOT NULL,
  `question_key` varchar(60) NOT NULL,
  `rating` tinyint(3) unsigned DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `f360_answer_unique` (`review_id`,`question_key`),
  CONSTRAINT `feedback_360_answers_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `feedback_360_reviews` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Pertanyaan kompetensi (communication/teamwork/quality/reliability/leadership/
-- problem_solving) TETAP, didefinisikan di App\Models\Appraisal\Feedback360Review::$questions
-- (bukan tabel referensi) — supaya skema tetap ringkas.

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_08_31_170000_create_performance_extras_tables', <BATCH>);
-- =============================================================================

-- =============================================================================
-- TAHAP 29 — compensation-succession-survey-extras-manual.sql
-- =============================================================================
-- =============================================================================
-- Modul: 3 area "Kematangan HR" lanjutan — Compensation (benchmark gaji per
--        Level), Succession Planning (jabatan kritikal + talent pool), Survey
--        pulse & eNPS (surveys.type). Skema saja — 1 migrasi utk 3 area.
--
-- Setara dengan migration:
--   2026_08_31_180000_create_compensation_succession_survey_extras
--
-- WAJIB backup dulu. Jalankan SETELAH:
--   - master-organization-manual.sql (butuh `levels`)
--   - master-data-manual.sql / migrasi awal (butuh `positions`, `employees`, `users`)
--   - engagement-analytics-manual.sql (butuh `surveys`)
-- =============================================================================

CREATE TABLE `salary_benchmarks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `level_id` bigint(20) unsigned NOT NULL,
  `market_min` bigint(20) unsigned NOT NULL,
  `market_mid` bigint(20) unsigned NOT NULL,
  `market_max` bigint(20) unsigned NOT NULL,
  `source` varchar(150) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `updated_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `salary_benchmarks_level_id_unique` (`level_id`),
  KEY `salary_benchmarks_updated_by_user_id_foreign` (`updated_by_user_id`),
  CONSTRAINT `salary_benchmarks_level_id_foreign` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`) ON DELETE CASCADE,
  CONSTRAINT `salary_benchmarks_updated_by_user_id_foreign` FOREIGN KEY (`updated_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `positions`
  ADD COLUMN `is_critical_position` tinyint(1) NOT NULL DEFAULT 0 AFTER `tarif_lembur`,
  ADD COLUMN `succession_risk` varchar(10) DEFAULT NULL AFTER `is_critical_position`,
  ADD COLUMN `succession_notes` text DEFAULT NULL AFTER `succession_risk`;

CREATE TABLE `talent_pool_members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `position_id` bigint(20) unsigned NOT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `readiness` varchar(20) NOT NULL,             -- ready_now/ready_1_2yr/ready_3_5yr/development
  `development_notes` text DEFAULT NULL,
  `added_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `talent_pool_unique` (`position_id`,`employee_id`),
  KEY `talent_pool_members_employee_id_foreign` (`employee_id`),
  KEY `talent_pool_members_added_by_user_id_foreign` (`added_by_user_id`),
  CONSTRAINT `talent_pool_members_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `talent_pool_members_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `talent_pool_members_added_by_user_id_foreign` FOREIGN KEY (`added_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `surveys`
  ADD COLUMN `type` varchar(20) NOT NULL DEFAULT 'standard' AFTER `is_anonymous`; -- standard/pulse/enps

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_08_31_180000_create_compensation_succession_survey_extras', <BATCH>);
-- =============================================================================


-- =============================================================================
-- TAHAP 30 — notification-talent-extras-manual.sql
-- =============================================================================
-- =============================================================================
-- Modul: 4 gap tambahan hasil review kematangan HR lanjutan —
--        (1) Notification Center (bel notifikasi in-app, tabel `notifications`
--            standar Laravel — User sudah pakai trait Notifiable dari awal),
--        (2) Struktur Gaji Internal / Salary Grade per Level (beda dari
--            salary_benchmarks yang acuan pasar eksternal),
--        (3) Grid 9-Kotak — `employees.potential_rating` dkk (sumbu performa
--            dari total_score Appraisal terakhir, tidak butuh kolom baru),
--        (4) Recognition / Kudos — apresiasi non-finansial antar karyawan.
--
-- Setara dengan migration:
--   2026_09_01_100000_create_notification_and_talent_extras
--
-- WAJIB backup dulu. Jalankan SETELAH:
--   - master-organization-manual.sql (butuh `levels`)
--   - migrasi dasar (butuh `companies`, `employees`, `users`)
-- =============================================================================

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `salary_grades` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,   -- NULL = berlaku semua PT (global/fallback)
  `level_id` bigint(20) unsigned NOT NULL,
  `grade_min` bigint(20) unsigned NOT NULL,
  `grade_mid` bigint(20) unsigned NOT NULL,
  `grade_max` bigint(20) unsigned NOT NULL,
  `notes` text DEFAULT NULL,
  `updated_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_grades_company_id_foreign` (`company_id`),
  KEY `salary_grades_level_id_foreign` (`level_id`),
  KEY `salary_grades_updated_by_user_id_foreign` (`updated_by_user_id`),
  CONSTRAINT `salary_grades_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `salary_grades_level_id_foreign` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`) ON DELETE CASCADE,
  CONSTRAINT `salary_grades_updated_by_user_id_foreign` FOREIGN KEY (`updated_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `employees`
  ADD COLUMN `potential_rating` varchar(10) DEFAULT NULL AFTER `is_active`,           -- low/medium/high
  ADD COLUMN `potential_notes` text DEFAULT NULL AFTER `potential_rating`,
  ADD COLUMN `potential_assessed_at` date DEFAULT NULL AFTER `potential_notes`,
  ADD COLUMN `potential_assessed_by_user_id` bigint(20) unsigned DEFAULT NULL AFTER `potential_assessed_at`,
  ADD CONSTRAINT `employees_potential_assessed_by_user_id_foreign`
    FOREIGN KEY (`potential_assessed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

CREATE TABLE `kudos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `from_employee_id` bigint(20) unsigned NOT NULL,
  `to_employee_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `category` varchar(30) NOT NULL,   -- teamwork/innovation/leadership/customer_focus/integrity/excellence
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kudos_from_employee_id_foreign` (`from_employee_id`),
  KEY `kudos_to_employee_id_foreign` (`to_employee_id`),
  KEY `kudos_company_id_foreign` (`company_id`),
  CONSTRAINT `kudos_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `kudos_from_employee_id_foreign` FOREIGN KEY (`from_employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kudos_to_employee_id_foreign` FOREIGN KEY (`to_employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_09_01_100000_create_notification_and_talent_extras', <BATCH>);
-- =============================================================================


-- =============================================================================
-- TAHAP 31 — gap4-extras-manual.sql
-- =============================================================================
-- =============================================================================
-- Modul: 7 gap lanjutan hasil review kematangan HR lebih dalam —
--        Notification Center (perluas cakupan, TANPA skema baru di sini),
--        Merit Increase (salary_increase_requests, lewat Approval Engine),
--        Employee Referral (candidates.referred_by_employee_id dkk),
--        Probation Review (probation_reviews),
--        Kalender Cuti Tim (TANPA skema baru — read-only dari leave_requests),
--        Riwayat Grid 9-Kotak & Struktur Gaji (employee_potential_history,
--        salary_grade_history), Pulse Survey auto-recurring (surveys.recurrence
--        + parent_survey_id).
--
-- Setara dengan migration:
--   2026_09_01_110000_create_gap4_extras
--
-- WAJIB backup dulu. Jalankan SETELAH:
--   - migrasi dasar (butuh `employees`, `companies`, `users`)
--   - master-organization-manual.sql (butuh `levels`)
--   - candidate-database-ats-manual.sql (butuh `candidates`)
--   - compensation-succession-survey-extras-manual.sql (butuh `surveys.type`,
--     kolom `surveys` sudah ada duluan)
-- =============================================================================

CREATE TABLE `salary_increase_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `requested_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `current_salary` bigint(20) unsigned DEFAULT NULL,
  `proposed_salary` bigint(20) unsigned NOT NULL,
  `effective_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',   -- draft/pending/approved/rejected/cancelled
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_increase_requests_employee_id_foreign` (`employee_id`),
  KEY `salary_increase_requests_company_id_foreign` (`company_id`),
  KEY `salary_increase_requests_requested_by_user_id_foreign` (`requested_by_user_id`),
  CONSTRAINT `salary_increase_requests_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `salary_increase_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `salary_increase_requests_requested_by_user_id_foreign` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `candidates`
  ADD COLUMN `referred_by_employee_id` bigint(20) unsigned DEFAULT NULL AFTER `source`,
  ADD COLUMN `referral_bonus_amount` bigint(20) unsigned DEFAULT NULL AFTER `referred_by_employee_id`,
  ADD COLUMN `referral_bonus_paid_at` date DEFAULT NULL AFTER `referral_bonus_amount`,
  ADD CONSTRAINT `candidates_referred_by_employee_id_foreign`
    FOREIGN KEY (`referred_by_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

CREATE TABLE `probation_reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `review_date` date NOT NULL,
  `decision` varchar(20) NOT NULL,   -- passed/extended/failed
  `performance_notes` text DEFAULT NULL,
  `extended_until` date DEFAULT NULL,
  `reviewed_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `probation_reviews_employee_id_foreign` (`employee_id`),
  KEY `probation_reviews_reviewed_by_user_id_foreign` (`reviewed_by_user_id`),
  CONSTRAINT `probation_reviews_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `probation_reviews_reviewed_by_user_id_foreign` FOREIGN KEY (`reviewed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_potential_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `potential_rating` varchar(10) NOT NULL,   -- low/medium/high
  `notes` text DEFAULT NULL,
  `assessed_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `assessed_at` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_potential_history_employee_id_foreign` (`employee_id`),
  KEY `employee_potential_history_assessed_by_user_id_foreign` (`assessed_by_user_id`),
  CONSTRAINT `employee_potential_history_assessed_by_user_id_foreign` FOREIGN KEY (`assessed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_potential_history_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `salary_grade_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `level_id` bigint(20) unsigned NOT NULL,
  `grade_min` bigint(20) unsigned NOT NULL,
  `grade_mid` bigint(20) unsigned NOT NULL,
  `grade_max` bigint(20) unsigned NOT NULL,
  `changed_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_grade_history_company_id_foreign` (`company_id`),
  KEY `salary_grade_history_level_id_foreign` (`level_id`),
  KEY `salary_grade_history_changed_by_user_id_foreign` (`changed_by_user_id`),
  CONSTRAINT `salary_grade_history_changed_by_user_id_foreign` FOREIGN KEY (`changed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `salary_grade_history_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `salary_grade_history_level_id_foreign` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `surveys`
  ADD COLUMN `recurrence` varchar(20) NOT NULL DEFAULT 'none' AFTER `type`,   -- none/monthly/quarterly
  ADD COLUMN `parent_survey_id` bigint(20) unsigned DEFAULT NULL AFTER `recurrence`,
  ADD CONSTRAINT `surveys_parent_survey_id_foreign`
    FOREIGN KEY (`parent_survey_id`) REFERENCES `surveys` (`id`) ON DELETE SET NULL;

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_09_01_110000_create_gap4_extras', <BATCH>);
-- =============================================================================

-- =============================================================================
-- SETELAH kode live, jalankan ulang seeder berikut (idempoten, aman diulang):
--   php artisan db:seed --class=ApprovalWorkflowSeeder
-- (menambahkan workflow default 'salary_increase_request' — direct_manager →
--  hr_manager → ceo — untuk 3 PT, TANPA mengubah workflow existing lain)
-- =============================================================================


-- =============================================================================
-- TAHAP 32 — mitra-employee-type-manual.sql
-- =============================================================================
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


-- =============================================================================
-- TAHAP 33 — fix-level-rank-manual.sql
-- =============================================================================
-- =============================================================================
-- Perbaikan data: hierarki levels.rank supaya Bagan Organisasi urut benar.
-- Org chart sort tier pakai `rank` ASC (1 = paling senior, lihat placeholder
-- form Edit Level). Data awal Direksi/Manager/SPV/Admin semua rank=99 (seri) --
-- akibatnya CEO (Direksi) tidak tampil lebih tinggi dari Manager/SPV di bagan.
--
-- Setara dengan migration:
--   2026_09_08_100000_fix_level_rank_hierarchy
--
-- Aman dijalankan berkali-kali (idempoten). Cocokkan dulu nama Level di server
-- (kalau namanya beda dari default, sesuaikan WHERE name=... di bawah).
-- =============================================================================

UPDATE `levels` SET `rank` = 1 WHERE `name` = 'Direksi';
UPDATE `levels` SET `rank` = 2 WHERE `name` = 'Manager';
UPDATE `levels` SET `rank` = 3 WHERE `name` = 'SPV';
UPDATE `levels` SET `rank` = 4 WHERE `name` = 'Senior Staff';
UPDATE `levels` SET `rank` = 5 WHERE `name` = 'Staff';
UPDATE `levels` SET `rank` = 6 WHERE `name` = 'Admin';

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_09_08_100000_fix_level_rank_hierarchy', <BATCH>);
-- =============================================================================


-- =============================================================================
-- TAHAP 34 — tds-bod-commercial-logistik-positions-manual.sql
-- =============================================================================
-- =============================================================================
-- Isi Jabatan dasar utk Divisi BOD/Commercial/Logistik milik PT. Tridaya
-- Selaras (TDS) — sebelumnya kosong (0 Jabatan), jadi bagan organisasi tidak
-- bisa menampilkan "Commercial/Logistik lapor ke BOD" karena tidak ada
-- Jabatan sama sekali utk direkrut/ditempatkan.
--
-- Setara dengan migration:
--   2026_09_08_110000_add_tds_bod_commercial_logistik_positions
--
-- WAJIB backup dulu. Sesuaikan dulu nama Company/Departemen/Level di server
-- kalau beda dari default ('PT. Tridaya Selaras', 'BOD', 'COMMERCIAL TDS',
-- 'LOGISTIK TDS', 'Direksi', 'Manager', 'SPV', 'Senior Staff', 'Staff') —
-- query di bawah pakai subquery by name, BUKAN id hardcode, supaya aman
-- dijalankan di server walau id-nya beda dari lokal.
-- =============================================================================

SET @company_id = (SELECT id FROM companies WHERE name = 'PT. Tridaya Selaras' LIMIT 1);
SET @dept_bod = (SELECT id FROM departments WHERE company_id = @company_id AND name = 'BOD' LIMIT 1);
SET @dept_commercial = (SELECT id FROM departments WHERE company_id = @company_id AND name = 'COMMERCIAL TDS' LIMIT 1);
SET @dept_logistik = (SELECT id FROM departments WHERE company_id = @company_id AND name = 'LOGISTIK TDS' LIMIT 1);

SET @lvl_direksi = (SELECT id FROM levels WHERE name = 'Direksi' LIMIT 1);
SET @lvl_manager = (SELECT id FROM levels WHERE name = 'Manager' LIMIT 1);
SET @lvl_spv = (SELECT id FROM levels WHERE name = 'SPV' LIMIT 1);
SET @lvl_senior_staff = (SELECT id FROM levels WHERE name = 'Senior Staff' LIMIT 1);
SET @lvl_staff = (SELECT id FROM levels WHERE name = 'Staff' LIMIT 1);

INSERT INTO `positions` (`company_id`, `department_id`, `level_id`, `code`, `name`, `tunjangan_jabatan`, `is_active`, `created_at`, `updated_at`)
SELECT * FROM (SELECT
    @company_id, @dept_bod, @lvl_direksi, 'BOD-DIR', 'Direktur Utama', 20000000, 1, NOW(), NOW()
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `positions` WHERE `company_id` = @company_id AND `code` = 'BOD-DIR');

INSERT INTO `positions` (`company_id`, `department_id`, `level_id`, `code`, `name`, `tunjangan_jabatan`, `is_active`, `created_at`, `updated_at`)
SELECT * FROM (SELECT @company_id, @dept_commercial, @lvl_manager, 'COMM-TDS-MGR', 'Manager Commercial TDS', 5000000, 1, NOW(), NOW()) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `positions` WHERE `company_id` = @company_id AND `code` = 'COMM-TDS-MGR');

INSERT INTO `positions` (`company_id`, `department_id`, `level_id`, `code`, `name`, `tunjangan_jabatan`, `is_active`, `created_at`, `updated_at`)
SELECT * FROM (SELECT @company_id, @dept_commercial, @lvl_spv, 'COMM-TDS-SPV', 'SPV Commercial TDS', 2500000, 1, NOW(), NOW()) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `positions` WHERE `company_id` = @company_id AND `code` = 'COMM-TDS-SPV');

INSERT INTO `positions` (`company_id`, `department_id`, `level_id`, `code`, `name`, `tunjangan_jabatan`, `is_active`, `created_at`, `updated_at`)
SELECT * FROM (SELECT @company_id, @dept_commercial, @lvl_senior_staff, 'COMM-TDS-SST', 'Senior Staff Commercial TDS', 1200000, 1, NOW(), NOW()) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `positions` WHERE `company_id` = @company_id AND `code` = 'COMM-TDS-SST');

INSERT INTO `positions` (`company_id`, `department_id`, `level_id`, `code`, `name`, `tunjangan_jabatan`, `is_active`, `created_at`, `updated_at`)
SELECT * FROM (SELECT @company_id, @dept_commercial, @lvl_staff, 'COMM-TDS-STF', 'Staff Commercial TDS', 500000, 1, NOW(), NOW()) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `positions` WHERE `company_id` = @company_id AND `code` = 'COMM-TDS-STF');

INSERT INTO `positions` (`company_id`, `department_id`, `level_id`, `code`, `name`, `tunjangan_jabatan`, `is_active`, `created_at`, `updated_at`)
SELECT * FROM (SELECT @company_id, @dept_logistik, @lvl_manager, 'LOG-TDS-MGR', 'Manager Logistik TDS', 5000000, 1, NOW(), NOW()) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `positions` WHERE `company_id` = @company_id AND `code` = 'LOG-TDS-MGR');

INSERT INTO `positions` (`company_id`, `department_id`, `level_id`, `code`, `name`, `tunjangan_jabatan`, `is_active`, `created_at`, `updated_at`)
SELECT * FROM (SELECT @company_id, @dept_logistik, @lvl_spv, 'LOG-TDS-SPV', 'SPV Logistik TDS', 2500000, 1, NOW(), NOW()) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `positions` WHERE `company_id` = @company_id AND `code` = 'LOG-TDS-SPV');

INSERT INTO `positions` (`company_id`, `department_id`, `level_id`, `code`, `name`, `tunjangan_jabatan`, `is_active`, `created_at`, `updated_at`)
SELECT * FROM (SELECT @company_id, @dept_logistik, @lvl_senior_staff, 'LOG-TDS-SST', 'Senior Staff Logistik TDS', 1200000, 1, NOW(), NOW()) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `positions` WHERE `company_id` = @company_id AND `code` = 'LOG-TDS-SST');

INSERT INTO `positions` (`company_id`, `department_id`, `level_id`, `code`, `name`, `tunjangan_jabatan`, `is_active`, `created_at`, `updated_at`)
SELECT * FROM (SELECT @company_id, @dept_logistik, @lvl_staff, 'LOG-TDS-STF', 'Staff Logistik TDS', 500000, 1, NOW(), NOW()) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM `positions` WHERE `company_id` = @company_id AND `code` = 'LOG-TDS-STF');

-- Manager Commercial TDS & Manager Logistik TDS lapor ke Direktur Utama
-- (dipakai halaman Jabatan; garis di bagan organisasi sendiri ikut
-- Employee.manager_id saat masing2 posisi ini direkrut/diisi).
UPDATE `positions`
SET `reports_to_position_id` = (SELECT id FROM `positions` WHERE `company_id` = @company_id AND `code` = 'BOD-DIR')
WHERE `company_id` = @company_id AND `code` IN ('COMM-TDS-MGR', 'LOG-TDS-MGR');

-- =============================================================================
-- Opsional daftarkan ke tabel migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
--   ('2026_09_08_110000_add_tds_bod_commercial_logistik_positions', <BATCH>);
-- =============================================================================



-- ###########################################################################
-- >>> SETELAH SEMUA SQL: jalankan seeder (butuh php artisan):
-- >>>   php artisan db:seed --class=RegionSeeder
-- >>>   php artisan db:seed --class=RegionDistrictSeeder
-- >>>   php artisan db:seed --class=ApprovalWorkflowSeeder
-- >>>   php artisan db:seed --class=CompetencySeeder
-- >>>   php artisan db:seed --class=LeavePolicySeeder
-- >>>   php artisan db:seed --class=OnboardingChecklistItemSeeder    (skip kalau TAHAP 12 sudah insert)
-- >>>   php artisan db:seed --class=OffboardingChecklistItemSeeder
-- >>>   php artisan db:seed --class=PreEmploymentChecklistItemSeeder  (skip kalau TAHAP 25 sudah insert)
-- >>>   php artisan db:seed --class=PermissionCatalogSeeder   (TERAKHIR)
-- >>> Lalu: cron `* * * * * php artisan schedule:run`, `php artisan up`.
-- ###########################################################################


SET FOREIGN_KEY_CHECKS = 1;
