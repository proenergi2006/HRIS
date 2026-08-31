<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            LevelSeeder::class,
            RegionSeeder::class,
            RegionDistrictSeeder::class,
            ApprovalWorkflowSeeder::class,
            UserSeeder::class,
            AppraisalTemplateSeeder::class,
            CompetencySeeder::class,
            LeavePolicySeeder::class,
            OffboardingChecklistItemSeeder::class,
            ITDemoSeeder::class,
            // Terakhir — perlu semua role sudah dibuat & di-assign ke user dulu
            // (backfill role_company_assignments baca model_has_roles saat ini).
            PermissionCatalogSeeder::class,
        ]);
    }
}
