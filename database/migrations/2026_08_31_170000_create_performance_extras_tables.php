<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Performance Management — pelengkap appraisal formal (KPI/objective) yang sudah ada:
     * (1) Continuous Feedback / 1-on-1 check-in — catatan ngobrol rutin manager-karyawan,
     *     di luar siklus appraisal tahunan/semesteran.
     * (2) OKR cascading — Sasaran Perusahaan/Departemen (company_objectives, pohon lewat
     *     parent_objective_id) yang bisa ditautkan ke KPI individu (appraisal_objectives).
     * (3) 360° Feedback — 1 subjek dinilai banyak rater (diri sendiri/atasan/rekan/bawahan)
     *     dalam satu cycle, pertanyaan kompetensi tetap (didefinisikan di kode, bukan tabel
     *     terpisah, supaya skema tetap ringkas).
     */
    public function up(): void
    {
        Schema::create('performance_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('checkin_date');
            $table->text('notes');
            $table->text('action_items')->nullable();
            $table->text('employee_comment')->nullable();
            $table->date('next_checkin_date')->nullable();
            $table->timestamps();
        });

        Schema::create('company_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_objective_id')->nullable()->constrained('company_objectives')->nullOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter')->nullable(); // NULL = tahunan
            $table->foreignId('owner_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('status', 20)->default('active'); // active/completed/cancelled
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('appraisal_objectives', function (Blueprint $table) {
            $table->foreignId('company_objective_id')->nullable()->after('appraisal_id')
                ->constrained('company_objectives')->nullOnDelete();
        });

        Schema::create('feedback_360_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title', 150);
            $table->date('period_start');
            $table->date('period_end');
            $table->string('status', 20)->default('draft'); // draft/open/closed
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('feedback_360_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained('feedback_360_cycles')->cascadeOnDelete();
            $table->foreignId('subject_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('rater_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('relation_type', 20); // self/manager/peer/subordinate
            $table->string('status', 20)->default('pending'); // pending/submitted
            $table->dateTime('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['cycle_id', 'subject_employee_id', 'rater_employee_id'], 'f360_review_unique');
        });

        Schema::create('feedback_360_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('feedback_360_reviews')->cascadeOnDelete();
            $table->string('question_key', 60);
            $table->unsignedTinyInteger('rating')->nullable(); // 1-5
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['review_id', 'question_key'], 'f360_answer_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_360_answers');
        Schema::dropIfExists('feedback_360_reviews');
        Schema::dropIfExists('feedback_360_cycles');
        Schema::table('appraisal_objectives', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_objective_id');
        });
        Schema::dropIfExists('company_objectives');
        Schema::dropIfExists('performance_checkins');
    }
};
