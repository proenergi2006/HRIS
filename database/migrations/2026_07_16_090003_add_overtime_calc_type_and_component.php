<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE salary_components MODIFY COLUMN calculation_type
            ENUM('manual','percent_of_base','late_deduction','medical_claim','mirror_pph21','position_fixed','position_daily','overtime')
            NOT NULL DEFAULT 'manual'");

        DB::table('salary_components')->insert([
            'company_id' => null, 'name' => 'Tunjangan Lembur', 'type' => 'allowance',
            'calculation_type' => 'overtime', 'rate_percent' => null, 'salary_cap' => null,
            'is_taxable' => false, 'is_active' => true, 'sort_order' => 9,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('salary_components')->whereNull('company_id')->where('name', 'Tunjangan Lembur')->delete();

        DB::statement("ALTER TABLE salary_components MODIFY COLUMN calculation_type
            ENUM('manual','percent_of_base','late_deduction','medical_claim','mirror_pph21','position_fixed','position_daily')
            NOT NULL DEFAULT 'manual'");
    }
};
