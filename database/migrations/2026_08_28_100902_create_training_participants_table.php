<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Peserta + riwayat ikut training (PRD Bab 3 modul #11 — training record). */
    public function up(): void
    {
        Schema::create('training_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status', 20)->default('planned'); // planned/ongoing/completed/cancelled/no_show
            $table->string('score', 20)->nullable(); // nilai/predikat — teks bebas (angka atau "Lulus", dst.)
            $table->string('certificate_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_participants');
    }
};
