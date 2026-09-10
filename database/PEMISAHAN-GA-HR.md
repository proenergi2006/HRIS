# Pemisahan Tabel — GA vs HR (untuk deploy ke Produksi)

Tujuan: memastikan bundel deploy HR yang dikirim ke produksi **tidak menyentuh**
tabel milik General Affairs (GA) yang sudah live.

Kesimpulan singkat:

- **Tabel GA = 11 tabel** (daftar di bawah). Semuanya **TIDAK** ada di
  `database/deploy-all-produksi.sql` — sudah aman.
- Tidak ada satu pun migration / `*-manual.sql` HR yang `CREATE`/`ALTER`/`DROP`
  tabel GA. Tidak ada foreign key HR → GA. Boundary-nya bersih.
- Satu-satunya titik singgung: `vault_document_transactions.created_by` → `users.id`
  dan login `ga@proenergi.co.id` (role `admin_ga`). Tabel `users` dipakai bersama,
  tapi bundel HR tidak meng-`ALTER` `users`.

---

## 1. Tabel milik GA — JANGAN ikut dikirim / diubah

Modul GA: Kendaraan (check-in/out QR), Ruang Meeting (checklist kebersihan QR),
Barcode Dokumen Brankas. Permission modul: `ga.*` (role `admin_ga` / `admin`).

| # | Tabel | Modul GA | File skema |
|---|-------|----------|------------|
| 1 | `vehicles` | Kendaraan | migration `2026_06_19_063153` (belum ada `*-manual.sql`) |
| 2 | `vehicle_usages` | Kendaraan | migration `2026_06_19_063200` |
| 3 | `meeting_rooms` | Ruang Meeting | migration `2026_06_19_080000` |
| 4 | `room_cleaning_items` | Ruang Meeting | migration `2026_06_19_080001` |
| 5 | `room_cleaning_logs` | Ruang Meeting | migration `2026_06_19_080002` |
| 6 | `room_cleaning_log_details` | Ruang Meeting | migration `2026_06_19_080003` |
| 7 | `room_cleaning_photos` | Ruang Meeting | migration `2026_06_19_080004` |
| 8 | `vault_document_categories` | Brankas | `vault-tables.sql` |
| 9 | `vaults` | Brankas | `vault-tables.sql` / `vault-tables-update-berangkas.sql` |
| 10 | `vault_documents` | Brankas | `vault-tables.sql` (+ kolom `vault_id`) |
| 11 | `vault_document_transactions` | Brankas | `vault-tables.sql` |

File SQL khusus GA (jangan jalankan lagi kalau modul GA sudah live):
`database/vault-tables.sql`, `database/vault-tables-update-berangkas.sql`.

---

## 2. Whistleblower — bukan GA, bukan HR (biarkan apa adanya)

| Tabel | Keterangan |
|-------|------------|
| `whistleblower_reports` | Modul publik/compliance, permission terpisah `whistleblower-admin.*`. Sudah live sejak Juni 2026. Bundel HR tidak menyentuhnya. |

---

## 3. Yang dikirim ke produksi untuk HR

Semua di luar tabel GA & whistleblower di atas. Sudah dibundel jadi satu file:

- **`database/deploy-all-produksi.sql`** — gabungan 27 `*-manual.sql` HRIS v1.2
  dalam urutan jalan (98 tabel baru + 20 tabel lama di-ALTER).
- Runbook langkah per langkah: **`database/DEPLOY-PRODUKSI.md`**
- Rincian tabel/kolom: **`database/SKEMA-PERUBAHAN.md`**
- Seeder artisan wajib (lihat DEPLOY-PRODUKSI.md §6): `RegionSeeder`,
  `RegionDistrictSeeder`, `ApprovalWorkflowSeeder`, `CompetencySeeder`,
  `LeavePolicySeeder`, checklist seeders, `PermissionCatalogSeeder` (terakhir).

### Prasyarat baseline (kalau DB prod masih lama)

DEPLOY-PRODUKSI.md §0 mengasumsikan tabel-tabel ini **sudah ada**. Kalau belum,
jalankan file-nya dulu sebelum `deploy-all-produksi.sql`:

| Baseline sudah ada? | File kalau belum |
|---|---|
| `employees`, `companies`, `departments`, `positions`, `levels`, `salary_components` | `employee-master-data-manual.sql`, `master-data-manual.sql` |
| `attendance_records`, `leave_requests`, `leave_balances`, `payroll_slips` | `hr-module-manual.sql` |
| `perdin_requests`, `reimbursement_requests` | (rilis sebelumnya) |
| `employee_family_members` | `employee-family-members-manual.sql` |
| `employee_documents` | `2026_07_02_100000` |

---

## 4. Verifikasi boundary (sudah dicek 2026-09-10)

```
grep -niE "vault|vehicle|meeting_room|room_cleaning|whistle" database/deploy-all-produksi.sql
  → 0 hasil

grep -rniE "references.*(vaults|vehicles|meeting_rooms)" database/migrations/
  → 0 hasil
```

Aman untuk kirim `deploy-all-produksi.sql` ke produksi tanpa risiko ke data GA.
