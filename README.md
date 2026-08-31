# SIPRO

SIPRO adalah aplikasi HRIS (Human Resource Information System) internal untuk **PT Pro Energi Group** (PT Pro Energi, PT Tridaya Selaras, PT Pinnafore Staraya), dibangun dengan Laravel. Aplikasi ini menggantikan beberapa proses manual/aplikasi terpisah (termasuk migrasi data karyawan dari Jayroll) menjadi satu sistem terpadu untuk kepegawaian, penilaian kinerja, keuangan karyawan, dan operasional General Affairs.

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Database**: MySQL / MariaDB
- **Frontend**: Blade + Bootstrap 4 (tema GrainDashboard), jQuery, DataTables
- **Build tool**: Vite
- **Package penting**:
  - `spatie/laravel-permission` — role & permission
  - `maatwebsite/excel` — export/import Excel
  - `barryvdh/laravel-dompdf` — export PDF
  - `simplesoftwareio/simple-qrcode` — QR code (barcode kendaraan/ruangan/dokumen brankas)
  - `vinkla/hashids` — obfuscated ID di URL (route model binding)

## Modul Utama

| Modul | Deskripsi |
|---|---|
| **Data Karyawan** | Master data karyawan (profil, jabatan, departemen, dokumen) + import Excel. Tab data lengkap (tambah/edit/hapus): Keluarga, BPJS/NSSF, Pendidikan, Pengalaman Kerja, Skill, Riwayat Organisasi (sumber Career Management), Fasilitas, Rekening Bank, Kontrak |
| **Employee Administration** | Surat (template + terbitkan per karyawan, merge placeholder, export PDF) dan pengajuan Perubahan Data pribadi self-service (approval lewat **Approval Engine**, field dibatasi whitelist) |
| **Struktur Organisasi** | Company → Divisi → Departemen → Section → Jabatan (+ Cost Center, Head of Unit, `reports_to` antar jabatan). Bagan organisasi otomatis mengikuti hierarki ini (tingkat Divisi/Section muncul otomatis begitu datanya diisi), tampilkan jabatan kosong (vacant), filter Divisi/Departemen/Section, export PDF & Gambar, **drag & drop reparenting** Departemen/Section (permission `org-structure.edit`). Audit riwayat perubahan (`org_change_logs`) |
| **Approval Engine** | Mesin persetujuan generik (`App\Services\ApprovalEngine`): workflow per jenis transaksi per perusahaan, step berbasis org chart / jabatan / role, kondisi, eskalasi, delegasi. Kotak Persetujuan terpadu + simulasi + copy antar-PT. Transaksi baru di atasnya: Reward, Punishment, Promosi & Rotasi, Termination. **Notifikasi in-app + email generik** (ke approver saat step actionable, ke requester saat approved/rejected — semua modul, dari engine, bukan per-modul). **Reminder eskalasi terjadwal** (`approval:remind-overdue`) untuk step yang lewat tenggat. **Riwayat perubahan aturan approval** (before/after daftar step, `approval_workflow_change_logs`) |
| **Master Data** | Tabel referensi mengikuti PRD: Agama, Jenjang & Jurusan Pendidikan, Status Pernikahan (+ kode PTKP), Golongan Darah, Tipe Karyawan, Bank, Rekening Perusahaan (per PT), Wilayah berjenjang Provinsi → Kota/Kabupaten → Kecamatan → Kelurahan/Desa (dengan cascade AJAX di form karyawan) |
| **Penilaian Kinerja (Appraisal)** | KPI/objective-based (bobot% + target + realisasi + capaian → skor per KPI, opsional mulai dari Template KPI starter atau dari nol), 1 evaluator per appraisal (default atasan langsung), approval lewat **Approval Engine** (Kotak Persetujuan terpadu), grade otomatis dari total skor, laporan & export PDF |
| **Reimbursement** | Pengajuan reimbursement medical, approval lewat **Approval Engine**, potong saldo otomatis saat disetujui |
| **Perjalanan Dinas (Perdin)** | Pengajuan perjalanan dinas + anggaran & itinerary. Approval lewat **Approval Engine** (Kotak Persetujuan terpadu), laporan |
| **General Affairs (GA)** | Kendaraan (check-in/out via QR), Ruang Meeting (checklist kebersihan via QR), Barcode Dokumen Brankas (pengambilan/pengembalian dokumen via QR) |
| **HR** | Absensi (input manual & import log mesin fingerprint), Lembur (pengajuan self-service + approval lewat **Approval Engine**, otomatis tercatat ke rekap Lembur & Tunjangan Lembur payroll), Cuti (pengajuan + approval lewat **Approval Engine**, potong saldo otomatis saat disetujui), Penggajian (komponen gaji, slip gaji per periode, **PPh21 otomatis metode TER** + BPJS), **THR** (proporsional sesuai Permenaker 6/2016), ESS lihat slip gaji sendiri (periode closed) |
| **Manpower Planning** | Rencana headcount per company/unit/jabatan per periode vs actual (live dari data karyawan), approval lewat **Approval Engine**, dashboard konsolidasi grup |
| **Recruitment & Onboarding** | Job Requisition (approval lewat **Approval Engine**) → Kandidat (screening/interview/offer) → Pre-Employment (dokumen, hasil MCU) → konversi jadi Karyawan → checklist Onboarding (dokumen/akun/aset/induction), item aset otomatis tercatat di tab Fasilitas karyawan |
| **Training & Development** | Katalog program training + peserta/training record per karyawan |
| **Competency Framework** | Kamus kompetensi (kategori + skala level 1–5), kompetensi wajib per jabatan, penilaian kompetensi karyawan (current-state, perubahan tercatat di `activity_log`), Analisis Gap (wajib vs aktual per karyawan/departemen) + rekomendasi program training se-kategori, export PDF |
| **Career Management** | Career path (jenjang jabatan berurutan) + timeline riwayat jabatan karyawan (dari Organization Experience), shortcut ke pengajuan Promosi/Rotasi |
| **Whistleblower** | Pengaduan publik (tanpa login) dengan tracking tiket |
| **Laporan** | Rekap bulanan (PDF) dan export Excel lintas modul; **Laporan Absensi & Cuti** bulanan per company; **Laporan Payroll Summary** per periode per company (rincian per komponen: PPh21, BPJS, dll); **Dashboard Headcount** per company/unit/status/tipe/level/gender + konsolidasi grup (chart + tabel + PDF) |
| **Manajemen User & Role** | Role & permission granular per modul + per company (lihat bagian Role & Permission) |
| **Audit Trail** | Semua transaksi (Cuti, Lembur, Reward, Punishment, Promosi/Rotasi, Termination, Job Requisition, Manpower Plan, Perubahan Data Karyawan, Perdin, Reimbursement, Appraisal) mencatat create/update/delete + diff nilai ke `activity_log` (`spatie/laravel-activitylog`) |
| **Keamanan & Backup** | Data sensitif (NIK, NPWP, No. rekening) dienkripsi at-rest (cast `encrypted` Laravel). Backup DB harian otomatis (`backup:database` → `storage/app/backups/`, retensi 14 hari) |

## Role & Permission

Role & permission granular per modul (lihat/tambah/ubah/hapus/approve), skema PRD Bab 4. Dikelola
via `spatie/laravel-permission`, dikonfigurasi lewat menu **Sistem → Role & Hak Akses** (admin) —
HR bisa bikin role baru & atur hak aksesnya sendiri tanpa developer.

- Katalog modul & aksi: `App\Support\PermissionCatalog` (sumber tunggal, dipakai seeder & UI).
- Role bawaan (legacy, akses persis sama seperti sebelum migrasi ini): `admin`, `admin_ga`,
  `hr_manager`, `ceo`, `cfo`, `evaluator`, `user_ii`, `karyawan`.
- Role baru sesuai PRD Bab 4 (tambahan, tersedia untuk dipakai): `super_admin`, `hr_group_admin`,
  `hr_admin`, `hr_payroll`, `recruiter`.
- Satu user bisa punya beberapa role, masing-masing bisa dibatasi ke 1 company atau berlaku di
  semua company (`role_company_assignments`, dikelola di menu **Manajemen User**). Company aktif
  user dipilih lewat company-switcher di header (middleware `ResolveActiveCompany`).
- Rute dibatasi lewat middleware `permission:<module>.<action>` di `routes/web.php` (bukan lagi
  `role:...` string hardcoded), sebagian besar dalam group
  `Route::middleware(['auth','permission:...'])`.

## Instalasi & Setup Lokal

```bash
git clone <repo-url> sipro-app
cd sipro-app

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Isi kredensial database di `.env`, lalu:

```bash
php artisan migrate --seed   # jika ada seeder awal
php artisan db:seed --class=RegionSeeder   # 38 provinsi + ~377 kota/kabupaten
npm run build                # atau `npm run dev` untuk mode watch
php artisan serve
```

### Data sample untuk uji coba

`database/seeders/SampleDataSeeder.php` — bukan bagian dari `db:seed` default (biar tidak numpuk data sample di install produksi). Jalankan manual kalau perlu contoh data yang hidup di semua modul (karyawan lintas Divisi/Section/atasan, 1 jabatan kosong, dan transaksi Cuti/Perdin/Reimbursement/Reward/Punishment/Promosi/Termination dalam berbagai status):

```bash
php artisan db:seed --class=SampleDataSeeder
```

Asumsi: master data (companies, departments, positions, levels, master referensi, `approval_workflows`) sudah ada; tabel karyawan & transaksi masih kosong (aman dijalankan berulang setelah dikosongkan lagi).

### Deploy ke produksi (tanpa `artisan migrate`)

Server produksi TIDAK menjalankan `php artisan migrate`. Setiap batch perubahan
skema disertai file SQL manual di `database/*.sql` — jalankan berurutan setelah
backup. Urutan untuk modul-modul terbaru:
`database/master-data-manual.sql` → `database/master-organization-manual.sql`
→ `database/employee-data-tabs-manual.sql` → `database/approval-engine-manual.sql`
→ `database/role-permission-manual.sql` → `database/payroll-pph21-manual.sql`
→ `database/manpower-planning-manual.sql` → `database/recruitment-manual.sql`
→ `database/training-career-manual.sql` → `database/employee-administration-manual.sql`
→ `database/overtime-request-manual.sql` → `database/thr-manual.sql` → `database/appraisal-kpi-manual.sql`
→ `database/gap-closure-manual.sql` → `database/competency-manual.sql`
→ `database/approval-audit-region-manual.sql`
→ `database/kasbon-bonus-manual.sql` → `database/shift-leave-policy-manual.sql`
→ `database/offboarding-letter-request-manual.sql` → `database/engagement-analytics-manual.sql`
(data wilayah lewat `RegionSeeder` + `RegionDistrictSeeder`; workflow default lewat
`ApprovalWorkflowSeeder`; permission & mapping role lewat `PermissionCatalogSeeder`; kamus kompetensi awal
lewat `CompetencySeeder`; kebijakan cuti default lewat `LeavePolicySeeder`; checklist clearance resign
default lewat `OffboardingChecklistItemSeeder` — lihat catatan di dalam `role-permission-manual.sql`,
`competency-manual.sql`, `approval-audit-region-manual.sql`, dan ke-4 file Fase 2 di atas).

**Fase 2 HRD** (kasbon/bonus/bukti-potong-PPh21, shift & roster, kebijakan cuti, clearance resign,
permintaan surat self-service, pengumuman, survey engagement, HR analytics) ditandai umum vs. lanjutan
sesuai prioritas HR — lihat 4 file manual di atas. Fitur ini DITUNDA (belum dibangun): check-in mobile
GPS/selfie, payroll host-to-host ke bank, aplikasi mobile native, career-site publik, integrasi e-Bupot/
DJP Online, entity Group/Holding.

ℹ️ Master Kecamatan/Kelurahan: `RegionDistrictSeeder` cuma isi starter set (~6 kota besar + kelurahan
Jakarta Selatan/Pusat). Dataset penuh Indonesia (~7rb kecamatan, ~83rb kelurahan) di-import terpisah dari
wilayah.id/Kemendagri ke `database/data/regions-districts.php` lalu jalankan ulang seeder.

⚠️ Tabel tarif PPh21 (TER) di `payroll-pph21-manual.sql` masih perkiraan/ilustratif — WAJIB
divalidasi Finance sebelum dipakai untuk penggajian resmi (lihat banner di halaman Penggajian).

⚠️ `gap-closure-manual.sql` (enkripsi data sensitif): setelah `ALTER TABLE`, WAJIB jalankan
`php artisan employees:encrypt-sensitive` di server (enkripsi butuh `APP_KEY`, tidak bisa SQL murni).
Jangan rotate `APP_KEY` setelahnya tanpa proses re-enkripsi.

**Scheduler**: fitur terjadwal (`contract:remind`, `approval:remind-overdue`, `backup:database`,
`documents:remind`, `leave:year-end`, `leave:expire-carry`) butuh cron
`* * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1` di server. `backup:database`
butuh binary `mysqldump` di PATH.

Aplikasi berjalan di `http://127.0.0.1:8000`.

### Catatan Storage

Upload file (dokumen karyawan, foto profil, foto serah terima dokumen brankas, foto checklist kebersihan, dll) disimpan di disk `local` (`storage/app/private`) — **tidak** memakai `storage:link`/disk `public`. Setiap file selalu disajikan lewat route yang memvalidasi otorisasi terlebih dahulu (streaming via `Storage::disk('local')->response(...)`), bukan URL publik langsung.

## Struktur Direktori Penting

```
app/Http/Controllers/
  Appraisal/     -> Data Karyawan, Departemen, Jabatan, Penilaian Kinerja
  GA/            -> Kendaraan, Ruang Meeting, Barcode Dokumen Brankas
  HR/            -> Absensi, Cuti, Penggajian
  Perdin/        -> Perjalanan Dinas
  Reimbursement/ -> Reimbursement
  Whistleblower/ -> Pengaduan

app/Models/
  Employee.php, Company.php, Department.php, Position.php, Level.php
  GA/            -> Vehicle, MeetingRoom, VaultDocument, dst.
  HR/            -> AttendanceRecord, LeaveRequest, SalaryComponent, dst.
  Appraisal/     -> Appraisal, AppraisalTemplate, AppraisalFlowConfig, dst.

resources/views/
  layouts/grain.blade.php    -> layout utama area admin (login)
  ga/public/                 -> halaman publik hasil scan QR (tanpa login)
  components/sidebar.blade.php -> menu navigasi, dikelompokkan per modul & role
```

## Konvensi Kode

- Route model binding memakai **Hashids** (trait `App\Traits\HasHashid`) untuk data yang bisa diakses lewat URL publik (Kendaraan, Ruang Meeting, Dokumen Brankas) — ID asli tidak pernah terekspos di URL.
- Master data referensi (Kategori Dokumen, Departemen, Jabatan, dsb.) memakai pola CRUD ringan: satu halaman index dengan form tambah + baris yang bisa diedit/dihapus langsung.
- Setiap fitur upload foto dari kamera (bukan pilih dari galeri) memakai `getUserMedia` + `canvas` langsung di halaman (bukan `<input type="file" capture>`), karena atribut `capture` tidak konsisten menyembunyikan opsi galeri di semua browser/HP.
