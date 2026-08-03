<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_components', function (Blueprint $table) {
            // manual        = admin isi nominal per karyawan (spt sekarang)
            // percent_of_base = otomatis: persen x (Gaji Pokok + Tunjangan Jabatan), dgn batas atas opsional
            // late_deduction  = otomatis: dari total menit terlambat bulan berjalan
            // medical_claim   = otomatis: total reimbursement medical approved bulan berjalan
            // mirror_pph21    = otomatis: mengikuti nominal komponen "Potongan PPh 21" (gross-up)
            $table->enum('calculation_type', ['manual', 'percent_of_base', 'late_deduction', 'medical_claim', 'mirror_pph21'])
                ->default('manual')->after('type');
            $table->decimal('rate_percent', 5, 2)->nullable()->after('calculation_type');
            $table->bigInteger('salary_cap')->nullable()->after('rate_percent');
        });
    }

    public function down(): void
    {
        Schema::table('salary_components', function (Blueprint $table) {
            $table->dropColumn(['calculation_type', 'rate_percent', 'salary_cap']);
        });
    }
};
