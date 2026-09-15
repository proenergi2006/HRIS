<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * org_change_logs.unit_type adalah ENUM ketat (division/department/section/
     * position) — kelewatan saat menambah BranchController::logOrgChange('branch', ...).
     * Di MariaDB prod (strict mode) INSERT nilai di luar enum jadi ERROR
     * "Data truncated for column 'unit_type'" (bukan cuma warning seperti di
     * MySQL non-strict lokal, makanya lolos waktu tes lokal).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE org_change_logs MODIFY COLUMN unit_type "
            . "ENUM('branch','division','department','section','position') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("DELETE FROM org_change_logs WHERE unit_type = 'branch'");
        DB::statement("ALTER TABLE org_change_logs MODIFY COLUMN unit_type "
            . "ENUM('division','department','section','position') NOT NULL");
    }
};
