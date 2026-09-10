-- Dipecah dari database/deploy-all-produksi.sql — jalankan HANYA tahap yang belum ada di prod.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- TAHAP 6 — role-permission-manual.sql
-- =============================================================================
-- ============================================================================
-- Modul: Role & Permission granular per modul + per company (PRD HRIS v1.2 Bab 4)
--
-- Setara dengan migration 2026_08_28_100501_create_role_company_assignments_table.
--
-- Tabel `permissions`/`roles`/`model_has_roles`/`role_has_permissions` (spatie)
-- SUDAH ADA dari migrasi awal (2026_06_16_073113_create_permission_tables) — tidak
-- perlu CREATE TABLE lagi di sini, cuma diisi datanya lewat seeder di bawah.
--
-- WAJIB backup dulu. Jalankan berurutan.
-- ============================================================================

CREATE TABLE `role_company_assignments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `role_company_assignments_role_id_foreign` (`role_id`),
  KEY `role_company_assignments_company_id_foreign` (`company_id`),
  KEY `role_company_assignments_user_id_role_id_index` (`user_id`,`role_id`),
  CONSTRAINT `role_company_assignments_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_company_assignments_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_company_assignments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Setelah tabel dibuat, WAJIB jalankan seeder berikut (data permission tidak
-- praktis ditulis manual — ada di App\Support\PermissionCatalog + mapping role
-- lama->permission di database/seeders/PermissionCatalogSeeder.php):
--
--   php artisan db:seed --class=PermissionCatalogSeeder
--
-- Seeder ini idempoten (aman dijalankan ulang), dan otomatis:
--   1. generate semua baris `permissions` (module.action);
--   2. petakan 8 role lama -> permission (persis akses efektif sebelum migrasi);
--   3. tambah role baru PRD (super_admin, hr_group_admin, hr_admin, hr_payroll,
--      recruiter) dengan mapping default;
--   4. backfill role_company_assignments dari model_has_roles saat ini
--      (company_id NULL = semua company) supaya tidak ada user yang makin
--      terbatas dari kondisi sekarang.
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_100501_create_role_company_assignments_table',<BATCH>);
-- ============================================================================


-- =============================================================================

SET FOREIGN_KEY_CHECKS = 1;
