<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambah calculation_type 'pph21_ter' (kalkulasi PPh21 otomatis metode TER), lalu alihkan
     * komponen "Potongan PPh 21" yang sudah ada (dulu calc=manual, admin ketik sendiri) supaya
     * otomatis. Nama komponen SENGAJA tidak diganti — komponen "Tunjangan PPh 21" (calc=
     * mirror_pph21) mencari nominal gross-up lewat nama "Potongan PPh 21" (lihat
     * PayrollController::generate()), jadi harus tetap match.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE salary_components MODIFY COLUMN calculation_type ENUM(
            'manual','percent_of_base','late_deduction','medical_claim','mirror_pph21',
            'position_fixed','position_daily','overtime','pph21_ter'
        ) NOT NULL DEFAULT 'manual'");

        DB::table('salary_components')
            ->where('name', 'Potongan PPh 21')
            ->update(['calculation_type' => 'pph21_ter', 'is_taxable' => false]);
    }

    public function down(): void
    {
        DB::table('salary_components')
            ->where('name', 'Potongan PPh 21')
            ->update(['calculation_type' => 'manual']);

        DB::statement("ALTER TABLE salary_components MODIFY COLUMN calculation_type ENUM(
            'manual','percent_of_base','late_deduction','medical_claim','mirror_pph21',
            'position_fixed','position_daily','overtime'
        ) NOT NULL DEFAULT 'manual'");
    }
};
