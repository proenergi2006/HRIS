<?php

namespace Database\Seeders;

use App\Models\Approval\ApprovalWorkflow;
use App\Models\Company;
use Illuminate\Database\Seeder;

class ApprovalWorkflowSeeder extends Seeder
{
    /**
     * Workflow default per perusahaan. HR bisa mengubahnya lewat menu
     * "Pengaturan Approval" tanpa developer.
     */
    public function run(): void
    {
        $defaults = [
            'leave_request' => [
                ['approver_type' => 'direct_manager'],
                ['approver_type' => 'specific_role', 'approver_role' => 'hr_manager'],
            ],
            'overtime_request' => [
                ['approver_type' => 'direct_manager'],
            ],
            'perdin_request' => [
                ['approver_type' => 'direct_manager'],
                ['approver_type' => 'specific_role', 'approver_role' => 'hr_manager'],
                ['approver_type' => 'specific_role', 'approver_role' => 'ceo'],
            ],
            'reimbursement_request' => [
                ['approver_type' => 'direct_manager'],
                ['approver_type' => 'specific_role', 'approver_role' => 'admin'],
            ],
            'reward_request' => [
                ['approver_type' => 'direct_manager'],
                ['approver_type' => 'department_head'],
            ],
            'punishment_request' => [
                ['approver_type' => 'direct_manager'],
                ['approver_type' => 'specific_role', 'approver_role' => 'hr_manager'],
                ['approver_type' => 'specific_role', 'approver_role' => 'ceo',
                 'conditions' => [['field' => 'sanction_level', 'operator' => 'in', 'value' => 'sp3,phk,demosi']]],
            ],
            'promotion_rotation_request' => [
                ['approver_type' => 'direct_manager'],
                ['approver_type' => 'specific_role', 'approver_role' => 'hr_manager'],
                ['approver_type' => 'specific_role', 'approver_role' => 'ceo'],
            ],
            'termination_request' => [
                ['approver_type' => 'direct_manager'],
                ['approver_type' => 'specific_role', 'approver_role' => 'hr_manager'],
                ['approver_type' => 'specific_role', 'approver_role' => 'ceo'],
            ],
            'salary_increase_request' => [
                ['approver_type' => 'direct_manager'],
                ['approver_type' => 'specific_role', 'approver_role' => 'hr_manager'],
                ['approver_type' => 'specific_role', 'approver_role' => 'ceo'],
            ],
            'manpower_plan_request' => [
                ['approver_type' => 'specific_role', 'approver_role' => 'hr_manager'],
                ['approver_type' => 'specific_role', 'approver_role' => 'ceo'],
            ],
            'job_requisition' => [
                ['approver_type' => 'department_head'],
                ['approver_type' => 'specific_role', 'approver_role' => 'hr_manager'],
            ],
            'employee_data_change_request' => [
                ['approver_type' => 'specific_role', 'approver_role' => 'hr_manager'],
            ],
            'appraisal' => [
                ['approver_type' => 'specific_role', 'approver_role' => 'hr_manager'],
                ['approver_type' => 'specific_role', 'approver_role' => 'ceo'],
            ],
        ];

        foreach (Company::all() as $company) {
            foreach ($defaults as $type => $steps) {
                $workflow = ApprovalWorkflow::firstOrCreate(
                    ['company_id' => $company->id, 'transaction_type' => $type],
                    ['name' => ApprovalWorkflow::$transactionTypes[$type] ?? $type, 'is_active' => true],
                );

                if ($workflow->steps()->exists()) {
                    continue;
                }

                foreach ($steps as $i => $s) {
                    $workflow->steps()->create([
                        'step_order'          => $i + 1,
                        'approver_type'       => $s['approver_type'],
                        'approver_role'       => $s['approver_role'] ?? null,
                        'conditions'          => $s['conditions'] ?? null,
                        'is_active'           => true,
                    ]);
                }
            }
        }
    }
}
