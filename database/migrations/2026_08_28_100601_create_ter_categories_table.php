<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kategori TER (Tarif Efektif Rata-rata) PPh21 bulanan sesuai PP 58/2023 & PMK 168/2023
     * — 3 kategori (A/B/C) ditentukan dari status PTKP (status kawin + jumlah tanggungan).
     */
    public function up(): void
    {
        Schema::create('ter_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->unique(); // A, B, C
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        DB::table('ter_categories')->insert([
            ['code' => 'A', 'name' => 'TER A', 'description' => 'PTKP: TK/0, TK/1, K/0', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'B', 'name' => 'TER B', 'description' => 'PTKP: TK/2, TK/3, K/1, K/2', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'C', 'name' => 'TER C', 'description' => 'PTKP: K/3', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('ter_categories');
    }
};
