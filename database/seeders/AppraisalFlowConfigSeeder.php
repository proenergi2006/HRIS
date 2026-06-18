<?php

namespace Database\Seeders;

use App\Models\Appraisal\AppraisalFlowConfig;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AppraisalFlowConfigSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure ceo role exists
        Role::firstOrCreate(['name' => 'ceo', 'guard_name' => 'web']);

        $configs = [
            // Default flow (applies to any department without a specific config)
            ['department' => null, 'step' => 1, 'role' => 'user_ii', 'label' => 'User II / Atasan Langsung'],
            ['department' => null, 'step' => 2, 'role' => 'cfo',     'label' => 'CFO'],

            // IT department: final approver is CEO
            ['department' => 'IT', 'step' => 1, 'role' => 'user_ii', 'label' => 'User II / Atasan Langsung'],
            ['department' => 'IT', 'step' => 2, 'role' => 'ceo',     'label' => 'CEO'],
        ];

        foreach ($configs as $config) {
            AppraisalFlowConfig::updateOrCreate(
                ['department' => $config['department'], 'step' => $config['step']],
                ['role' => $config['role'], 'label' => $config['label']]
            );
        }
    }
}
