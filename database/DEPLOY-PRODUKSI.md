# Deploy Produksi — HRIS v1.2 (Fase 2 HRD + Rebuild Organisasi)

Rilis besar: ~70 migration, 24 file `*-manual.sql`, modul baru (Approval Engine,
Recruitment, Training, Competency, Career, Kasbon, Bonus, THR, Shift/Roster,
Offboarding, Pengumuman, Survey, Manpower Planning, Cabang, enkripsi data sensitif).

Server produksi dinaikkan **manual** (bukan `php artisan migrate`). Setiap modul
punya file `database/<modul>-manual.sql` yang setara dengan migration-nya.

---

## 0. Prasyarat & pengecekan awal

1. **BACKUP DATABASE PENUH** (`mysqldump`) — wajib, rilis ini mengandung
   `ALTER`/`DROP` destruktif (appraisal lama di-drop, kolom `employees` di-drop,
   `ktp_number`/`npwp_number` dienkripsi).
2. Masuk maintenance mode kalau bisa (`php artisan down`) — ada normalisasi status
   pengajuan Cuti/Perdin/Reimbursement yang lebih aman tanpa transaksi berjalan.
3. Cek posisi server sekarang:
   ```sql
   SELECT migration FROM migrations ORDER BY id DESC LIMIT 10;
   SHOW TABLES;
   ```
   **Baseline yang diasumsikan sudah ada** (rilis-rilis sebelumnya): `employees`,
   `companies`, `departments`, `positions`, `levels`, `salary_components`,
   `attendance_records`, `leave_requests`, `leave_balances`, `perdin_requests`,
   `reimbursement_requests`, `employee_family_members`, tabel vault/GA.
   Setara file: `employee-master-data-manual.sql`, `hr-module-manual.sql`,
   `employee-family-members-manual.sql`, `reimbursement-payment-period-manual.sql`,
   `vault-tables*.sql`. Kalau salah satu tabel di atas belum ada, jalankan file itu
   dulu sebelum lanjut.
4. Cek MySQL bisa `CREATE TABLE ... FOREIGN KEY` (InnoDB) dan `json_valid()`
   (MariaDB 10.4+ / MySQL 5.7+) — dipakai `org_change_logs`, `activity_log`.

---

## 1. Master data & struktur organisasi

Jalankan berurutan, satu file selesai baru lanjut:

| # | File | Isi |
|---|------|-----|
| 1 | `master-data-manual.sql` | agama, pendidikan, status kawin, gol. darah, tipe karyawan, bank, `company_banks`, provinsi/kota; konversi field string `employees` → FK master |
| 2 | `master-organization-manual.sql` | `divisions`, `sections`, kolom org di `departments`/`positions`/`employees`, `org_change_logs` |
| 3 | `branch-and-level-rank-manual.sql` **(baru)** | `levels.rank`, tabel `branches`, `employees.branch_id`, `positions.branch_id` |
| 4 | `employee-data-tabs-manual.sql` | `employee_nssf`, `employee_educations`, `employee_work_experiences`, `employee_skills`, `employee_org_experiences`, `employee_facilities`, `employee_bank_accounts`, `employee_contracts` |

Data wilayah lengkap (provinsi/kota/kecamatan/kelurahan) diisi lewat seeder di
langkah 6 — SQL cuma menyiapkan tabel + contoh baris.

---

## 2. Approval Engine & hak akses

| # | File | Isi |
|---|------|-----|
| 5 | `approval-engine-manual.sql` | `approval_workflows`, `approval_workflow_steps`, `approval_requests`, `approval_request_steps`, `approval_delegations`, `reward_requests`, `punishment_requests`, `promotion_rotation_requests`, `termination_requests` |
| 6 | `role-permission-manual.sql` | `role_company_assignments` |

> Seeder `ApprovalWorkflowSeeder` & `PermissionCatalogSeeder` dijalankan di
> langkah 6 (butuh semua tabel modul sudah ada dulu).

---

## 3. Modul transaksi HR

Urutan aman (dependency dari header tiap file sudah diperhatikan):

| # | File | Catatan urutan |
|---|------|----------------|
| 7  | `payroll-pph21-manual.sql` | `ter_categories`, `ter_brackets`, komponen PPh21 TER. Angka sudah rekonstruksi Lampiran PMK 168/2023 (31 Agu 2026) — **masih WAJIB dicocokkan Finance/Tax ke dokumen resmi**, lihat banner di menu Master Data > Tarif PPh21 |
| 8  | `kasbon-bonus-manual.sql` | setelah #7 (butuh `salary_components`). `employee_loans`, `loan_installments`, `companies.tax_signer_*`, `bonus_periods`, `bonus_payments` |
| 9  | `thr-manual.sql` | setelah #2 & #7. `thr_periods` (+ pakai `payroll_slips`) |
| 10 | `overtime-request-manual.sql` | setelah #5. `overtime_requests` |
| 11 | `manpower-planning-manual.sql` | setelah #5 & #6. `manpower_plans` |
| 12 | `recruitment-manual.sql` | setelah #5 & #6. `job_requisitions`, `candidates*`, `onboarding_*` |
| 13 | `training-career-manual.sql` | setelah #6. `training_programs`, `training_participants`, `career_paths`, `career_path_steps`, `employees.career_path_id` |
| 14 | `competency-manual.sql` | setelah #13. tabel competency framework |
| 15 | `employee-administration-manual.sql` | setelah #5 & #6. `employee_data_change_requests`, `letter_templates`, `employee_letters` |

> **Kalau server sudah lebih dulu menjalankan `payroll-pph21-manual.sql` versi lama** (kurva
> ilustratif, sebelum 31 Agu 2026) — jalankan `database/pph21-ter-rate-correction-manual.sql`
> setelah TAHAP 7 di atas untuk mengganti `ter_brackets` ke rekonstruksi resmi. Untuk deploy
> baru (TAHAP 7 sudah pakai angka terbaru), file koreksi ini **tidak perlu** dijalankan.
| 16 | `appraisal-kpi-manual.sql` | **DESTRUKTIF** — DROP 5 tabel appraisal lama (`appraisal_approvals`, `appraisal_items`, `appraisal_aspect_weights`, `appraisal_aspects`, `appraisal_flow_configs`) + buat model KPI/objective. Pastikan backup. |
| 17 | `approval-audit-region-manual.sql` | `approval_workflow_change_logs`, `districts`, `villages`, FK `employees.district_id`/`village_id` |

---

## 4. Deploy kode aplikasi

Setelah semua SQL langkah 1–3 sukses:

1. Upload kode rilis ini (`app/`, `resources/`, `routes/`, `config/`, `lang/`,
   `database/seeders/`, `database/migrations/`, `public/img/`,
   `public/vendor/html2canvas/`).
2. `composer install --no-dev --optimize-autoloader`
3. `.env`: set `APP_NAME=ProPeople` (kosmetik). Tidak ada env var baru wajib.
   Pastikan `APP_KEY` **tidak** diubah (lihat langkah 5).
4. `php artisan config:clear && php artisan route:clear && php artisan view:clear`
5. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
6. `php artisan storage:link` (kalau belum).

---

## 5. Setelah kode live — schema yang bergantung ke kode

| # | File / perintah | Catatan |
|---|-----------------|---------|
| 18 | `gap-closure-manual.sql` | `ALTER employees ktp_number/npwp_number → text`, `ALTER employee_bank_accounts account_number → text`, **DROP** `employees.marital_status`/`religion_name`/`blood_type`. |
| 19 | `php artisan employees:encrypt-sensitive` | **WAJIB segera** setelah #18 + kode live. Enkripsi NIK/NPWP/rekening yang sudah ada. Idempotent. Tanpa ini, Eloquent `DecryptException`. **Jangan rotate `APP_KEY` setelah ini.** |
| 20 | `approval-status-normalisasi-manual.sql` **(baru)** | Ubah `status` enum→varchar + normalisasi nilai untuk `leave_requests` / `perdin_requests` / `reimbursement_requests`. Lihat catatan in-flight di dalam file (opsi A/B/C). |
| 21 | `shift-leave-policy-manual.sql` | setelah `hr-module` baseline. `shifts`, `roster_entries`, `leave_policies` |
| 22 | `offboarding-letter-request-manual.sql` | setelah #18 (butuh `employee_facilities`, `termination_requests`). `offboarding_checklist_items`, `employee_offboarding_tasks`, `letter_requests` |
| 23 | `engagement-analytics-manual.sql` | setelah #2 & #12. `announcements*`, `surveys*`, `recruitment_costs` |
| 24 | `job-requisition-budget-control-manual.sql` | setelah #12 & #11. `job_requisitions` + `request_type`/`manpower_plan_id`/`replaces_employee_id` (Budget Control di kode) |
| 25 | `candidate-database-ats-manual.sql` | setelah #12. `candidates` + `expected_salary`/`assessment_*` + `candidate_educations`/`_experiences`/`_skills`/`_certifications` |
| 26 | `preemployment-manual.sql` | setelah #25 & #1. `candidate_preemployment`, `preemployment_checklist_items` (+13 item default), `candidate_preemployment_tasks` |
| 27 | `employee-document-management-manual.sql` | setelah `employee_documents` (baseline) & `termination_requests` (#5). Folder Employee Digital File (`employee_documents.group`) + Exit Interview/Final Settlement (`termination_requests`) |
| 28 | `performance-management-extras-manual.sql` | setelah #16 (`appraisal_objectives`) & #2 (`departments`). `performance_checkins` (1-on-1), `company_objectives` (OKR) + `appraisal_objectives.company_objective_id`, `feedback_360_cycles`/`_reviews`/`_answers` |
| 29 | `compensation-succession-survey-extras-manual.sql` **(baru)** | setelah #1 (`levels`, `positions`) & #23 (`surveys`). `salary_benchmarks` (Compensation), `positions.is_critical_position`/`succession_risk`/`succession_notes` + `talent_pool_members` (Succession Planning), `surveys.type` (Survey pulse/eNPS) |
| 30 | `notification-talent-extras-manual.sql` **(baru)** | setelah #1 (`levels`), `companies`, `employees`, `users`. `notifications` (Notification Center — tabel standar Laravel), `salary_grades` (Struktur Gaji Internal), `employees.potential_rating`/`potential_notes`/`potential_assessed_at`/`potential_assessed_by_user_id` (Grid 9-Kotak), `kudos` (Recognition) |
| 31 | `gap4-extras-manual.sql` **(baru)** | setelah `employees`/`companies`/`users`, `candidates` (#25), `surveys` (TAHAP 29). `salary_increase_requests` (Merit Increase, lewat Approval Engine), `candidates.referred_by_employee_id` dkk (Employee Referral), `probation_reviews`, `employee_potential_history` + `salary_grade_history` (riwayat), `surveys.recurrence` + `parent_survey_id` (Pulse auto-recurring). **Setelah kode live, jalankan ulang `php artisan db:seed --class=ApprovalWorkflowSeeder`** (idempoten — nambah workflow `salary_increase_request` 3 PT). |
| 32 | `mitra-employee-type-manual.sql` **(baru)** | setelah `master-data-manual.sql` (butuh `employee_types`). Tambah 1 baris tipe karyawan "Mitra" — data saja, tidak ada perubahan skema/kode. |
| 33 | `fix-level-rank-manual.sql` **(baru)** | setelah `master-organization-manual.sql` (butuh `levels`). Perbaikan hierarki `levels.rank` (Direksi/Manager/SPV/Admin sebelumnya semua 99, seri — bagan organisasi tidak bisa urutkan CEO di atas Manager/SPV). Data saja, idempoten. |
| 34 | `tds-bod-commercial-logistik-positions-manual.sql` **(baru)** | setelah #33 (butuh `levels`), `companies`, `departments`. Isi 9 Jabatan dasar (Direktur Utama, Manager/SPV/Senior Staff/Staff × Commercial & Logistik) utk Divisi BOD/Commercial/Logistik PT. Tridaya Selaras yg sebelumnya kosong. Data saja, idempoten (skip kalau code sudah ada). **Perlu kode live juga** — lihat catatan bagan organisasi di bawah (cross-division Direksi nesting). |

---

## 6. Seeder data awal (butuh akses `php artisan`)

Jalankan **setelah** semua tabel ada:

```bash
php artisan db:seed --class=RegionSeeder            # provinsi + kota lengkap
php artisan db:seed --class=RegionDistrictSeeder    # kecamatan + kelurahan
php artisan db:seed --class=ApprovalWorkflowSeeder  # workflow default per PT (semua transaction_type)
php artisan db:seed --class=CompetencySeeder        # ~10 kompetensi default
php artisan db:seed --class=LeavePolicySeeder       # kebijakan cuti default per PT
php artisan db:seed --class=OnboardingChecklistItemSeeder     # 11 item onboarding (skip kalau recruitment-manual.sql sudah insert)
php artisan db:seed --class=OffboardingChecklistItemSeeder
php artisan db:seed --class=PreEmploymentChecklistItemSeeder  # 13 item checklist pre-employment (bisa di-skip kalau preemployment-manual.sql sudah insert)
php artisan db:seed --class=PermissionCatalogSeeder # TERAKHIR — permission semua modul + backfill role_company_assignments
```

Opsional (contoh data, **jangan** di server kalau data organisasi sudah diisi manual):
`BranchDireksiSeeder`. **Jangan** jalankan `ITDemoSeeder` / `*SampleData*` / `AllMenuSampleDataSeeder` di produksi.

> Konversi kandidat → karyawan sekarang otomatis: generate NIP (`{PT}-{tahun}-{urут}`),
> buat perjanjian kerja awal (`employee_contracts`), opsional buat akun login
> (role `karyawan`, password sementara ditampilkan sekali), dan tandai selesai item
> onboarding NIP / perjanjian kerja / akun. Tidak butuh langkah deploy tambahan.

> Tanpa akses artisan: `ApprovalWorkflowSeeder` & `PermissionCatalogSeeder` sulit
> direplikasi manual (logika kompleks per-PT). Minimal jalankan keduanya lewat
> `php artisan tinker` atau shell terjadwal.

---

## 7. Cron (fitur terjadwal baru)

Pastikan ada 1 baris cron di server:

```cron
* * * * * cd /path/aplikasi && php artisan schedule:run >> /dev/null 2>&1
```

Menjalankan (lihat `routes/console.php`):
`contract:remind` 08:00 · `approval:remind-overdue` 08:30 · `documents:remind` 08:15 ·
`backup:database` 01:00 (**butuh `mysqldump` di PATH**) · `leave:expire-carry` 02:30 ·
`leave:year-end` 1 Jan 02:00 · `hr:remind-birthdays` 07:00 (ulang tahun & hari jadi
kerja) · `survey:auto-recur` 06:00 (buka otomatis putaran pulse/eNPS berkala).

---

## 8. Verifikasi

1. `php artisan about` — no error; `php artisan migrate:status` (kalau migration
   didaftarkan manual ke tabel `migrations`, semua "Ran").
2. Kosongkan `storage/logs/laravel.log`, login sebagai admin HRD, buka:
   Dashboard (tab SDM + semua chart), Data Karyawan (+ Probation Review), Struktur
   Organisasi (Cabang/Divisi/Section), Approvals (+ Kenaikan Gaji), Rekrutmen →
   Onboarding → Kalender Interview → Referensikan Kandidat, Payroll, Cuti → Kalender
   Cuti, Laporan (6 halaman termasuk Report Builder + PDF), Kasbon, Bonus, THR,
   Offboarding, Pengumuman, Survey (+ Tren eNPS), Competency, Career → Succession
   Planning (+ Grid 9-Kotak + Riwayat Potensi), Kompensasi & Benchmark (4 sub-halaman
   + Riwayat Struktur Gaji), Kudos (Apresiasi), bel notifikasi header (harus tampil
   tanpa error di SEMUA role — admin/hr_manager/evaluator/cfo/ceo/user_ii/karyawan)
   + halaman Notifikasi Saya.
3. Cek `laravel.log` bersih.
4. Spot-check data: `SELECT COUNT(*) FROM permissions;` naik; `approval_workflows`
   terisi per PT; NIK 1 karyawan tampil benar di UI (bukti enkripsi + APP_KEY oke).
5. Keluar maintenance mode: `php artisan up`.

---

## Ringkasan file SQL & padanan migration

| File manual | Migration |
|-------------|-----------|
| `master-data-manual.sql` | `2026_08_28_100001`–`100011` |
| `master-organization-manual.sql` | `2026_08_28_100101`–`100106` |
| `branch-and-level-rank-manual.sql` | `2026_08_25_100000`, `2026_08_29_190000`, `2026_08_29_191500` |
| `employee-data-tabs-manual.sql` | `2026_08_28_100201`–`100208` |
| `approval-engine-manual.sql` | `2026_08_28_100301`–`100305`, `100311`–`100314` |
| `role-permission-manual.sql` | `2026_08_28_100501` |
| `payroll-pph21-manual.sql` | `2026_08_28_100601`–`100603` |
| `kasbon-bonus-manual.sql` | `2026_08_29_170000`, `170100`, `170200` |
| `thr-manual.sql` | `2026_08_28_101201` |
| `overtime-request-manual.sql` | `2026_08_28_101101` |
| `manpower-planning-manual.sql` | `2026_08_28_100701` |
| `recruitment-manual.sql` | `2026_08_28_100801`–`100803`, `2026_08_31_130000` (materi + konfirmasi karyawan di onboarding) |
| `training-career-manual.sql` | `2026_08_28_100901`–`100905` |
| `competency-manual.sql` | `2026_08_29_150000` |
| `employee-administration-manual.sql` | `2026_08_28_101001`–`101003` |
| `appraisal-kpi-manual.sql` | `2026_08_28_101301` |
| `approval-audit-region-manual.sql` | `2026_08_29_160000`, `161000`, `161100` |
| `gap-closure-manual.sql` | `2026_08_29_140000`, `141000` |
| `approval-status-normalisasi-manual.sql` | `2026_08_28_100401`–`100403` |
| `shift-leave-policy-manual.sql` | `2026_08_29_180000`, `180100` |
| `offboarding-letter-request-manual.sql` | `2026_08_29_180200`, `180300` |
| `engagement-analytics-manual.sql` | `2026_08_29_180400`, `180500`, `180600` |
| `job-requisition-budget-control-manual.sql` | `2026_08_31_100000` |
| `candidate-database-ats-manual.sql` | `2026_08_31_110000` |
| `preemployment-manual.sql` | `2026_08_31_120000` |
| `employee-document-management-manual.sql` | `2026_08_31_140000`, `2026_08_31_150000` |
| `pph21-ter-rate-correction-manual.sql` | (koreksi data, bukan migration — lihat catatan di TAHAP 7) |
| `performance-management-extras-manual.sql` | `2026_08_31_170000` |
| `compensation-succession-survey-extras-manual.sql` | `2026_08_31_180000` |
| `notification-talent-extras-manual.sql` | `2026_09_01_100000` |
| `gap4-extras-manual.sql` | `2026_09_01_110000` |
| `mitra-employee-type-manual.sql` | `2026_09_01_120000` |
| `fix-level-rank-manual.sql` | `2026_09_08_100000` |
| `tds-bod-commercial-logistik-positions-manual.sql` | `2026_09_08_110000` |
