<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambah 'mitra' ke enum employees.employment_status — field "Status
     * Kontrak" di tab Employee Information (beda dari employee_contracts.
     * contract_type di tab Kontrak Kerja, yang sudah dapat 'mitra' duluan
     * lewat migration 2026_09_15_100000).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE employees MODIFY COLUMN employment_status "
            . "ENUM('permanent','contract','probation','mitra') NOT NULL DEFAULT 'permanent'");
    }

    public function down(): void
    {
        DB::statement("UPDATE employees SET employment_status = 'permanent' WHERE employment_status = 'mitra'");
        DB::statement("ALTER TABLE employees MODIFY COLUMN employment_status "
            . "ENUM('permanent','contract','probation') NOT NULL DEFAULT 'permanent'");
    }
};
