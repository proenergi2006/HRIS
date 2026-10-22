<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Profil karyawan tahap 1: HP kantor (phone = HP pribadi), No. SIM,
     * 2 jaminan (jenis + file), kontak darurat ke-2.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('office_phone', 30)->nullable()->after('phone');
            $table->string('sim_number', 30)->nullable()->after('ktp_number');

            $table->string('guarantee1_type', 100)->nullable();
            $table->string('guarantee1_file')->nullable();
            $table->string('guarantee2_type', 100)->nullable();
            $table->string('guarantee2_file')->nullable();

            $table->string('emergency_contact2_name', 150)->nullable();
            $table->string('emergency_contact2_relation', 100)->nullable();
            $table->string('emergency_contact2_phone', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'office_phone', 'sim_number',
                'guarantee1_type', 'guarantee1_file', 'guarantee2_type', 'guarantee2_file',
                'emergency_contact2_name', 'emergency_contact2_relation', 'emergency_contact2_phone',
            ]);
        });
    }
};
