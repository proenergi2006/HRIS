<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Beri Donna role evaluator (agar bisa buat penilaian untuk Radieta)
        $donna = DB::table('users')->where('email', 'donna@proenergi.co.id')->first();
        if ($donna) {
            $roleEvaluator = DB::table('roles')
                ->where('name', 'evaluator')
                ->where('guard_name', 'web')
                ->first();

            if ($roleEvaluator) {
                DB::table('model_has_roles')->insertOrIgnore([
                    'role_id'    => $roleEvaluator->id,
                    'model_type' => 'App\\Models\\User',
                    'model_id'   => $donna->id,
                ]);
            }
        }

        // 2. Hapus step 1 HR & GA agar alur jadi single-step:
        //    Donna evaluasi → submit → langsung ke CEO (approved_user2 → ceo approve)
        DB::table('appraisal_flow_configs')
            ->where('department', 'HR & GA')
            ->where('step', 1)
            ->delete();
    }

    public function down(): void
    {
        // Kembalikan step 1 HR & GA
        DB::table('appraisal_flow_configs')->insertOrIgnore([
            'department'  => 'HR & GA',
            'step'        => 1,
            'role'        => 'user_ii',
            'label'       => 'Manager HR & GA',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }
};
