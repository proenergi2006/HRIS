<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** PrivyID karyawan (identitas tanda tangan digital). */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('privy_id', 50)->nullable()->after('sim_number');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('privy_id');
        });
    }
};
