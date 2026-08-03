<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE salary_components MODIFY COLUMN calculation_type
            ENUM('manual','percent_of_base','late_deduction','medical_claim','mirror_pph21','position_fixed','position_daily')
            NOT NULL DEFAULT 'manual'");

        // Tunjangan Jabatan -> otomatis dari nominal tetap per Jabatan
        DB::table('salary_components')
            ->whereNull('company_id')->where('name', 'Tunjangan Jabatan')
            ->update(['calculation_type' => 'position_fixed', 'updated_at' => now()]);

        // Tunjangan Makan & Transport -> otomatis dari tarif harian per Jabatan x hari hadir
        DB::table('salary_components')
            ->whereNull('company_id')->where('name', 'Tunjangan Makan & Transport')
            ->update(['calculation_type' => 'position_daily', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('salary_components')
            ->whereNull('company_id')->whereIn('name', ['Tunjangan Jabatan', 'Tunjangan Makan & Transport'])
            ->update(['calculation_type' => 'manual', 'updated_at' => now()]);

        DB::statement("ALTER TABLE salary_components MODIFY COLUMN calculation_type
            ENUM('manual','percent_of_base','late_deduction','medical_claim','mirror_pph21')
            NOT NULL DEFAULT 'manual'");
    }
};
