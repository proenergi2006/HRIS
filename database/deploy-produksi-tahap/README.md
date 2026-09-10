# Deploy produksi per-TAHAP (recovery)

Dipakai kalau `deploy-all-produksi.sql` **sudah dijalankan sebagian** di prod, jadi
tidak bisa diulang dari atas (file itu pakai `CREATE TABLE` polos tanpa
`IF NOT EXISTS` — MySQL berhenti di tabel pertama yang sudah ada).

`tahap-01.sql` … `tahap-34.sql` = potongan tiap TAHAP dari `deploy-all-produksi.sql`,
isi & urutan identik. Nomor TAHAP = kolom di `DEPLOY-PRODUKSI.md`.

## Status prod `hris` per 2026-09-10

Dari daftar tabel prod: **TAHAP 1–6 SUDAH** (master data, master organisasi, branches,
employee data tabs, approval engine, role_company_assignments semua ada). **TAHAP 7–34
BELUM** (ter_categories, employee_loans, thr_periods, job_requisitions, candidates,
training_programs, competencies, districts, shifts, announcements, surveys, notifications,
dll — semua hilang). Prod juga masih punya 5 tabel appraisal lama (`appraisal_approvals`,
`appraisal_items`, `appraisal_aspects`, `appraisal_aspect_weights`, `appraisal_flow_configs`)
yang akan di-DROP oleh TAHAP 16.

GA (`vehicles`, `meeting_rooms`, `room_cleaning_*`, `vaults`, `vault_*`) & `whistleblower_reports`
sudah live di prod — **tidak disentuh** file mana pun di sini.

## Jalur cepat (kondisi prod di atas): pakai `deploy-produksi-HR-lanjutan.sql`

File `database/deploy-produksi-HR-lanjutan.sql` = TAHAP 7–34 digabung, GA-free.

```bash
# 0. WAJIB
mysqldump -u USER -p hris > backup-hris-sebelum-lanjutan.sql
php artisan down

# 1. Pastikan TAHAP 1–6 lengkap (semua baris harus "ADA")
mysql -u USER -p hris < database/deploy-produksi-tahap/CEK-KOLOM.sql

# 2. Jalankan TAHAP 7–17 (file berhenti sendiri di penanda "-- >>> STOP" utk deploy kode)
#    -> paling mudah: jalankan seluruh file, statement setelah STOP tetap jalan,
#       tapi LEBIH AMAN dipotong. Lihat "Kalau mau dipotong" di bawah.
mysql -u USER -p hris < database/deploy-produksi-HR-lanjutan.sql

# 3. Deploy kode aplikasi (git pull main, composer install, cache) — lihat DEPLOY-PRODUKSI.md §4
# 4. WAJIB setelah kode live:
php artisan employees:encrypt-sensitive

# 5. Seeder (PermissionCatalogSeeder TERAKHIR) — lihat DEPLOY-PRODUKSI.md §6
# 6. php artisan up
```

> **Penting soal urutan:** `deploy-produksi-HR-lanjutan.sql` isinya TAHAP 7–34 termasuk
> TAHAP 18 (enkripsi kolom NIK/NPWP) yang idealnya jalan SETELAH kode baru live. Kalau
> file dijalankan sekaligus lalu kode di-deploy lalu `employees:encrypt-sensitive`, tetap
> aman (command idempotent, meng-handle nilai yang belum terenkripsi). Yang penting
> `employees:encrypt-sensitive` jalan SEBELUM ada user buka halaman karyawan.

### Kalau mau dipotong (paling aman)

Jalankan per-TAHAP: `tahap-07.sql` … `tahap-17.sql`, lalu deploy kode +
`php artisan employees:encrypt-sensitive`, lalu `tahap-18.sql` … `tahap-34.sql`.

## Kalau prod ternyata beda dari asumsi di atas

**1. Cek tabel apa yang belum ada:**
```bash
mysql -u USER -p hris < CEK-TABEL.sql
```
Output = daftar `tabel_hilang` + `jalankan_tahap`.

**2. Jalankan HANYA TAHAP yang muncul di output**, berurutan dari nomor kecil.

**3. Kalau satu TAHAP error "table already exists" / "Duplicate column"** — berarti
TAHAP itu sudah pernah jalan sebagian. Buka file-nya, jalankan manual hanya statement
`CREATE TABLE` / `ALTER TABLE` yang tabel/kolomnya belum ada.

## Catatan

- **TAHAP 16 destruktif** — `DROP` 5 tabel appraisal lama (`appraisal_approvals`,
  `appraisal_items`, `appraisal_aspects`, `appraisal_aspect_weights`, `appraisal_flow_configs`).
  Kalau modul Appraisal lama di prod ada data penting, backup 5 tabel itu dulu.
- **TAHAP 18 destruktif** — `DROP` kolom `employees.marital_status` / `religion_name`
  / `blood_type` + ubah `ktp_number`/`npwp_number` → text. Jalankan `employees:encrypt-sensitive`
  segera setelahnya atau semua halaman karyawan error `DecryptException`.
- Tidak ada TAHAP 28 (penomoran ikut `deploy-all-produksi.sql`).
- Kalau prod **belum ada data penting sama sekali** (deploy baru), lebih bersih:
  backup 11 tabel GA + `whistleblower_reports` + `users`, `DROP` sisanya, jalankan
  `deploy-all-produksi.sql` utuh — tidak usah per-TAHAP.
