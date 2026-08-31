<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Manpower Planning (PRD HRIS v1.2 Bab 3 modul #2) — rencana headcount per unit/posisi
     * per company per periode, dibandingkan dengan actual (dihitung live dari Employee,
     * bukan kolom tersimpan — konsisten dgn pola vacant-position org chart).
     */
    public function up(): void
    {
        Schema::create('manpower_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month')->nullable(); // NULL = rencana tahunan
            $table->unsignedInteger('planned_headcount');
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('draft'); // draft/pending/approved/rejected/cancelled
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes_rejection')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manpower_plans');
    }
};
