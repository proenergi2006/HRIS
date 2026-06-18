<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['name' => 'SPV',     'description' => 'Supervisor'],
            ['name' => 'Manager', 'description' => 'Manager'],
            ['name' => 'Admin',   'description' => 'Administrator / Staff'],
            ['name' => 'Direksi', 'description' => 'Direktur / BOD'],
        ];

        foreach ($levels as $level) {
            Level::firstOrCreate(['name' => $level['name']], $level);
        }
    }
}
