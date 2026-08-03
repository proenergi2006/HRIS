<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->bigInteger('tunjangan_jabatan')->nullable()->after('name');
            $table->bigInteger('tunjangan_harian')->nullable()->after('tunjangan_jabatan');
        });
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn(['tunjangan_jabatan', 'tunjangan_harian']);
        });
    }
};
