<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained()->cascadeOnDelete();
            $table->string('code', 10)->unique();   // kode wilayah BPS (4 digit)
            $table->string('name', 100);
            $table->enum('type', ['kota', 'kabupaten'])->default('kabupaten');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['province_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
