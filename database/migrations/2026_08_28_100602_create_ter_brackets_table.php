<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lapisan tarif TER per kategori (income_from s/d income_to, income_to NULL = tak terhingga).
     *
     * ‼️ PENTING: angka batas & persentase di seed bawah ini adalah PERKIRAAN/ILUSTRATIF
     * (bentuk kurva progresif TER yang benar — makin tinggi gaji, makin tinggi tarif, dan
     * kategori C > B > A di penghasilan yang sama), BUKAN salinan persis tabel resmi
     * Lampiran PMK 168/2023. WAJIB diganti dengan tabel resmi (atau divalidasi paralel vs
     * jPayroll) sebelum dipakai untuk penggajian riil — edit lewat menu
     * Master Data > Tarif PPh21 (TER), tidak perlu developer.
     */
    public function up(): void
    {
        Schema::create('ter_brackets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ter_category_id')->constrained('ter_categories')->cascadeOnDelete();
            $table->unsignedBigInteger('income_from');
            $table->unsignedBigInteger('income_to')->nullable(); // NULL = tak terhingga
            $table->decimal('rate_percent', 5, 2);
            $table->timestamps();
        });

        $bounds = [0, 5_000_000, 10_000_000, 15_000_000, 20_000_000, 25_000_000, 30_000_000,
                   40_000_000, 50_000_000, 75_000_000, 100_000_000, 150_000_000, 200_000_000,
                   500_000_000, 1_000_000_000];

        $rates = [
            'A' => [0, 2, 4, 6, 8, 10, 12, 15, 17, 20, 23, 25, 27, 30, 32],
            'B' => [0, 3, 6, 8, 11, 13, 15, 18, 20, 23, 25, 27, 29, 31, 33],
            'C' => [0, 5, 8, 11, 14, 16, 18, 21, 23, 25, 28, 30, 32, 33, 34],
        ];

        $categoryIds = DB::table('ter_categories')->pluck('id', 'code');
        $rows = [];
        foreach ($rates as $code => $rateList) {
            foreach ($bounds as $i => $from) {
                $rows[] = [
                    'ter_category_id' => $categoryIds[$code],
                    'income_from'     => $from,
                    'income_to'       => $bounds[$i + 1] ?? null,
                    'rate_percent'    => $rateList[$i],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }
        }
        DB::table('ter_brackets')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('ter_brackets');
    }
};
