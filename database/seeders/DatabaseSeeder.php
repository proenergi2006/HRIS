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
            UserSeeder::class,
            AppraisalTemplateSeeder::class,
            AppraisalFlowConfigSeeder::class,
            ITDemoSeeder::class,
        ]);
    }
}
