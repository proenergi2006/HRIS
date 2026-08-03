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
| **Data Karyawan** | Master data karyawan (profil, jabatan, departemen, dokumen), lengkap dengan import dari Excel |
| **Penilaian Kinerja (Appraisal)** | Template penilaian, periode, alur persetujuan berjenjang per departemen, laporan & export |
| **Reimbursement** | Pengajuan reimbursement medical, approval, saldo per karyawan |
| **Perjalanan Dinas (Perdin)** | Pengajuan perjalanan dinas, approval berjenjang, laporan |
| **General Affairs (GA)** | Kendaraan (check-in/out via QR), Ruang Meeting (checklist kebersihan via QR), Barcode Dokumen Brankas (pengambilan/pengembalian dokumen via QR) |
| **HR** | Absensi (input manual & import log mesin fingerprint), Cuti (pengajuan & approval berjenjang), Penggajian (komponen gaji, slip gaji per periode) |
| **Whistleblower** | Pengaduan publik (tanpa login) dengan tracking tiket |
| **Laporan** | Rekap bulanan (PDF) dan export Excel lintas modul |
| **Manajemen User & Role** | Admin, GA, HR Manager, CEO/CFO (approval), Karyawan |

## Role & Middleware

Role dikelola via `spatie/laravel-permission`. Role yang ada saat ini:

- `admin` — akses penuh
- `admin_ga` — modul General Affairs
- `hr_manager` — modul HR (absensi, cuti, payroll) & laporan
- `ceo`, `cfo` — approval akhir penilaian kinerja
- `karyawan` — akses standar (self-service: pengajuan, penilaian diri, dsb.)

Rute admin dibatasi lewat middleware `role:...` di `routes/web.php`, sebagian besar dalam group `Route::middleware(['auth','role:...'])`.

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
npm run build                # atau `npm run dev` untuk mode watch
php artisan serve
```

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
