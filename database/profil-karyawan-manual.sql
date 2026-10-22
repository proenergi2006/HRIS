-- ============================================================================
-- Profil karyawan lanjutan + riwayat perubahan (Okt 2026)
--
-- Setara dengan migration:
--   2026_10_22_100000_add_contact_sim_guarantee_to_employees_table
--   2026_10_22_110000_create_employee_histories_table
--
-- WAJIB backup dulu. Jalankan SEKALI (ALTER ADD COLUMN gagal bila kolom sudah ada).
-- Prasyarat: tabel `employees` dan `users` sudah ada.
-- ============================================================================

-- 1) Kolom baru di employees
ALTER TABLE `employees`
  ADD COLUMN `office_phone` VARCHAR(30) NULL AFTER `phone`,
  ADD COLUMN `sim_number` VARCHAR(30) NULL AFTER `ktp_number`,
  ADD COLUMN `guarantee1_type` VARCHAR(100) NULL,
  ADD COLUMN `guarantee1_file` VARCHAR(255) NULL,
  ADD COLUMN `guarantee2_type` VARCHAR(100) NULL,
  ADD COLUMN `guarantee2_file` VARCHAR(255) NULL,
  ADD COLUMN `emergency_contact2_name` VARCHAR(150) NULL,
  ADD COLUMN `emergency_contact2_relation` VARCHAR(100) NULL,
  ADD COLUMN `emergency_contact2_phone` VARCHAR(30) NULL;

-- 2) Riwayat perubahan kepegawaian
CREATE TABLE `employee_histories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` BIGINT UNSIGNED NOT NULL,
  `category` VARCHAR(30) NOT NULL,
  `effective_date` DATE NOT NULL,
  `old_value` VARCHAR(255) NULL,
  `new_value` VARCHAR(255) NULL,
  `note` VARCHAR(255) NULL,
  `changed_by_user_id` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_histories_employee_id_category_effective_date_index` (`employee_id`,`category`,`effective_date`),
  CONSTRAINT `employee_histories_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_histories_changed_by_user_id_foreign` FOREIGN KEY (`changed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Catat ke tabel migrations supaya `php artisan migrate` kelak tidak mengulang
--    (cek dulu: SELECT MAX(batch) FROM migrations;  lalu ganti <BATCH> dengan MAX+1)
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_10_22_100000_add_contact_sim_guarantee_to_employees_table',<BATCH>),
-- ('2026_10_22_110000_create_employee_histories_table',<BATCH>);
