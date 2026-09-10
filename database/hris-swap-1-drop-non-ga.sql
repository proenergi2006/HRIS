-- ==============================================================================
-- hris-swap-1-drop-non-ga.sql — HAPUS SEMUA TABEL NON-GA di prod hris
-- ==============================================================================
-- Menyisakan HANYA 11 tabel GA (vehicles/vehicle_usages/meeting_rooms/
-- room_cleaning_*/vault_*/vaults) beserta datanya, UTUH di tempatnya.
-- Daftar dari snapshot prod 2026-09-10 (database/hris.sql).
--
-- WAJIB: mysqldump backup PENUH + backup GA terpisah DULU (lihat REPLACE-HR-PRESERVE-GA.md).
-- Setelah ini: import DB HR baru, lalu (kalau perlu) ga-preserve.sql.
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `activity_log`;
DROP TABLE IF EXISTS `appraisals`;
DROP TABLE IF EXISTS `appraisal_approvals`;
DROP TABLE IF EXISTS `appraisal_aspects`;
DROP TABLE IF EXISTS `appraisal_aspect_weights`;
DROP TABLE IF EXISTS `appraisal_flow_configs`;
DROP TABLE IF EXISTS `appraisal_grade_bands`;
DROP TABLE IF EXISTS `appraisal_items`;
DROP TABLE IF EXISTS `appraisal_periods`;
DROP TABLE IF EXISTS `appraisal_templates`;
DROP TABLE IF EXISTS `approval_delegations`;
DROP TABLE IF EXISTS `approval_requests`;
DROP TABLE IF EXISTS `approval_request_steps`;
DROP TABLE IF EXISTS `approval_workflows`;
DROP TABLE IF EXISTS `approval_workflow_steps`;
DROP TABLE IF EXISTS `attendance_records`;
DROP TABLE IF EXISTS `banks`;
DROP TABLE IF EXISTS `blood_types`;
DROP TABLE IF EXISTS `branches`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `cities`;
DROP TABLE IF EXISTS `companies`;
DROP TABLE IF EXISTS `company_banks`;
DROP TABLE IF EXISTS `departments`;
DROP TABLE IF EXISTS `divisions`;
DROP TABLE IF EXISTS `education_levels`;
DROP TABLE IF EXISTS `education_majors`;
DROP TABLE IF EXISTS `employees`;
DROP TABLE IF EXISTS `employee_bank_accounts`;
DROP TABLE IF EXISTS `employee_contracts`;
DROP TABLE IF EXISTS `employee_documents`;
DROP TABLE IF EXISTS `employee_educations`;
DROP TABLE IF EXISTS `employee_facilities`;
DROP TABLE IF EXISTS `employee_family_members`;
DROP TABLE IF EXISTS `employee_nssf`;
DROP TABLE IF EXISTS `employee_org_experiences`;
DROP TABLE IF EXISTS `employee_salary_components`;
DROP TABLE IF EXISTS `employee_skills`;
DROP TABLE IF EXISTS `employee_types`;
DROP TABLE IF EXISTS `employee_work_experiences`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `leave_balances`;
DROP TABLE IF EXISTS `leave_requests`;
DROP TABLE IF EXISTS `leave_types`;
DROP TABLE IF EXISTS `levels`;
DROP TABLE IF EXISTS `marital_statuses`;
DROP TABLE IF EXISTS `migrations`;
DROP TABLE IF EXISTS `model_has_permissions`;
DROP TABLE IF EXISTS `model_has_roles`;
DROP TABLE IF EXISTS `org_change_logs`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `payroll_periods`;
DROP TABLE IF EXISTS `payroll_slips`;
DROP TABLE IF EXISTS `payroll_slip_details`;
DROP TABLE IF EXISTS `perdin_approvals`;
DROP TABLE IF EXISTS `perdin_budget_items`;
DROP TABLE IF EXISTS `perdin_itinerary`;
DROP TABLE IF EXISTS `perdin_requests`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `positions`;
DROP TABLE IF EXISTS `promotion_rotation_requests`;
DROP TABLE IF EXISTS `provinces`;
DROP TABLE IF EXISTS `punishment_requests`;
DROP TABLE IF EXISTS `reimbursement_attachments`;
DROP TABLE IF EXISTS `reimbursement_balances`;
DROP TABLE IF EXISTS `reimbursement_items`;
DROP TABLE IF EXISTS `reimbursement_requests`;
DROP TABLE IF EXISTS `religions`;
DROP TABLE IF EXISTS `reward_requests`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `role_company_assignments`;
DROP TABLE IF EXISTS `role_has_permissions`;
DROP TABLE IF EXISTS `salary_components`;
DROP TABLE IF EXISTS `sections`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `termination_requests`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `whistleblower_reports`;

SET FOREIGN_KEY_CHECKS = 1;
