<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Onboarding (PRD Bab 3 modul #5) — materi induction + konfirmasi karyawan.
     * Item bisa dilampiri deskripsi/materi (file atau link). Item yang
     * `requires_acknowledgement` diselesaikan oleh KARYAWAN (baca + setuju),
     * bukan HR — dipakai untuk kurikulum induction (Welcome, HR Procedure,
     * Fakta Integritas, Vopak Procedure, dll).
     */
    public function up(): void
    {
        Schema::table('onboarding_checklist_items', function (Blueprint $table) {
            $table->text('description')->nullable()->after('label');
            $table->string('material_path', 500)->nullable()->after('description');
            $table->string('material_original_name', 255)->nullable()->after('material_path');
            $table->string('material_url', 500)->nullable()->after('material_original_name');
            $table->boolean('requires_acknowledgement')->default(false)->after('is_required');
        });

        Schema::table('employee_onboarding_tasks', function (Blueprint $table) {
            $table->dateTime('acknowledged_at')->nullable()->after('done_at');
            $table->string('acknowledgement_note', 500)->nullable()->after('acknowledged_at');
        });
    }

    public function down(): void
    {
        Schema::table('employee_onboarding_tasks', function (Blueprint $table) {
            $table->dropColumn(['acknowledged_at', 'acknowledgement_note']);
        });

        Schema::table('onboarding_checklist_items', function (Blueprint $table) {
            $table->dropColumn([
                'description', 'material_path', 'material_original_name', 'material_url', 'requires_acknowledgement',
            ]);
        });
    }
};
