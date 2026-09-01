# Skema Perubahan Database — HRIS v1.2 (Fase 2 HRD + Rebuild Organisasi)

Daftar tabel BARU dan kolom yang DITAMBAHKAN/DIUBAH/DIHAPUS ke tabel lama.

Legend: 🆕 tabel baru · ➕ kolom ditambah · ✏️ kolom diubah tipe · 🔤 kolom di-rename · ❌ kolom dihapus

**Ringkasan: 94 tabel baru + 20 tabel lama di-ALTER.** Semua tabel baru punya `id` bigint unsigned PK auto-increment
dan `created_at`/`updated_at` timestamp (kecuali disebut lain). FK & index tidak
dirinci di sini — lihat file `.sql` masing-masing untuk `CONSTRAINT`/`KEY` lengkap.

### Tabel lama yang kena ALTER
| Tabel | Perubahan |
|---|---|
| `employees` | +18 kolom FK (religion_id, marital_status_id, blood_type_id, employee_type_id, division_id, section_id, branch_id, career_path_id, domicile/ktp × province/city/district/village _id); `ktp_number`/`npwp_number` → text (enkripsi); ❌ `marital_status`, `religion_name`, `blood_type` |
| `departments` | + `division_id`, `head_employee_id`, `cost_center` |
| `positions` | + `section_id`, `level_id`, `branch_id`, `reports_to_position_id`, `job_description` |
| `levels` | + `rank` |
| `companies` | + `tax_signer_name`, `tax_signer_npwp` |
| `salary_components` | enum `calculation_type` + `pph21_ter`, `loan_installment` |
| `employee_bank_accounts` | `account_number` → text (enkripsi) |
| `appraisal_templates` | ❌ `scoring_type` |
| `appraisals` | ❌ 11 kolom model lama; + `development_notes`; `strength_points`→`strengths`; `status`/`total_score`/`appraisal_template_id` diubah |
| `leave_requests` | `status` enum → varchar(30) |
| `perdin_requests` | `status` enum → varchar(30) |
| `reimbursement_requests` | (nilai `status` saja) |
| `job_requisitions` | + `request_type`, `manpower_plan_id` (FK), `replaces_employee_id` (FK) |
| `candidates` | + `expected_salary`, `assessment_result`, `assessment_score`, `assessment_notes` |
| _(candidates)_ | Pre-Employment: relasi 1:1 `candidate_preemployment` + `candidate_preemployment_tasks` (tabel baru, bukan kolom) |
| `onboarding_checklist_items` | + `description`, `material_path`, `material_original_name`, `material_url`, `requires_acknowledgement` |
| `employee_onboarding_tasks` | + `acknowledged_at`, `acknowledgement_note` |
| `employee_documents` | + `group` (folder Personal/Employment/Movement/Development/Exit) |
| `termination_requests` | + `exit_interview_date/notes/by_user_id`, `final_settlement_amount/date/notes` |
| `appraisal_objectives` | + `company_objective_id` (tautan ke OKR — kolom siap, UI penautan belum dikerjakan) |


## Master Data referensi
`master-data-manual.sql`

### 🆕 `religions`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `code` — varchar(20) NOT NULL
- `name` — varchar(100) NOT NULL
- `sort_order` — int(11) NOT NULL DEFAULT 0
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `education_levels`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `code` — varchar(20) NOT NULL
- `name` — varchar(100) NOT NULL
- `sort_order` — tinyint(3) unsigned NOT NULL DEFAULT 0
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `education_majors`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `code` — varchar(20) NOT NULL
- `name` — varchar(150) NOT NULL
- `sort_order` — int(11) NOT NULL DEFAULT 0
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `marital_statuses`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `code` — varchar(20) NOT NULL
- `name` — varchar(100) NOT NULL
- `ptkp_code` — varchar(10) DEFAULT NULL
- `legacy_key` — varchar(30) DEFAULT NULL
- `sort_order` — int(11) NOT NULL DEFAULT 0
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `blood_types`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `code` — varchar(10) NOT NULL
- `name` — varchar(20) NOT NULL
- `sort_order` — int(11) NOT NULL DEFAULT 0
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `employee_types`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `code` — varchar(20) NOT NULL
- `name` — varchar(100) NOT NULL
- `legacy_key` — varchar(30) DEFAULT NULL
- `sort_order` — int(11) NOT NULL DEFAULT 0
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `banks`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `code` — varchar(20) NOT NULL
- `name` — varchar(150) NOT NULL
- `swift_code` — varchar(20) DEFAULT NULL
- `sort_order` — int(11) NOT NULL DEFAULT 0
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `company_banks`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `company_id` — bigint(20) unsigned NOT NULL
- `bank_id` — bigint(20) unsigned NOT NULL
- `account_number` — varchar(50) NOT NULL
- `account_name` — varchar(150) NOT NULL
- `branch_name` — varchar(150) DEFAULT NULL
- `is_primary` — tinyint(1) NOT NULL DEFAULT 0
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `provinces`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `code` — varchar(10) NOT NULL
- `name` — varchar(100) NOT NULL
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `cities`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `province_id` — bigint(20) unsigned NOT NULL
- `code` — varchar(10) NOT NULL
- `name` — varchar(100) NOT NULL
- `type` — enum('kota','kabupaten') NOT NULL DEFAULT 'kabupaten'
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### `employees` (tabel lama)

- ➕ `religion_id` — bigint(20) unsigned DEFAULT NULL
- ➕ `marital_status_id` — bigint(20) unsigned DEFAULT NULL
- ➕ `blood_type_id` — bigint(20) unsigned DEFAULT NULL
- ➕ `employee_type_id` — bigint(20) unsigned DEFAULT NULL
- ➕ `domicile_province_id` — bigint(20) unsigned DEFAULT NULL
- ➕ `domicile_city_id` — bigint(20) unsigned DEFAULT NULL
- ➕ `ktp_province_id` — bigint(20) unsigned DEFAULT NULL
- ➕ `ktp_city_id` — bigint(20) unsigned DEFAULT NULL


## Master Organization
`master-organization-manual.sql`

### 🆕 `divisions`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `company_id` — bigint(20) unsigned NOT NULL
- `code` — varchar(20) NOT NULL
- `name` — varchar(150) NOT NULL
- `head_employee_id` — bigint(20) unsigned DEFAULT NULL
- `cost_center` — varchar(50) DEFAULT NULL
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `sections`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `department_id` — bigint(20) unsigned NOT NULL
- `code` — varchar(20) NOT NULL
- `name` — varchar(150) NOT NULL
- `head_employee_id` — bigint(20) unsigned DEFAULT NULL
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### `departments` (tabel lama)

- ➕ `division_id` — bigint(20) unsigned DEFAULT NULL
- ➕ `head_employee_id` — bigint(20) unsigned DEFAULT NULL
- ➕ `cost_center` — varchar(50) DEFAULT NULL

### `positions` (tabel lama)

- ➕ `section_id` — bigint(20) unsigned DEFAULT NULL
- ➕ `level_id` — bigint(20) unsigned DEFAULT NULL
- ➕ `reports_to_position_id` — bigint(20) unsigned DEFAULT NULL
- ➕ `job_description` — text DEFAULT NULL

### `employees` (tabel lama)

- ➕ `division_id` — bigint(20) unsigned DEFAULT NULL
- ➕ `section_id` — bigint(20) unsigned DEFAULT NULL

### 🆕 `org_change_logs`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `unit_type` — enum('division','department','section','position') NOT NULL
- `unit_id` — bigint(20) unsigned NOT NULL
- `unit_name` — varchar(150) DEFAULT NULL
- `action` — enum('created','updated','moved','deactivated','deleted') NOT NULL
- `changes` — longtext DEFAULT NULL CHECK (json_valid(`changes`))
- `effective_date` — date NOT NULL
- `changed_by` — bigint(20) unsigned DEFAULT NULL
- `note` — varchar(255) DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL


## Cabang + urutan Level
`branch-and-level-rank-manual.sql`

### `levels` (tabel lama)

- ➕ `rank` — smallint(5) unsigned NOT NULL DEFAULT 99

### 🆕 `branches`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `company_id` — bigint(20) unsigned NOT NULL
- `code` — varchar(20) NOT NULL
- `name` — varchar(100) NOT NULL
- `head_employee_id` — bigint(20) unsigned DEFAULT NULL
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### `employees` (tabel lama)

- ➕ `branch_id` — bigint(20) unsigned DEFAULT NULL

### `positions` (tabel lama)

- ➕ `branch_id` — bigint(20) unsigned DEFAULT NULL


## Tab Data Karyawan
`employee-data-tabs-manual.sql`

### 🆕 `employee_nssf`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `employee_id` — bigint(20) unsigned NOT NULL
- `health_registered` — tinyint(1) NOT NULL DEFAULT 0
- `health_number` — varchar(50) DEFAULT NULL
- `health_join_date` — date DEFAULT NULL
- `employment_registered` — tinyint(1) NOT NULL DEFAULT 0
- `employment_number` — varchar(50) DEFAULT NULL
- `employment_join_date` — date DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `employee_educations`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `employee_id` — bigint(20) unsigned NOT NULL
- `education_level_id` — bigint(20) unsigned DEFAULT NULL
- `education_major_id` — bigint(20) unsigned DEFAULT NULL
- `institution` — varchar(200) DEFAULT NULL
- `graduation_year` — year(4) DEFAULT NULL
- `gpa` — decimal(4,2) DEFAULT NULL
- `notes` — varchar(255) DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `employee_work_experiences`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `employee_id` — bigint(20) unsigned NOT NULL
- `company_name` — varchar(200) NOT NULL
- `company_city` — varchar(100) DEFAULT NULL
- `phone` — varchar(30) DEFAULT NULL
- `start_date` — date DEFAULT NULL
- `end_date` — date DEFAULT NULL
- `end_job_title` — varchar(150) DEFAULT NULL
- `end_pay_rate` — bigint(20) DEFAULT NULL
- `job_description` — text DEFAULT NULL
- `remarks` — text DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `employee_skills`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `employee_id` — bigint(20) unsigned NOT NULL
- `name` — varchar(150) NOT NULL
- `proficiency` — enum('basic','intermediate','advanced','expert') DEFAULT NULL
- `notes` — varchar(255) DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `employee_org_experiences`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `employee_id` — bigint(20) unsigned NOT NULL
- `company_id` — bigint(20) unsigned DEFAULT NULL
- `position_id` — bigint(20) unsigned DEFAULT NULL
- `unit_name` — varchar(200) DEFAULT NULL
- `position_name` — varchar(150) DEFAULT NULL
- `change_type` — enum('join','promotion','rotation','mutation','demotion','other') NOT NULL DEFAULT 'other'
- `start_date` — date NOT NULL
- `end_date` — date DEFAULT NULL
- `remarks` — text DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `employee_facilities`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `employee_id` — bigint(20) unsigned NOT NULL
- `name` — varchar(150) NOT NULL
- `description` — varchar(255) DEFAULT NULL
- `received_date` — date DEFAULT NULL
- `returned_date` — date DEFAULT NULL
- `remarks` — text DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `employee_bank_accounts`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `employee_id` — bigint(20) unsigned NOT NULL
- `bank_id` — bigint(20) unsigned NOT NULL
- `account_number` — varchar(50) NOT NULL
- `account_holder_name` — varchar(150) NOT NULL
- `branch_name` — varchar(150) DEFAULT NULL
- `is_primary` — tinyint(1) NOT NULL DEFAULT 0
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `employee_contracts`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `employee_id` — bigint(20) unsigned NOT NULL
- `contract_type` — enum('pkwtt','pkwt','probation','magang','harian','other') NOT NULL DEFAULT 'pkwt'
- `number` — varchar(100) DEFAULT NULL
- `start_date` — date NOT NULL
- `end_date` — date DEFAULT NULL
- `status` — enum('active','expired','terminated','renewed') NOT NULL DEFAULT 'active'
- `document_path` — varchar(500) DEFAULT NULL
- `original_name` — varchar(255) DEFAULT NULL
- `notes` — text DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL


## Approval Engine
`approval-engine-manual.sql`

### 🆕 `approval_workflows`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `company_id` — bigint(20) unsigned NOT NULL
- `transaction_type` — varchar(50) NOT NULL
- `name` — varchar(150) NOT NULL
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `approval_workflow_steps`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `approval_workflow_id` — bigint(20) unsigned NOT NULL
- `step_order` — tinyint(3) unsigned NOT NULL
- `approver_type` — enum('direct_manager','section_head','department_head','division_head','specific_position','specific_role') NOT NULL
- `approver_position_id` — bigint(20) unsigned DEFAULT NULL
- `approver_role` — varchar(50) DEFAULT NULL
- `conditions` — longtext DEFAULT NULL CHECK (json_valid(`conditions`))
- `escalate_after_days` — smallint(5) unsigned DEFAULT NULL
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `approval_requests`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `approvable_type` — varchar(255) NOT NULL
- `approvable_id` — bigint(20) unsigned NOT NULL
- `approval_workflow_id` — bigint(20) unsigned DEFAULT NULL
- `company_id` — bigint(20) unsigned DEFAULT NULL
- `transaction_type` — varchar(50) NOT NULL
- `summary` — varchar(255) DEFAULT NULL
- `requester_user_id` — bigint(20) unsigned DEFAULT NULL
- `subject_employee_id` — bigint(20) unsigned DEFAULT NULL
- `status` — enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending'
- `current_step_order` — tinyint(3) unsigned NOT NULL DEFAULT 0
- `submitted_at` — timestamp NULL DEFAULT NULL
- `completed_at` — timestamp NULL DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `approval_request_steps`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `approval_request_id` — bigint(20) unsigned NOT NULL
- `step_order` — tinyint(3) unsigned NOT NULL
- `approver_type` — enum('direct_manager','section_head','department_head','division_head','specific_position','specific_role') NOT NULL
- `approver_label` — varchar(150) DEFAULT NULL
- `approver_user_id` — bigint(20) unsigned DEFAULT NULL
- `status` — enum('pending','approved','rejected','skipped') NOT NULL DEFAULT 'pending'
- `acted_by_user_id` — bigint(20) unsigned DEFAULT NULL
- `acted_at` — timestamp NULL DEFAULT NULL
- `notes` — varchar(500) DEFAULT NULL
- `due_at` — timestamp NULL DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `approval_delegations`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `delegator_user_id` — bigint(20) unsigned NOT NULL
- `delegate_user_id` — bigint(20) unsigned NOT NULL
- `start_date` — date NOT NULL
- `end_date` — date NOT NULL
- `reason` — varchar(255) DEFAULT NULL
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `reward_requests`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `employee_id` — bigint(20) unsigned NOT NULL
- `company_id` — bigint(20) unsigned DEFAULT NULL
- `requested_by_user_id` — bigint(20) unsigned DEFAULT NULL
- `reward_type` — varchar(100) NOT NULL
- `description` — text DEFAULT NULL
- `effective_date` — date DEFAULT NULL
- `amount` — bigint(20) DEFAULT NULL
- `status` — enum('draft','pending','approved','rejected','cancelled') NOT NULL DEFAULT 'draft'
- `notes` — text DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `punishment_requests`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `employee_id` — bigint(20) unsigned NOT NULL
- `company_id` — bigint(20) unsigned DEFAULT NULL
- `requested_by_user_id` — bigint(20) unsigned DEFAULT NULL
- `violation_type` — varchar(150) NOT NULL
- `sanction_level` — enum('teguran_lisan','sp1','sp2','sp3','demosi','phk','other') NOT NULL DEFAULT 'sp1'
- `description` — text DEFAULT NULL
- `incident_date` — date DEFAULT NULL
- `effective_date` — date DEFAULT NULL
- `status` — enum('draft','pending','approved','rejected','cancelled') NOT NULL DEFAULT 'draft'
- `notes` — text DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `promotion_rotation_requests`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `employee_id` — bigint(20) unsigned NOT NULL
- `company_id` — bigint(20) unsigned DEFAULT NULL
- `requested_by_user_id` — bigint(20) unsigned DEFAULT NULL
- `request_type` — enum('promotion','rotation','mutation','demotion') NOT NULL DEFAULT 'promotion'
- `from_position_id` — bigint(20) unsigned DEFAULT NULL
- `to_position_id` — bigint(20) unsigned DEFAULT NULL
- `from_company_id` — bigint(20) unsigned DEFAULT NULL
- `to_company_id` — bigint(20) unsigned DEFAULT NULL
- `effective_date` — date DEFAULT NULL
- `reason` — text DEFAULT NULL
- `status` — enum('draft','pending','approved','rejected','cancelled') NOT NULL DEFAULT 'draft'
- `notes` — text DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `termination_requests`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `employee_id` — bigint(20) unsigned NOT NULL
- `company_id` — bigint(20) unsigned DEFAULT NULL
- `requested_by_user_id` — bigint(20) unsigned DEFAULT NULL
- `termination_type` — enum('resign','pkwt_end','dismissal','retirement','deceased','other') NOT NULL DEFAULT 'resign'
- `reason` — text DEFAULT NULL
- `last_working_date` — date DEFAULT NULL
- `effective_date` — date DEFAULT NULL
- `status` — enum('draft','pending','approved','rejected','cancelled') NOT NULL DEFAULT 'draft'
- `notes` — text DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL


## Role & Permission
`role-permission-manual.sql`

### 🆕 `role_company_assignments`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `user_id` — bigint(20) unsigned NOT NULL
- `role_id` — bigint(20) unsigned NOT NULL
- `company_id` — bigint(20) unsigned DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL


## Payroll PPh21 (TER)
`payroll-pph21-manual.sql`

### 🆕 `ter_categories`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `code` — varchar(5) NOT NULL
- `name` — varchar(100) NOT NULL
- `description` — varchar(255) DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `ter_brackets`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `ter_category_id` — bigint(20) unsigned NOT NULL
- `income_from` — bigint(20) unsigned NOT NULL
- `income_to` — bigint(20) unsigned DEFAULT NULL
- `rate_percent` — decimal(5,2) NOT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### `salary_components` (tabel lama)

- ✏️ `calculation_type` → tambah nilai enum `pph21_ter`
- (data) komponen "Potongan PPh 21" di-set `calculation_type='pph21_ter'`, `is_taxable=0`


## Kasbon / Bonus / Bukti Potong
`kasbon-bonus-manual.sql`

### `salary_components` (tabel lama)

- ✏️ `calculation_type` → tambah nilai enum `loan_installment`. Enum lengkap jadi:
  `manual, percent_of_base, late_deduction, medical_claim, mirror_pph21, position_fixed, position_daily, overtime, pph21_ter, loan_installment`
- (data) komponen baru "Potongan Kasbon/Pinjaman" (`type=deduction`, `calculation_type=loan_installment`)

### `companies` (tabel lama)

- ➕ `tax_signer_name` — varchar(150) DEFAULT NULL (setelah `npwp`)
- ➕ `tax_signer_npwp` — varchar(30) DEFAULT NULL

### 🆕 `employee_loans`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `employee_id` — bigint(20) unsigned NOT NULL
- `company_id` — bigint(20) unsigned NOT NULL
- `loan_type` — varchar(20) NOT NULL DEFAULT 'kasbon'
- `reference_no` — varchar(50) DEFAULT NULL
- `principal` — bigint(20) NOT NULL
- `installment_count` — smallint(5) unsigned NOT NULL
- `installment_amount` — bigint(20) NOT NULL
- `start_month` — tinyint(3) unsigned NOT NULL
- `start_year` — smallint(5) unsigned NOT NULL
- `status` — varchar(20) NOT NULL DEFAULT 'active'
- `notes` — text DEFAULT NULL
- `created_by` — bigint(20) unsigned DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `loan_installments`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `employee_loan_id` — bigint(20) unsigned NOT NULL
- `payroll_slip_id` — bigint(20) unsigned DEFAULT NULL
- `period_month` — tinyint(3) unsigned NOT NULL
- `period_year` — smallint(5) unsigned NOT NULL
- `amount` — bigint(20) NOT NULL
- `status` — varchar(20) NOT NULL DEFAULT 'pending'
- `deducted_at` — datetime DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `bonus_periods`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `company_id` — bigint(20) unsigned NOT NULL
- `name` — varchar(120) NOT NULL
- `bonus_type` — varchar(20) NOT NULL DEFAULT 'bonus'
- `payment_date` — date NOT NULL
- `is_taxable` — tinyint(1) NOT NULL DEFAULT 1
- `status` — varchar(20) NOT NULL DEFAULT 'open'
- `closed_by` — bigint(20) unsigned DEFAULT NULL
- `closed_at` — datetime DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `bonus_payments`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `bonus_period_id` — bigint(20) unsigned NOT NULL
- `employee_id` — bigint(20) unsigned NOT NULL
- `base_amount` — bigint(20) DEFAULT NULL
- `gross_amount` — bigint(20) NOT NULL DEFAULT 0
- `tax_amount` — bigint(20) NOT NULL DEFAULT 0
- `net_amount` — bigint(20) NOT NULL DEFAULT 0
- `notes` — text DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL


## THR
`thr-manual.sql`

### 🆕 `thr_periods`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `company_id` — bigint(20) unsigned NOT NULL
- `year` — smallint(5) unsigned NOT NULL
- `holiday_name` — varchar(100) NOT NULL
- `payment_date` — date NOT NULL
- `status` — varchar(20) NOT NULL DEFAULT 'open'
- `closed_by` — bigint(20) unsigned DEFAULT NULL
- `closed_at` — datetime DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `thr_payments`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `thr_period_id` — bigint(20) unsigned NOT NULL
- `employee_id` — bigint(20) unsigned NOT NULL
- `base_salary` — bigint(20) NOT NULL
- `months_worked` — tinyint(3) unsigned NOT NULL
- `proration_ratio` — decimal(4,3) NOT NULL
- `thr_amount` — bigint(20) NOT NULL
- `notes` — text DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL


## Pengajuan Lembur
`overtime-request-manual.sql`

### 🆕 `overtime_requests`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `employee_id` — bigint(20) unsigned NOT NULL
- `company_id` — bigint(20) unsigned DEFAULT NULL
- `requested_by_user_id` — bigint(20) unsigned DEFAULT NULL
- `date` — date NOT NULL
- `planned_hours` — decimal(4,1) NOT NULL
- `reason` — text DEFAULT NULL
- `status` — varchar(20) NOT NULL DEFAULT 'draft'
- `notes` — text DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL


## Manpower Planning
`manpower-planning-manual.sql`

### 🆕 `manpower_plans`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `company_id` — bigint(20) unsigned NOT NULL
- `department_id` — bigint(20) unsigned DEFAULT NULL
- `section_id` — bigint(20) unsigned DEFAULT NULL
- `position_id` — bigint(20) unsigned DEFAULT NULL
- `year` — smallint(5) unsigned NOT NULL
- `month` — tinyint(3) unsigned DEFAULT NULL
- `planned_headcount` — int(10) unsigned NOT NULL
- `notes` — text DEFAULT NULL
- `status` — varchar(20) NOT NULL DEFAULT 'draft'
- `requested_by_user_id` — bigint(20) unsigned DEFAULT NULL
- `notes_rejection` — text DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL


## Recruitment + Onboarding
`recruitment-manual.sql`

### 🆕 `job_requisitions`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `company_id` — bigint(20) unsigned NOT NULL
- `department_id` — bigint(20) unsigned DEFAULT NULL
- `section_id` — bigint(20) unsigned DEFAULT NULL
- `position_id` — bigint(20) unsigned DEFAULT NULL
- `title` — varchar(150) NOT NULL
- `reason` — text DEFAULT NULL
- `headcount_requested` — int(10) unsigned NOT NULL DEFAULT 1
- `employment_type_id` — bigint(20) unsigned DEFAULT NULL
- `target_join_date` — date DEFAULT NULL
- `status` — varchar(20) NOT NULL DEFAULT 'draft'
- `requested_by_user_id` — bigint(20) unsigned DEFAULT NULL
- `notes_rejection` — text DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `candidates`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `job_requisition_id` — bigint(20) unsigned DEFAULT NULL
- `name` — varchar(150) NOT NULL
- `email` — varchar(150) DEFAULT NULL
- `phone` — varchar(30) DEFAULT NULL
- `source` — varchar(100) DEFAULT NULL
- `status` — varchar(20) NOT NULL DEFAULT 'applied'
- `mcu_result` — varchar(20) DEFAULT NULL
- `converted_employee_id` — bigint(20) unsigned DEFAULT NULL
- `notes` — text DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `candidate_interviews`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `candidate_id` — bigint(20) unsigned NOT NULL
- `stage` — varchar(100) NOT NULL
- `scheduled_at` — datetime DEFAULT NULL
- `interviewer_employee_id` — bigint(20) unsigned DEFAULT NULL
- `result` — varchar(20) NOT NULL DEFAULT 'pending'
- `notes` — text DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `candidate_offers`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `candidate_id` — bigint(20) unsigned NOT NULL
- `position_id` — bigint(20) unsigned DEFAULT NULL
- `offered_salary` — bigint(20) DEFAULT NULL
- `start_date_offered` — date DEFAULT NULL
- `status` — varchar(20) NOT NULL DEFAULT 'draft'
- `notes` — text DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `candidate_documents`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `candidate_id` — bigint(20) unsigned NOT NULL
- `doc_type` — varchar(60) NOT NULL
- `title` — varchar(200) NOT NULL
- `file_path` — varchar(255) NOT NULL
- `original_name` — varchar(255) DEFAULT NULL
- `notes` — text DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `onboarding_checklist_items`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `company_id` — bigint(20) unsigned DEFAULT NULL
- `label` — varchar(200) NOT NULL
- `category` — varchar(20) NOT NULL
- `is_required` — tinyint(1) NOT NULL DEFAULT 1
- `sort_order` — int(10) unsigned NOT NULL DEFAULT 0
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `employee_onboarding_tasks`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `employee_id` — bigint(20) unsigned NOT NULL
- `onboarding_checklist_item_id` — bigint(20) unsigned NOT NULL
- `is_done` — tinyint(1) NOT NULL DEFAULT 0
- `done_at` — datetime DEFAULT NULL
- `done_by_user_id` — bigint(20) unsigned DEFAULT NULL
- `notes` — text DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL


## Training & Career
`training-career-manual.sql`

### 🆕 `training_programs`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `company_id` — bigint(20) unsigned DEFAULT NULL
- `title` — varchar(200) NOT NULL
- `category` — varchar(100) DEFAULT NULL
- `provider` — varchar(150) DEFAULT NULL
- `duration_hours` — int(10) unsigned DEFAULT NULL
- `description` — text DEFAULT NULL
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `training_participants`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `training_program_id` — bigint(20) unsigned NOT NULL
- `employee_id` — bigint(20) unsigned NOT NULL
- `start_date` — date NOT NULL
- `end_date` — date DEFAULT NULL
- `status` — varchar(20) NOT NULL DEFAULT 'planned'
- `score` — varchar(20) DEFAULT NULL
- `certificate_number` — varchar(100) DEFAULT NULL
- `notes` — text DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `career_paths`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `company_id` — bigint(20) unsigned DEFAULT NULL
- `title` — varchar(150) NOT NULL
- `description` — text DEFAULT NULL
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `career_path_steps`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `career_path_id` — bigint(20) unsigned NOT NULL
- `position_id` — bigint(20) unsigned NOT NULL
- `step_order` — int(10) unsigned NOT NULL DEFAULT 0
- `notes` — text DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### `employees` (tabel lama)

- ➕ `career_path_id` — bigint(20) unsigned DEFAULT NULL


## Competency Framework
`competency-manual.sql`

### 🆕 `competencies`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `company_id` — bigint(20) unsigned DEFAULT NULL
- `code` — varchar(30) NOT NULL
- `name` — varchar(150) NOT NULL
- `category` — varchar(50) DEFAULT NULL
- `description` — text DEFAULT NULL
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `position_competencies`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `position_id` — bigint(20) unsigned NOT NULL
- `competency_id` — bigint(20) unsigned NOT NULL
- `required_level` — tinyint(3) unsigned NOT NULL
- `notes` — varchar(255) DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `employee_competencies`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `employee_id` — bigint(20) unsigned NOT NULL
- `competency_id` — bigint(20) unsigned NOT NULL
- `actual_level` — tinyint(3) unsigned NOT NULL
- `assessed_on` — date DEFAULT NULL
- `assessor_user_id` — bigint(20) unsigned DEFAULT NULL
- `notes` — varchar(255) DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL


## Employee Administration (Surat, Perubahan Data)
`employee-administration-manual.sql`

### 🆕 `employee_data_change_requests`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `employee_id` — bigint(20) unsigned NOT NULL
- `requested_by_user_id` — bigint(20) unsigned DEFAULT NULL
- `field_key` — varchar(50) NOT NULL
- `old_value` — text DEFAULT NULL
- `new_value` — text NOT NULL
- `reason` — text DEFAULT NULL
- `status` — varchar(20) NOT NULL DEFAULT 'draft'
- `notes_rejection` — text DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `letter_templates`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `company_id` — bigint(20) unsigned DEFAULT NULL
- `title` — varchar(200) NOT NULL
- `category` — varchar(50) NOT NULL DEFAULT 'lainnya'
- `body` — longtext NOT NULL
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `employee_letters`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `employee_id` — bigint(20) unsigned NOT NULL
- `letter_template_id` — bigint(20) unsigned DEFAULT NULL
- `letter_number` — varchar(100) NOT NULL
- `category` — varchar(50) NOT NULL
- `title` — varchar(200) NOT NULL
- `body` — longtext NOT NULL
- `issued_date` — date NOT NULL
- `issued_by_user_id` — bigint(20) unsigned DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL


## Appraisal rebuild (KPI)
`appraisal-kpi-manual.sql`

### `appraisal_templates` (tabel lama)

- ❌ `scoring_type` (dihapus)

### 🆕 `appraisal_template_objectives`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `appraisal_template_id` — bigint(20) unsigned NOT NULL
- `title` — varchar(200) NOT NULL
- `description` — text DEFAULT NULL
- `category` — varchar(100) DEFAULT NULL
- `weight_pct` — tinyint(3) unsigned NOT NULL DEFAULT 0
- `order` — tinyint(3) unsigned NOT NULL DEFAULT 0
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### `appraisals` (tabel lama)

- ❌ dihapus: `score_self`, `score_atasan1`, `score_atasan2`, `score_ho`, `avg_late_per_month`, `avg_leave_per_month`, `warning_letter`, `sp_level`, `decision`, `individual_development_plan`, `development_need`
- ➕ `development_notes` — text DEFAULT NULL
- ✏️ `status` → varchar(20) NOT NULL DEFAULT 'draft'
- ✏️ `total_score` → decimal(6,2) NOT NULL DEFAULT 0.00
- ✏️ `appraisal_template_id` → bigint(20) unsigned DEFAULT NULL (jadi opsional)
- 🔤 `strength_points` di-rename jadi `strengths` (text DEFAULT NULL)

### 🆕 `appraisal_objectives`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `appraisal_id` — bigint(20) unsigned NOT NULL
- `title` — varchar(200) NOT NULL
- `description` — text DEFAULT NULL
- `category` — varchar(100) DEFAULT NULL
- `weight_pct` — tinyint(3) unsigned NOT NULL DEFAULT 0
- `target` — text DEFAULT NULL
- `actual` — text DEFAULT NULL
- `achievement_pct` — decimal(6,2) DEFAULT NULL
- `score` — decimal(6,2) DEFAULT NULL
- `order` — tinyint(3) unsigned NOT NULL DEFAULT 0
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL


## Approval audit + Wilayah (Kec/Kel)
`approval-audit-region-manual.sql`

### 🆕 `approval_workflow_change_logs`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `company_id` — bigint(20) unsigned NOT NULL
- `transaction_type` — varchar(50) NOT NULL
- `transaction_label` — varchar(100) DEFAULT NULL
- `action` — varchar(20) NOT NULL
- `before` — longtext DEFAULT NULL CHECK (json_valid(`before`))
- `after` — longtext DEFAULT NULL CHECK (json_valid(`after`))
- `changed_by` — bigint(20) unsigned DEFAULT NULL
- `note` — varchar(255) DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `districts`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `city_id` — bigint(20) unsigned NOT NULL
- `code` — varchar(15) NOT NULL
- `name` — varchar(100) NOT NULL
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `villages`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `district_id` — bigint(20) unsigned NOT NULL
- `code` — varchar(20) NOT NULL
- `name` — varchar(100) NOT NULL
- `type` — varchar(15) NOT NULL DEFAULT 'kelurahan'
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### `employees` (tabel lama)

- ➕ `domicile_district_id` — bigint(20) unsigned DEFAULT NULL
- ➕ `domicile_village_id` — bigint(20) unsigned DEFAULT NULL
- ➕ `ktp_district_id` — bigint(20) unsigned DEFAULT NULL
- ➕ `ktp_village_id` — bigint(20) unsigned DEFAULT NULL


## Enkripsi data sensitif + drop kolom lama
`gap-closure-manual.sql`

### `employees` (tabel lama)

- ✏️ `ktp_number` → text DEFAULT NULL
- ✏️ `npwp_number` → text DEFAULT NULL

### `employee_bank_accounts` (tabel lama)

- ✏️ `account_number` → text NOT NULL

### `employees` (tabel lama)

- ❌ `marital_status` (dihapus)
- ❌ `religion_name` (dihapus)
- ❌ `blood_type` (dihapus)


## Normalisasi status Cuti/Perdin/Reimbursement
`approval-status-normalisasi-manual.sql`

### `leave_requests` (tabel lama)

- ✏️ `status` → VARCHAR(30) NOT NULL DEFAULT 'draft'

### `perdin_requests` (tabel lama)

- ✏️ `status` → VARCHAR(30) NOT NULL DEFAULT 'draft'

### `reimbursement_requests` (tabel lama)

- (tanpa perubahan skema — kolom `status` sudah varchar; hanya normalisasi nilai `submitted` → `pending`)


## Shift & Roster + Kebijakan Cuti
`shift-leave-policy-manual.sql`

### 🆕 `shifts`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `company_id` — bigint(20) unsigned DEFAULT NULL
- `code` — varchar(20) NOT NULL
- `name` — varchar(60) NOT NULL
- `start_time` — time NOT NULL
- `end_time` — time NOT NULL
- `break_minutes` — smallint(6) NOT NULL DEFAULT 0
- `crosses_midnight` — tinyint(1) NOT NULL DEFAULT 0
- `late_grace_minutes` — smallint(6) NOT NULL DEFAULT 0
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `roster_entries`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `employee_id` — bigint(20) unsigned NOT NULL
- `company_id` — bigint(20) unsigned NOT NULL
- `work_date` — date NOT NULL
- `shift_id` — bigint(20) unsigned DEFAULT NULL
- `notes` — varchar(255) DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `leave_policies`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `company_id` — bigint(20) unsigned DEFAULT NULL
- `leave_type_id` — bigint(20) unsigned NOT NULL
- `level_id` — bigint(20) unsigned DEFAULT NULL
- `min_years_service` — tinyint(3) unsigned NOT NULL DEFAULT 0
- `quota_days` — decimal(5,1) NOT NULL
- `carry_forward_max_days` — decimal(5,1) NOT NULL DEFAULT 0.0
- `carry_forward_expire_month` — tinyint(3) unsigned NOT NULL DEFAULT 3
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL


## Offboarding + Permintaan Surat
`offboarding-letter-request-manual.sql`

### 🆕 `offboarding_checklist_items`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `company_id` — bigint(20) unsigned DEFAULT NULL
- `label` — varchar(200) NOT NULL
- `category` — varchar(20) NOT NULL
- `is_required` — tinyint(1) NOT NULL DEFAULT 1
- `sort_order` — int(10) unsigned NOT NULL DEFAULT 0
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `employee_offboarding_tasks`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `employee_id` — bigint(20) unsigned NOT NULL
- `offboarding_checklist_item_id` — bigint(20) unsigned NOT NULL
- `is_done` — tinyint(1) NOT NULL DEFAULT 0
- `done_at` — datetime DEFAULT NULL
- `done_by_user_id` — bigint(20) unsigned DEFAULT NULL
- `notes` — text DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `letter_requests`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `employee_id` — bigint(20) unsigned NOT NULL
- `requested_by_user_id` — bigint(20) unsigned DEFAULT NULL
- `letter_template_id` — bigint(20) unsigned DEFAULT NULL
- `purpose` — varchar(60) DEFAULT NULL
- `notes` — text DEFAULT NULL
- `status` — varchar(20) NOT NULL DEFAULT 'pending'
- `employee_letter_id` — bigint(20) unsigned DEFAULT NULL
- `handled_by_user_id` — bigint(20) unsigned DEFAULT NULL
- `handled_at` — datetime DEFAULT NULL
- `rejection_note` — varchar(255) DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL


## Candidate Database (ATS) — `candidate-database-ats-manual.sql`

`candidates` (tabel lama):
- ➕ `expected_salary` bigint(20) NULL
- ➕ `assessment_result` varchar(20) NULL  (pass / hold / fail)
- ➕ `assessment_score` decimal(5,2) NULL
- ➕ `assessment_notes` text NULL

🆕 `candidate_educations` — candidate_id, education_level varchar(50), major varchar(150), institution varchar(200), graduation_year year, gpa decimal(4,2), notes varchar(255)
🆕 `candidate_experiences` — candidate_id, company_name varchar(200), job_title varchar(150), company_city varchar(100), start_date, end_date, last_salary bigint, job_description text, notes varchar(255)
🆕 `candidate_skills` — candidate_id, name varchar(150), proficiency enum(basic/intermediate/advanced/expert), notes varchar(255)
🆕 `candidate_certifications` — candidate_id, name varchar(200), issuer varchar(150), issued_date, expires_date, credential_id varchar(100), notes varchar(255)

Saat kandidat dikonversi jadi karyawan, `educations`/`experiences`/`skills` disalin ke
`employee_educations` / `employee_work_experiences` / `employee_skills` (di kode).


## Pre-Employment — `preemployment-manual.sql`

🆕 `candidate_preemployment` (1:1 `candidates`) — gender (L/P), birth_place, birth_date, marital_status_id, religion_id, blood_type_id, ktp_number (enkripsi), npwp_number (enkripsi), ktp_address, ktp_city, domicile_address, domicile_city, bank_id, bank_account_number (enkripsi), bank_account_holder, bpjs_health_number, bpjs_health_date, bpjs_employment_number, bpjs_employment_date, emergency_contact_name/relation/phone, notes
🆕 `preemployment_checklist_items` — company_id (null=global), label varchar(200), category varchar(20) (dokumen/data/verifikasi), is_required, sort_order, is_active. +13 item default (Offer, Data pribadi, KTP, NPWP, KK, Rekening, BPJS ×2, Ijazah, SKCK, Paklaring, MCU, Pas foto)
🆕 `candidate_preemployment_tasks` — candidate_id, preemployment_checklist_item_id, is_done, done_at, done_by_user_id, notes (unique candidate+item)

Konversi kandidat → karyawan diblok sampai semua item `is_required` selesai. Data
pindah ke `employees` + `employee_bank_accounts` + `employee_nssf` (di kode).


## Employee Document Management + Exit Process — `employee-document-management-manual.sql`

`employee_documents` (tabel lama): ➕ `group` varchar(20) default 'lainnya' — folder Personal/
Employment/Movement/Development/Exit/Lainnya (backfill dari doc_type lama). Katalog `doc_type`
diperluas di kode (`EmployeeDocument::$docTypes`/`$docGroups`) — bukan tabel referensi, tidak
perlu migrasi tambahan: KK, Bank Rekening, Job Description, Amandemen Kontrak, Surat Promosi,
Surat Mutasi, Surat Peringatan, Sertifikat Training, Sertifikat Kompetensi, Hasil Assessment,
Surat Pengunduran Diri, Berita Acara Clearance, Final Settlement.

`termination_requests` (tabel lama): ➕ `exit_interview_date`, `exit_interview_notes`,
`exit_interview_by_user_id` (FK users), `final_settlement_amount`, `final_settlement_date`,
`final_settlement_notes`.

Folder Movement di halaman Dokumen Karyawan juga menautkan (bukan menduplikasi) surat
Promosi/Mutasi/Peringatan yang sudah digenerate lewat Manajemen Surat (`employee_letters`).
Document expiry alert (`documents:remind`) sudah ada sejak sebelumnya — tidak berubah.


## Job Requisition — tipe + Budget Control — `job-requisition-budget-control-manual.sql`

`job_requisitions` (tabel lama):
- ➕ `request_type` varchar(20) NOT NULL DEFAULT 'replacement'  (replacement / additional / new_position)
- ➕ `manpower_plan_id` bigint unsigned NULL → FK `manpower_plans` (ON DELETE SET NULL)
- ➕ `replaces_employee_id` bigint unsigned NULL → FK `employees` (ON DELETE SET NULL)

Budget Control ada di kode: requisition `additional`/`new_position` hanya bisa diajukan
bila `planned_headcount` MPP (disetujui) − aktual − sedang direkrut ≥ headcount diminta.


## Pengumuman + Survey + HR Analytics
`engagement-analytics-manual.sql`

### 🆕 `announcements`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `company_id` — bigint(20) unsigned DEFAULT NULL
- `title` — varchar(200) NOT NULL
- `body` — longtext NOT NULL
- `category` — varchar(20) NOT NULL DEFAULT 'info'
- `is_pinned` — tinyint(1) NOT NULL DEFAULT 0
- `published_at` — datetime DEFAULT NULL
- `expires_at` — date DEFAULT NULL
- `attachment_path` — varchar(500) DEFAULT NULL
- `attachment_name` — varchar(255) DEFAULT NULL
- `created_by_user_id` — bigint(20) unsigned DEFAULT NULL
- `is_active` — tinyint(1) NOT NULL DEFAULT 1
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `announcement_reads`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `announcement_id` — bigint(20) unsigned NOT NULL
- `user_id` — bigint(20) unsigned NOT NULL
- `read_at` — datetime NOT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `surveys`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `company_id` — bigint(20) unsigned DEFAULT NULL
- `title` — varchar(200) NOT NULL
- `description` — text DEFAULT NULL
- `is_anonymous` — tinyint(1) NOT NULL DEFAULT 1
- `status` — varchar(20) NOT NULL DEFAULT 'draft'
- `opens_at` — date DEFAULT NULL
- `closes_at` — date DEFAULT NULL
- `created_by_user_id` — bigint(20) unsigned DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `survey_questions`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `survey_id` — bigint(20) unsigned NOT NULL
- `text` — varchar(500) NOT NULL
- `type` — varchar(20) NOT NULL DEFAULT 'scale'
- `options` — longtext DEFAULT NULL CHECK (json_valid(`options`))
- `is_required` — tinyint(1) NOT NULL DEFAULT 1
- `sort_order` — int(10) unsigned NOT NULL DEFAULT 0
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `survey_responses`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `survey_id` — bigint(20) unsigned NOT NULL
- `user_id` — bigint(20) unsigned DEFAULT NULL
- `employee_id` — bigint(20) unsigned DEFAULT NULL
- `company_id` — bigint(20) unsigned DEFAULT NULL
- `department_id` — bigint(20) unsigned DEFAULT NULL
- `submitted_at` — datetime NOT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `survey_answers`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `survey_response_id` — bigint(20) unsigned NOT NULL
- `survey_question_id` — bigint(20) unsigned NOT NULL
- `value` — text DEFAULT NULL
- `value_json` — longtext DEFAULT NULL CHECK (json_valid(`value_json`))
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL

### 🆕 `recruitment_costs`

- `id` — bigint(20) unsigned NOT NULL AUTO_INCREMENT
- `company_id` — bigint(20) unsigned NOT NULL
- `job_requisition_id` — bigint(20) unsigned DEFAULT NULL
- `category` — varchar(40) NOT NULL
- `amount` — bigint(20) NOT NULL
- `incurred_on` — date NOT NULL
- `notes` — varchar(255) DEFAULT NULL
- `created_by` — bigint(20) unsigned DEFAULT NULL
- `created_at` — timestamp NULL DEFAULT NULL
- `updated_at` — timestamp NULL DEFAULT NULL



## Performance Management — pelengkap (1-on-1, OKR, 360°) — `performance-management-extras-manual.sql`

🆕 `performance_checkins` — employee_id, created_by_user_id, checkin_date date NOT NULL, notes text NOT NULL, action_items text, employee_comment text, next_checkin_date date
🆕 `company_objectives` — company_id, department_id, parent_objective_id (self-FK, kaskade), title varchar(200), description text, year smallint, quarter tinyint (null=tahunan), owner_employee_id, status varchar(20) default 'active', created_by_user_id
🆕 `feedback_360_cycles` — company_id, title varchar(150), period_start/end date, status varchar(20) default 'draft', created_by_user_id
🆕 `feedback_360_reviews` — cycle_id, subject_employee_id, rater_employee_id, relation_type varchar(20) (self/manager/peer/subordinate), status varchar(20) default 'pending', submitted_at (unique cycle+subject+rater)
🆕 `feedback_360_answers` — review_id, question_key varchar(60), rating tinyint(1-5), comment text (unique review+question_key)

`appraisal_objectives` (tabel lama): ➕ `company_objective_id` — tautan KPI individu ke OKR (kolom & relasi model siap, form Edit Appraisal belum ada dropdown-nya — OKR jalan sbg tracker berjenjang berdiri sendiri untuk saat ini).

Pertanyaan 360° (communication/teamwork/quality/reliability/leadership/problem_solving) tetap di kode (`Feedback360Review::$questions`), bukan tabel referensi.

---

## Compensation + Succession Planning + Survey Pulse/eNPS — `compensation-succession-survey-extras-manual.sql`

🆕 `salary_benchmarks` — level_id (FK `levels`, UNIQUE — 1 benchmark per Level, berlaku lintas 3 PT karena Level bukan entitas per-company), market_min/market_mid/market_max bigint unsigned, source varchar(150), notes text, updated_by_user_id
🆕 `talent_pool_members` — position_id (FK `positions`), employee_id (FK `employees`), readiness varchar(20) (ready_now/ready_1_2yr/ready_3_5yr/development), development_notes text, added_by_user_id (unique position_id+employee_id)

`positions` (tabel lama): ➕ `is_critical_position` tinyint(1) default 0, ➕ `succession_risk` varchar(10) nullable (low/medium/high), ➕ `succession_notes` text nullable
`surveys` (tabel lama): ➕ `type` varchar(20) default 'standard' (standard/pulse/enps) — skor eNPS (%Promoter 9-10 − %Detraktor 0-6) dihitung on-the-fly dari jawaban pertanyaan skala 0-10, bukan kolom tersimpan.

Total Rewards Statement (Compensation) & Report Builder/export Excel (Analytics) **tidak butuh tabel baru** — keduanya query read-only dari data payroll/THR/bonus/absensi/cuti/training yang sudah ada.

---

## Notification Center + Struktur Gaji Internal + Grid 9-Kotak + Kudos — `notification-talent-extras-manual.sql`

🆕 `notifications` — tabel STANDAR Laravel (`id` char(36) UUID, bukan bigint auto-increment), `type`, `notifiable_type`+`notifiable_id` (polymorphic ke `users`), `data` text (JSON: title/message/url/icon), `read_at`. `User` sudah pakai trait `Notifiable` sejak awal, tidak perlu perubahan model.
🆕 `salary_grades` — company_id (FK `companies`, NULLABLE = berlaku semua PT/fallback global), level_id (FK `levels`), grade_min/grade_mid/grade_max bigint unsigned, notes, updated_by_user_id. Beda dari `salary_benchmarks` (acuan pasar EKSTERNAL) — ini band gaji INTERNAL utk kontrol merit increase.
🆕 `kudos` — from_employee_id, to_employee_id (FK `employees`), company_id (FK `companies`, SET NULL), category varchar(30) (teamwork/innovation/leadership/customer_focus/integrity/excellence), message text.

`employees` (tabel lama): ➕ `potential_rating` varchar(10) nullable (low/medium/high), ➕ `potential_notes` text nullable, ➕ `potential_assessed_at` date nullable, ➕ `potential_assessed_by_user_id` (FK `users`, SET NULL) — sumbu Potensi Grid 9-Kotak; sumbu Performa dari `total_score` Appraisal terakhir berstatus approved, TIDAK butuh kolom baru.
