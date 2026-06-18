<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('department')->nullable()->after('email');
        });

        // ── Fix data Donna ──────────────────────────────────────────────────
        $donna = DB::table('users')->where('email', 'donna@proenergi.co.id')->first();
        if ($donna) {
            DB::table('users')->where('id', $donna->id)->update(['department' => 'HR & GA']);

            $role = DB::table('roles')->where('name', 'user_ii')->where('guard_name', 'web')->first();
            if ($role) {
                DB::table('model_has_roles')->insertOrIgnore([
                    'role_id'    => $role->id,
                    'model_type' => 'App\\Models\\User',
                    'model_id'   => $donna->id,
                ]);
            }
        }

        // ── Set department untuk approver IT ────────────────────────────────
        DB::table('users')->where('email', 'direktur.it@proenergi.co.id')
            ->update(['department' => 'IT']);

        DB::table('users')->where('email', 'it.manager@proenergi.co.id')
            ->update(['department' => 'IT']);

        // ── Fix flow config HR & GA ─────────────────────────────────────────
        DB::table('appraisal_flow_configs')
            ->where('department', 'HR & GA')
            ->where('step', 1)
            ->update(['role' => 'user_ii', 'label' => 'Manager HR & GA']);

        DB::table('appraisal_flow_configs')
            ->where('department', 'HR & GA')
            ->where('step', 2)
            ->update(['role' => 'ceo', 'label' => 'CEO']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('department');
        });
    }
};
