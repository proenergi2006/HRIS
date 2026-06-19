<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        Role::firstOrCreate(['name' => 'admin_ga', 'guard_name' => 'web']);

        User::updateOrCreate(
            ['email' => 'ga@proenergi.co.id'],
            ['name' => 'Admin GA', 'password' => bcrypt('password')]
        )->syncRoles(['admin_ga']);
    }

    public function down(): void
    {
        User::where('email', 'ga@proenergi.co.id')->delete();
        Role::where('name', 'admin_ga')->delete();
    }
};
