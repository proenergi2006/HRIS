-- CEK-TABEL.sql — jalankan di DB prod (mysql -u USER -p hris < CEK-TABEL.sql)
-- Menampilkan tabel HRIS v1.2 yang BELUM ada + di TAHAP mana file SQL-nya.
SELECT x.t AS tabel_hilang, x.tahap AS jalankan_tahap
FROM (
  SELECT 'religions' t,1 tahap UNION ALL
  SELECT 'education_levels' t,1 tahap UNION ALL
  SELECT 'education_majors' t,1 tahap UNION ALL
  SELECT 'marital_statuses' t,1 tahap UNION ALL
  SELECT 'blood_types' t,1 tahap UNION ALL
  SELECT 'employee_types' t,1 tahap UNION ALL
  SELECT 'banks' t,1 tahap UNION ALL
  SELECT 'company_banks' t,1 tahap UNION ALL
  SELECT 'provinces' t,1 tahap UNION ALL
  SELECT 'cities' t,1 tahap UNION ALL
  SELECT 'divisions' t,2 tahap UNION ALL
  SELECT 'sections' t,2 tahap UNION ALL
  SELECT 'org_change_logs' t,2 tahap UNION ALL
  SELECT 'branches' t,3 tahap UNION ALL
  SELECT 'employee_nssf' t,4 tahap UNION ALL
  SELECT 'employee_educations' t,4 tahap UNION ALL
  SELECT 'employee_work_experiences' t,4 tahap UNION ALL
  SELECT 'employee_skills' t,4 tahap UNION ALL
  SELECT 'employee_org_experiences' t,4 tahap UNION ALL
  SELECT 'employee_facilities' t,4 tahap UNION ALL
  SELECT 'employee_bank_accounts' t,4 tahap UNION ALL
  SELECT 'employee_contracts' t,4 tahap UNION ALL
  SELECT 'approval_workflows' t,5 tahap UNION ALL
  SELECT 'approval_workflow_steps' t,5 tahap UNION ALL
  SELECT 'approval_requests' t,5 tahap UNION ALL
  SELECT 'approval_request_steps' t,5 tahap UNION ALL
  SELECT 'approval_delegations' t,5 tahap UNION ALL
  SELECT 'reward_requests' t,5 tahap UNION ALL
  SELECT 'punishment_requests' t,5 tahap UNION ALL
  SELECT 'promotion_rotation_requests' t,5 tahap UNION ALL
  SELECT 'termination_requests' t,5 tahap UNION ALL
  SELECT 'role_company_assignments' t,6 tahap UNION ALL
  SELECT 'ter_categories' t,7 tahap UNION ALL
  SELECT 'ter_brackets' t,7 tahap UNION ALL
  SELECT 'employee_loans' t,8 tahap UNION ALL
  SELECT 'loan_installments' t,8 tahap UNION ALL
  SELECT 'bonus_periods' t,8 tahap UNION ALL
  SELECT 'bonus_payments' t,8 tahap UNION ALL
  SELECT 'thr_periods' t,9 tahap UNION ALL
  SELECT 'thr_payments' t,9 tahap UNION ALL
  SELECT 'overtime_requests' t,10 tahap UNION ALL
  SELECT 'manpower_plans' t,11 tahap UNION ALL
  SELECT 'job_requisitions' t,12 tahap UNION ALL
  SELECT 'candidates' t,12 tahap UNION ALL
  SELECT 'candidate_interviews' t,12 tahap UNION ALL
  SELECT 'candidate_offers' t,12 tahap UNION ALL
  SELECT 'candidate_documents' t,12 tahap UNION ALL
  SELECT 'onboarding_checklist_items' t,12 tahap UNION ALL
  SELECT 'employee_onboarding_tasks' t,12 tahap UNION ALL
  SELECT 'training_programs' t,13 tahap UNION ALL
  SELECT 'training_participants' t,13 tahap UNION ALL
  SELECT 'career_paths' t,13 tahap UNION ALL
  SELECT 'career_path_steps' t,13 tahap UNION ALL
  SELECT 'competencies' t,14 tahap UNION ALL
  SELECT 'position_competencies' t,14 tahap UNION ALL
  SELECT 'employee_competencies' t,14 tahap UNION ALL
  SELECT 'employee_data_change_requests' t,15 tahap UNION ALL
  SELECT 'letter_templates' t,15 tahap UNION ALL
  SELECT 'employee_letters' t,15 tahap UNION ALL
  SELECT 'appraisal_template_objectives' t,16 tahap UNION ALL
  SELECT 'appraisal_objectives' t,16 tahap UNION ALL
  SELECT 'approval_workflow_change_logs' t,17 tahap UNION ALL
  SELECT 'districts' t,17 tahap UNION ALL
  SELECT 'villages' t,17 tahap UNION ALL
  SELECT 'shifts' t,20 tahap UNION ALL
  SELECT 'roster_entries' t,20 tahap UNION ALL
  SELECT 'leave_policies' t,20 tahap UNION ALL
  SELECT 'offboarding_checklist_items' t,21 tahap UNION ALL
  SELECT 'employee_offboarding_tasks' t,21 tahap UNION ALL
  SELECT 'letter_requests' t,21 tahap UNION ALL
  SELECT 'announcements' t,22 tahap UNION ALL
  SELECT 'announcement_reads' t,22 tahap UNION ALL
  SELECT 'surveys' t,22 tahap UNION ALL
  SELECT 'survey_questions' t,22 tahap UNION ALL
  SELECT 'survey_responses' t,22 tahap UNION ALL
  SELECT 'survey_answers' t,22 tahap UNION ALL
  SELECT 'recruitment_costs' t,22 tahap UNION ALL
  SELECT 'candidate_educations' t,24 tahap UNION ALL
  SELECT 'candidate_experiences' t,24 tahap UNION ALL
  SELECT 'candidate_skills' t,24 tahap UNION ALL
  SELECT 'candidate_certifications' t,24 tahap UNION ALL
  SELECT 'candidate_preemployment' t,25 tahap UNION ALL
  SELECT 'preemployment_checklist_items' t,25 tahap UNION ALL
  SELECT 'candidate_preemployment_tasks' t,25 tahap UNION ALL
  SELECT 'performance_checkins' t,27 tahap UNION ALL
  SELECT 'company_objectives' t,27 tahap UNION ALL
  SELECT 'feedback_360_cycles' t,27 tahap UNION ALL
  SELECT 'feedback_360_reviews' t,27 tahap UNION ALL
  SELECT 'feedback_360_answers' t,27 tahap UNION ALL
  SELECT 'salary_benchmarks' t,29 tahap UNION ALL
  SELECT 'talent_pool_members' t,29 tahap UNION ALL
  SELECT 'notifications' t,30 tahap UNION ALL
  SELECT 'salary_grades' t,30 tahap UNION ALL
  SELECT 'kudos' t,30 tahap UNION ALL
  SELECT 'salary_increase_requests' t,31 tahap UNION ALL
  SELECT 'probation_reviews' t,31 tahap UNION ALL
  SELECT 'employee_potential_history' t,31 tahap UNION ALL
  SELECT 'salary_grade_history' t,31 tahap
) x
LEFT JOIN information_schema.tables i
  ON i.table_schema = DATABASE() AND i.table_name = x.t
WHERE i.table_name IS NULL
ORDER BY x.tahap, x.t;
