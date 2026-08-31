<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Identitas penandatangan bukti potong PPh21 tahunan (Fase 2 HRD).
     * companies.npwp sudah ada sejak awal — hanya tambah nama & NPWP penandatangan.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'tax_signer_name')) {
                $table->string('tax_signer_name', 150)->nullable()->after('npwp');
            }
            if (! Schema::hasColumn('companies', 'tax_signer_npwp')) {
                $table->string('tax_signer_npwp', 30)->nullable()->after('tax_signer_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['tax_signer_name', 'tax_signer_npwp']);
        });
    }
};
