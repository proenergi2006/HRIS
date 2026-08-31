<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Master Kecamatan (districts) & Kelurahan/Desa (villages) — PRD Bab 5.1
 * ("City / Province / Village"). Sebelumnya kecamatan/kelurahan cuma teks bebas
 * di alamat karyawan. Dataset penuh Indonesia (~7rb kecamatan, ~83rb kelurahan)
 * di-source terpisah dari wilayah.id/Kemendagri (PRD Bab 12) — seeder di app ini
 * cuma isi starter set kota-kota besar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->string('code', 15)->unique();
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['city_id', 'name']);
        });

        Schema::create('villages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->string('type', 15)->default('kelurahan'); // kelurahan / desa
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['district_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villages');
        Schema::dropIfExists('districts');
    }
};
