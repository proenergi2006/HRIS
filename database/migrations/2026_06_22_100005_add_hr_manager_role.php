<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        Role::firstOrCreate(['name' => 'hr_manager', 'guard_name' => 'web']);

        User::updateOrCreate(
            ['email' => 'hrmanager@proenergi.co.id'],
            ['name' => 'HR & GA Manager', 'password' => bcrypt('password')]
        )->syncRoles(['hr_manager']);
    }

    public function down(): void
    {
        User::where('email', 'hrmanager@proenergi.co.id')->delete();
        Role::where('name', 'hr_manager')->delete();
    }
};
