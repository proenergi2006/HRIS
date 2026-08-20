-- Modul: Anggota Keluarga Karyawan (Istri & Anak)
-- Dipakai sbg pilihan dropdown "Nama Pasien" di form Reimbursement.
-- Setara dengan migration:
--   2026_08_20_100001_create_employee_family_members_table
--
-- PRASYARAT: tabel `employees` sudah ada.
-- WAJIB backup database dulu sebelum jalankan.

CREATE TABLE `employee_family_members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `relation` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `birth_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_family_members_employee_id_foreign` (`employee_id`),
  CONSTRAINT `employee_family_members_employee_id_foreign`
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nilai `relation` yang dipakai aplikasi: 'spouse' (Istri/Suami), 'child' (Anak).
-- Tidak dibatasi di level database (perusahaan menanggung maks. 1 istri + 2 anak,
-- tapi HR tetap bisa mencatat lebih dari itu bila perlu — lihat app/Models/EmployeeFamilyMember.php).

-- Opsional: daftarkan ke tabel migrations Laravel supaya `php artisan migrate`
-- tidak mencoba menjalankan ulang perubahan ini nanti. Cek dulu:
--   SELECT MAX(batch) FROM migrations;
-- lalu ganti angka <BATCH> di bawah dengan (hasil query di atas + 1).
--
-- INSERT INTO `migrations` (`migration`, `batch`) VALUES
-- ('2026_08_20_100001_create_employee_family_members_table', <BATCH>);
