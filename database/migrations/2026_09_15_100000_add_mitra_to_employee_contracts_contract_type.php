<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambah 'mitra' ke enum employee_contracts.contract_type — Mitra sudah
     * ada sebagai Tipe Karyawan (employee_types) tapi belum bisa dipilih di
     * tab Kontrak Kerja karyawan (App\Models\EmployeeContract::$typeLabels).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE employee_contracts MODIFY COLUMN contract_type "
            . "ENUM('pkwtt','pkwt','probation','magang','harian','mitra','other') NOT NULL DEFAULT 'pkwt'");
    }

    public function down(): void
    {
        DB::statement("UPDATE employee_contracts SET contract_type = 'other' WHERE contract_type = 'mitra'");
        DB::statement("ALTER TABLE employee_contracts MODIFY COLUMN contract_type "
            . "ENUM('pkwtt','pkwt','probation','magang','harian','other') NOT NULL DEFAULT 'pkwt'");
    }
};
