# Deploy produksi per-TAHAP (recovery)

Dipakai kalau `deploy-all-produksi.sql` **sudah dijalankan sebagian** di prod, jadi
tidak bisa diulang dari atas (file itu pakai `CREATE TABLE` polos tanpa
`IF NOT EXISTS` — MySQL berhenti di tabel pertama yang sudah ada).

`tahap-01.sql` … `tahap-34.sql` = potongan tiap TAHAP dari `deploy-all-produksi.sql`,
isi & urutan identik. Nomor TAHAP = kolom di `DEPLOY-PRODUKSI.md`.

## Cara pakai

**1. Cek tabel apa yang belum ada:**
```bash
mysql -u USER -p hris < CEK-TABEL.sql
```
Output = daftar `tabel_hilang` + `jalankan_tahap`.

**2. Jalankan HANYA TAHAP yang muncul di output**, berurutan dari nomor kecil:
```bash
mysql -u USER -p hris < tahap-14.sql
mysql -u USER -p hris < tahap-17.sql
mysql -u USER -p hris < tahap-20.sql
# dst — sesuai hasil CEK-TABEL.sql
```

**3. Kalau satu TAHAP error "table already exists" / "Duplicate column"** — berarti
TAHAP itu sebenarnya sudah pernah jalan sebagian. Buka file-nya, jalankan manual
hanya statement `CREATE TABLE` / `ALTER TABLE` yang tabel/kolomnya belum ada.

**4. Urutan wajib** (dependency):
- TAHAP 1–17 dulu semua yang hilang → **deploy kode** (`git pull`, composer, cache)
- TAHAP 18 → langsung `php artisan employees:encrypt-sensitive`
- TAHAP 19–34 yang hilang
- baru seeder (`RegionSeeder`, `ApprovalWorkflowSeeder`, `CompetencySeeder`,
  `LeavePolicySeeder`, `OffboardingChecklistItemSeeder`, `PermissionCatalogSeeder` TERAKHIR)

## Catatan

- **TAHAP 16 destruktif** — `DROP` 5 tabel appraisal lama. Cek dulu prod masih pakai
  appraisal model lama atau tidak. Backup wajib.
- **TAHAP 18 destruktif** — `DROP` kolom `employees.marital_status` / `religion_name`
  / `blood_type` + ubah `ktp_number`/`npwp_number` → text. Jalankan `employees:encrypt-sensitive`
  segera setelahnya atau semua halaman karyawan error `DecryptException`.
- Tidak ada TAHAP 28 (penomoran ikut `deploy-all-produksi.sql`).
- Kalau prod **belum ada data penting sama sekali** (deploy baru), lebih bersih:
  `DROP DATABASE hris; CREATE DATABASE hris;` lalu jalankan `deploy-all-produksi.sql`
  utuh sekali jalan — tidak usah per-TAHAP.
