<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marital_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            // Kode dasar PTKP: TK (tidak kawin) / K (kawin) / K/I (kawin, penghasilan istri digabung).
            // Jumlah tanggungan ditambahkan terpisah saat perhitungan pajak.
            $table->string('ptkp_code', 10)->nullable();
            // Nilai enum lama di kolom employees.marital_status yang dipetakan ke baris ini.
            $table->string('legacy_key', 30)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now  = now();
        $rows = [
            ['BELUM_KAWIN', 'Belum Kawin', 'TK', 'belum_kawin', 1],
            ['KAWIN', 'Kawin', 'K', 'kawin', 2],
            ['CERAI_HIDUP', 'Cerai Hidup', 'TK', 'cerai_hidup', 3],
            ['CERAI_MATI', 'Cerai Mati', 'TK', 'cerai_mati', 4],
        ];

        DB::table('marital_statuses')->insert(
            collect($rows)->map(fn ($r) => [
                'code'       => $r[0],
                'name'       => $r[1],
                'ptkp_code'  => $r[2],
                'legacy_key' => $r[3],
                'sort_order' => $r[4],
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all()
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('marital_statuses');
    }
};
