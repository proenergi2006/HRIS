<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Competency Framework (melengkapi PRD Bab 3 modul #11 Training & Development —
 * bagian "competency"). Kamus kompetensi + kompetensi wajib per jabatan +
 * penilaian kompetensi karyawan (current-state, 1 baris per employee+competency).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->string('category', 50)->nullable(); // Core/Managerial/Technical/Behavioral — teks bebas
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('position_competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->constrained()->cascadeOnDelete();
            $table->foreignId('competency_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('required_level'); // 1-5
            $table->string('notes', 255)->nullable();
            $table->timestamps();
            $table->unique(['position_id', 'competency_id']);
        });

        Schema::create('employee_competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('competency_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('actual_level'); // 1-5
            $table->date('assessed_on')->nullable();
            $table->foreignId('assessor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('notes', 255)->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'competency_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_competencies');
        Schema::dropIfExists('position_competencies');
        Schema::dropIfExists('competencies');
    }
};
