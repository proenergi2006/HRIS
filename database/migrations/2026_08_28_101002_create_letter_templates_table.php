<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Template surat (PRD Bab 3 modul #7 — "surat"). Body pakai placeholder {{field}}. */
    public function up(): void
    {
        Schema::create('letter_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 200);
            $table->string('category', 50)->default('lainnya'); // keterangan_kerja/peringatan/mutasi/lainnya
            $table->longText('body');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_templates');
    }
};
