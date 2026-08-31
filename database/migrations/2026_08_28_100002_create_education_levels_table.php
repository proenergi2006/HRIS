<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_levels', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            // sort_order 0..9 — dipakai untuk validasi jenjang minimal pendidikan
            // (mis. syarat minimal pendidikan sebuah jabatan).
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now  = now();
        $rows = [
            ['TIDAK_SEKOLAH', 'Tidak Sekolah', 0],
            ['SD', 'SD / Sederajat', 1],
            ['SMP', 'SMP / Sederajat', 2],
            ['SMA', 'SMA / SMK / Sederajat', 3],
            ['D1', 'Diploma 1 (D1)', 4],
            ['D2', 'Diploma 2 (D2)', 5],
            ['D3', 'Diploma 3 (D3)', 6],
            ['S1', 'Sarjana / D4 (S1)', 7],
            ['S2', 'Magister (S2)', 8],
            ['S3', 'Doktor (S3)', 9],
        ];

        DB::table('education_levels')->insert(
            collect($rows)->map(fn ($r) => [
                'code'       => $r[0],
                'name'       => $r[1],
                'sort_order' => $r[2],
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all()
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('education_levels');
    }
};
