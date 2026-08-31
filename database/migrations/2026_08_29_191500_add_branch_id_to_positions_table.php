<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Posisi tanpa Departemen (mis. CEO/CFO, "Kepala Cabang X") perlu ditautkan
     * ke Cabang tertentu supaya perhitungan "jabatan kosong" per Cabang di
     * bagan organisasi tidak mencampur pool posisi lintas-cabang (mis. posisi
     * "Kepala Cabang Jakarta" muncul sbg kosong di cabang Banjarmasin).
     * Posisi yang sudah terikat Departemen tetap null (di-scope lewat
     * departemen seperti biasa).
     */
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('section_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};
