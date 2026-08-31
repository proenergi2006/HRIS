<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah FK kecamatan/kelurahan ke alamat karyawan (lanjutan konversi wilayah
 * ke master). Kolom teks lama (`domicile_district`, `domicile_subdistrict`,
 * `ktp_district`, `ktp_subdistrict`) SENGAJA dipertahankan — sumber backfill +
 * fallback saat master belum lengkap (konsisten cara konversi city).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('domicile_district_id')->nullable()->after('domicile_district')->constrained('districts')->nullOnDelete();
            $table->foreignId('domicile_village_id')->nullable()->after('domicile_subdistrict')->constrained('villages')->nullOnDelete();
            $table->foreignId('ktp_district_id')->nullable()->after('ktp_district')->constrained('districts')->nullOnDelete();
            $table->foreignId('ktp_village_id')->nullable()->after('ktp_subdistrict')->constrained('villages')->nullOnDelete();
        });

        // Backfill dari nilai teks lama (match nama, case-insensitive).
        foreach (['domicile', 'ktp'] as $p) {
            DB::statement("
                UPDATE employees e
                JOIN districts d ON LOWER(TRIM(e.{$p}_district)) = LOWER(d.name)
                SET e.{$p}_district_id = d.id
                WHERE e.{$p}_district_id IS NULL AND e.{$p}_district IS NOT NULL AND e.{$p}_district <> ''
            ");
            DB::statement("
                UPDATE employees e
                JOIN villages v ON LOWER(TRIM(e.{$p}_subdistrict)) = LOWER(v.name)
                SET e.{$p}_village_id = v.id
                WHERE e.{$p}_village_id IS NULL AND e.{$p}_subdistrict IS NOT NULL AND e.{$p}_subdistrict <> ''
            ");
        }
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('domicile_district_id');
            $table->dropConstrainedForeignId('domicile_village_id');
            $table->dropConstrainedForeignId('ktp_district_id');
            $table->dropConstrainedForeignId('ktp_village_id');
        });
    }
};
