<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Katalog program training (PRD Bab 3 modul #11 — Training plan). */
    public function up(): void
    {
        Schema::create('training_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 200);
            $table->string('category', 100)->nullable(); // technical/soft-skill/leadership/compliance/dst — teks bebas
            $table->string('provider', 150)->nullable();
            $table->unsignedInteger('duration_hours')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_programs');
    }
};
