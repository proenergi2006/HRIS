-- ============================================================================
-- Modul: Approval Engine generik (PRD HRIS v1.2 Bab 7)
--
-- Setara dengan migration 2026_08_28_1003xx:
--   approval_workflows, approval_workflow_steps, approval_requests,
--   approval_request_steps, approval_delegations,
--   reward_requests, punishment_requests,
--   promotion_rotation_requests, termination_requests
--
-- WAJIB backup dulu. Jalankan berurutan. Setelah selesai, jalankan seeder
-- workflow default:  php artisan db:seed --class=ApprovalWorkflowSeeder
-- (butuh role 'hr_manager', 'ceo', 'admin' sudah ada).
-- ============================================================================

CREATE TABLE `approval_workflows` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `approval_workflows_company_id_transaction_type_unique` (`company_id`,`transaction_type`),
  CONSTRAINT `approval_workflows_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `approval_workflow_steps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `approval_workflow_id` bigint(20) unsigned NOT NULL,
  `step_order` tinyint(3) unsigned NOT NULL,
  `approver_type` enum('direct_manager','section_head','department_head','division_head','specific_position','specific_role') NOT NULL,
  `approver_position_id` bigint(20) unsigned DEFAULT NULL,
  `approver_role` varchar(50) DEFAULT NULL,
  `conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`conditions`)),
  `escalate_after_days` smallint(5) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `approval_workflow_steps_approval_workflow_id_step_order_unique` (`approval_workflow_id`,`step_order`),
  KEY `approval_workflow_steps_approver_position_id_foreign` (`approver_position_id`),
  CONSTRAINT `approval_workflow_steps_approval_workflow_id_foreign` FOREIGN KEY (`approval_workflow_id`) REFERENCES `approval_workflows` (`id`) ON DELETE CASCADE,
  CONSTRAINT `approval_workflow_steps_approver_position_id_foreign` FOREIGN KEY (`approver_position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `approval_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `approvable_type` varchar(255) NOT NULL,
  `approvable_id` bigint(20) unsigned NOT NULL,
  `approval_workflow_id` bigint(20) unsigned DEFAULT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `summary` varchar(255) DEFAULT NULL,
  `requester_user_id` bigint(20) unsigned DEFAULT NULL,
  `subject_employee_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `current_step_order` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approval_requests_approvable_type_approvable_id_index` (`approvable_type`,`approvable_id`),
  KEY `approval_requests_status_transaction_type_index` (`status`,`transaction_type`),
  KEY `approval_requests_approval_workflow_id_foreign` (`approval_workflow_id`),
  KEY `approval_requests_company_id_foreign` (`company_id`),
  KEY `approval_requests_requester_user_id_foreign` (`requester_user_id`),
  KEY `approval_requests_subject_employee_id_foreign` (`subject_employee_id`),
  CONSTRAINT `approval_requests_approval_workflow_id_foreign` FOREIGN KEY (`approval_workflow_id`) REFERENCES `approval_workflows` (`id`) ON DELETE SET NULL,
  CONSTRAINT `approval_requests_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `approval_requests_requester_user_id_foreign` FOREIGN KEY (`requester_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `approval_requests_subject_employee_id_foreign` FOREIGN KEY (`subject_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `approval_request_steps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `approval_request_id` bigint(20) unsigned NOT NULL,
  `step_order` tinyint(3) unsigned NOT NULL,
  `approver_type` enum('direct_manager','section_head','department_head','division_head','specific_position','specific_role') NOT NULL,
  `approver_label` varchar(150) DEFAULT NULL,
  `approver_user_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('pending','approved','rejected','skipped') NOT NULL DEFAULT 'pending',
  `acted_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `acted_at` timestamp NULL DEFAULT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `due_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approval_request_steps_approver_user_id_status_index` (`approver_user_id`,`status`),
  KEY `approval_request_steps_approval_request_id_foreign` (`approval_request_id`),
  KEY `approval_request_steps_acted_by_user_id_foreign` (`acted_by_user_id`),
  CONSTRAINT `approval_request_steps_approval_request_id_foreign` FOREIGN KEY (`approval_request_id`) REFERENCES `approval_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `approval_request_steps_approver_user_id_foreign` FOREIGN KEY (`approver_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `approval_request_steps_acted_by_user_id_foreign` FOREIGN KEY (`acted_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `approval_delegations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `delegator_user_id` bigint(20) unsigned NOT NULL,
  `delegate_user_id` bigint(20) unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approval_delegations_delegator_user_id_foreign` (`delegator_user_id`),
  KEY `approval_delegations_delegate_user_id_foreign` (`delegate_user_id`),
  CONSTRAINT `approval_delegations_delegator_user_id_foreign` FOREIGN KEY (`delegator_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `approval_delegations_delegate_user_id_foreign` FOREIGN KEY (`delegate_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Transaksi HR baru (dijalankan lewat engine) ──────────────────────────

CREATE TABLE `reward_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `requested_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `reward_type` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `amount` bigint(20) DEFAULT NULL,
  `status` enum('draft','pending','approved','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reward_requests_employee_id_foreign` (`employee_id`),
  CONSTRAINT `reward_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reward_requests_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reward_requests_requested_by_user_id_foreign` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `punishment_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `requested_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `violation_type` varchar(150) NOT NULL,
  `sanction_level` enum('teguran_lisan','sp1','sp2','sp3','demosi','phk','other') NOT NULL DEFAULT 'sp1',
  `description` text DEFAULT NULL,
  `incident_date` date DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `status` enum('draft','pending','approved','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `punishment_requests_employee_id_foreign` (`employee_id`),
  CONSTRAINT `punishment_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `punishment_requests_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `punishment_requests_requested_by_user_id_foreign` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `promotion_rotation_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `requested_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `request_type` enum('promotion','rotation','mutation','demotion') NOT NULL DEFAULT 'promotion',
  `from_position_id` bigint(20) unsigned DEFAULT NULL,
  `to_position_id` bigint(20) unsigned DEFAULT NULL,
  `from_company_id` bigint(20) unsigned DEFAULT NULL,
  `to_company_id` bigint(20) unsigned DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('draft','pending','approved','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `promotion_rotation_requests_employee_id_foreign` (`employee_id`),
  CONSTRAINT `promotion_rotation_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `promotion_rotation_requests_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `promotion_rotation_requests_requested_by_user_id_foreign` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `promotion_rotation_requests_from_position_id_foreign` FOREIGN KEY (`from_position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `promotion_rotation_requests_to_position_id_foreign` FOREIGN KEY (`to_position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `promotion_rotation_requests_from_company_id_foreign` FOREIGN KEY (`from_company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `promotion_rotation_requests_to_company_id_foreign` FOREIGN KEY (`to_company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `termination_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `requested_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `termination_type` enum('resign','pkwt_end','dismissal','retirement','deceased','other') NOT NULL DEFAULT 'resign',
  `reason` text DEFAULT NULL,
  `last_working_date` date DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `status` enum('draft','pending','approved','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `termination_requests_employee_id_foreign` (`employee_id`),
  CONSTRAINT `termination_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `termination_requests_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `termination_requests_requested_by_user_id_foreign` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Setelah semua tabel dibuat:
--   php artisan db:seed --class=ApprovalWorkflowSeeder   (workflow default per PT)
-- lalu HR merapikan alur lewat menu "Persetujuan > Pengaturan Alur".
--
-- Opsional daftarkan ke migrations (cek: SELECT MAX(batch) FROM migrations;):
-- INSERT INTO `migrations` (`migration`,`batch`) VALUES
-- ('2026_08_28_100301_create_approval_workflows_table',<BATCH>),
-- ('2026_08_28_100302_create_approval_workflow_steps_table',<BATCH>),
-- ('2026_08_28_100303_create_approval_requests_table',<BATCH>),
-- ('2026_08_28_100304_create_approval_request_steps_table',<BATCH>),
-- ('2026_08_28_100305_create_approval_delegations_table',<BATCH>),
-- ('2026_08_28_100311_create_reward_requests_table',<BATCH>),
-- ('2026_08_28_100312_create_punishment_requests_table',<BATCH>),
-- ('2026_08_28_100313_create_promotion_rotation_requests_table',<BATCH>),
-- ('2026_08_28_100314_create_termination_requests_table',<BATCH>);
-- ============================================================================
