# Replace database HR, selamatkan data GA

HR minta prod `hris` di-**reset total** ke skema HRIS v1.2 yang benar, **kecuali**
11 tabel GA yang datanya harus utuh.

## Kenapa aman

GA (11 tabel) **sepenuhnya mandiri** — diverifikasi dari snapshot prod 2026-09-10:

- Tidak ada tabel non-GA yang punya FK ke tabel GA.
- Satu-satunya FK keluar GA → non-GA: `vault_document_transactions.created_by → users.id`,
  dan tabel itu **0 baris** di prod.

Jadi data GA bisa dicabut, disimpan, lalu dikembalikan tanpa nyangkut apa pun dari HR.

Data GA di prod (per 2026-09-10): vehicles 8 · vehicle_usages 61 · meeting_rooms 12 ·
room_cleaning_items 85 · room_cleaning_logs 10 · room_cleaning_log_details 75 ·
room_cleaning_photos 54 · vault_document_categories 2 · vaults 1 · vault_documents 125 ·
vault_document_transactions 0.

`whistleblower_reports` **0 baris** — ikut di-reset (bukan GA).

## PERINGATAN

Prosedur ini **menghapus SEMUA data HR di prod**: 13 user, 12 karyawan, payroll,
appraisal, cuti, perdin, reimbursement, dst. Pastikan HR memang mau fresh start
(atau punya file data pengganti). Backup penuh tetap wajib.

---

## Prosedur (jalankan di server prod)

### 1. Backup — WAJIB

```bash
cd /path/aplikasi
mysqldump -u USER -p hris > ~/backup-hris-FULL-$(date +%F-%H%M).sql

# Backup GA khusus (jaring pengaman kedua)
mysqldump -u USER -p --no-tablespaces hris \
  vehicles vehicle_usages meeting_rooms room_cleaning_items room_cleaning_logs \
  room_cleaning_log_details room_cleaning_photos vault_document_categories vaults \
  vault_documents vault_document_transactions > ~/ga-backup-$(date +%F).sql
```

> Kalau tidak bisa `mysqldump` di server: pakai `database/ga-preserve.sql` yang sudah
> disiapkan (snapshot GA 2026-09-10, sudah diuji restore). Kurang update dari live
> tapi lengkap.

### 2. Maintenance + deploy kode

```bash
php artisan down
git pull origin main
composer install --no-dev --optimize-autoloader
```

### 3. Reset database

```bash
mysql -u USER -p -e "DROP DATABASE hris; CREATE DATABASE hris CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --force
```

`migrate` bikin 152 tabel fresh (v1.2 lengkap, **termasuk 11 tabel GA dalam keadaan
kosong**). Semua tercatat di tabel `migrations`.

### 4. Kembalikan data GA

```bash
# data-only, struktur GA dari migrate dipertahankan:
mysql -u USER -p hris -e "SET FOREIGN_KEY_CHECKS=0; \
  TRUNCATE vehicles; TRUNCATE vehicle_usages; TRUNCATE meeting_rooms; \
  TRUNCATE room_cleaning_items; TRUNCATE room_cleaning_logs; \
  TRUNCATE room_cleaning_log_details; TRUNCATE room_cleaning_photos; \
  TRUNCATE vault_document_categories; TRUNCATE vaults; TRUNCATE vault_documents; \
  TRUNCATE vault_document_transactions;"

mysql -u USER -p hris < ~/ga-backup-$(date +%F).sql
```

`ga-backup.sql` / `ga-preserve.sql` sudah pakai `DROP TABLE IF EXISTS` + `CREATE` +
`INSERT`, jadi TRUNCATE di atas sebenarnya opsional — file akan menimpa 11 tabel itu.

### 5. Seeder data awal

```bash
php artisan db:seed --class=RegionSeeder
php artisan db:seed --class=RegionDistrictSeeder
php artisan db:seed --class=ApprovalWorkflowSeeder
php artisan db:seed --class=CompetencySeeder
php artisan db:seed --class=LeavePolicySeeder
php artisan db:seed --class=OnboardingChecklistItemSeeder
php artisan db:seed --class=OffboardingChecklistItemSeeder
php artisan db:seed --class=PreEmploymentChecklistItemSeeder
php artisan db:seed --class=PermissionCatalogSeeder   # TERAKHIR
```

Master data (agama, pendidikan, bank, dll), `companies`, `departments`, `positions`,
`levels`, user admin — **tidak ada seeder produksi-nya**. Isi lewat:
- file data pengganti dari HR, atau
- `UserSeeder` + input manual di aplikasi, atau
- `mysqldump` selektif dari backup lama (`companies`, `levels`, dll — hati-hati kolom
  v1.2 baru).

### 6. Selesai

```bash
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan storage:link
php artisan up
```

Pasang cron kalau belum: `* * * * * cd /path/aplikasi && php artisan schedule:run >> /dev/null 2>&1`

### 7. Verifikasi GA

```bash
mysql -u USER -p hris -e "
SELECT 'vehicles' t, COUNT(*) n FROM vehicles UNION ALL
SELECT 'vehicle_usages', COUNT(*) FROM vehicle_usages UNION ALL
SELECT 'vault_documents', COUNT(*) FROM vault_documents UNION ALL
SELECT 'room_cleaning_log_details', COUNT(*) FROM room_cleaning_log_details;"
```
Harus: 8 / 61 / 125 / 75. Lalu buka menu GA + scan 1 QR berangkas / kendaraan.

---

## Alternatif: TANPA drop database (GA tidak pernah dipindah)

Kalau ragu menyentuh DB GA sama sekali:

```bash
# setelah backup (langkah 1) + deploy kode (langkah 2)
mysql -u USER -p hris < database/hris-swap-1-drop-non-ga.sql   # hapus 81 tabel non-GA, 11 tabel GA UTUH di tempat
```

Lalu bangun ulang tabel non-GA. Masalahnya: `php artisan migrate` akan coba bikin
ulang tabel GA yang masih ada → error "table exists". Jadi butuh salah satu:
- `php artisan migrate --pretend` dulu, atau tandai migrasi GA sebagai sudah jalan:
  `INSERT INTO migrations (migration,batch) VALUES ('2026_06_19_063153_create_vehicles_table',1), ...`
  untuk 8 migrasi GA + `migrations` table dibuat manual, **ribet & rawan**.

→ **Drop database + restore GA (prosedur utama di atas) lebih bersih dan sudah teruji.**
Struktur GA yang dibuat `migrate` identik dengan yang di prod (migrasi `2026_06_19_*`
dan `2026_07_12_* / 07_21_*` = sumber `vault-tables*.sql`).
