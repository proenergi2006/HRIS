<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Job Requisition — awal alur Recruitment (PRD Bab 3 modul #3). Lewat Approval Engine
     * yang sama seperti modul lain (pola manual seperti ManpowerPlan/PerdinRequest).
     */
    public function up(): void
    {
        Schema::create('job_requisitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete(); // isi jabatan kosong existing, atau kosongkan = jabatan baru
            $table->string('title', 150);
            $table->text('reason')->nullable();
            $table->unsignedInteger('headcount_requested')->default(1);
            $table->foreignId('employment_type_id')->nullable()->constrained('employee_types')->nullOnDelete();
            $table->date('target_join_date')->nullable();
            $table->string('status', 20)->default('draft'); // draft/pending/approved/rejected/cancelled/closed
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes_rejection')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_requisitions');
    }
};
