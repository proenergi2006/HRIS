<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Candidate + tahapan seleksi (interview, offer) + dokumen Pre-Employment
     * (PRD Bab 3 modul #3/#4). ERD hint PRD: candidates, interviews, offers.
     */
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_requisition_id')->nullable()->constrained()->nullOnDelete(); // NULL = walk-in
            $table->string('name', 150);
            $table->string('email', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('source', 100)->nullable(); // referral, job portal, walk-in, dst — teks bebas
            $table->string('status', 20)->default('applied'); // applied/screening/interview/offer/accepted/rejected/withdrawn/converted
            $table->string('mcu_result', 20)->nullable(); // fit/unfit/conditional
            $table->foreignId('converted_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('candidate_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->string('stage', 100); // mis. "HR Screening", "User Interview", "Final Interview"
            $table->dateTime('scheduled_at')->nullable();
            $table->foreignId('interviewer_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('result', 20)->default('pending'); // pending/pass/fail
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('candidate_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->bigInteger('offered_salary')->nullable();
            $table->date('start_date_offered')->nullable();
            $table->string('status', 20)->default('draft'); // draft/sent/accepted/declined/expired
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('candidate_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->string('doc_type', 60); // ktp, ijazah, mcu, skck, npwp, dst.
            $table->string('title', 200);
            $table->string('file_path');
            $table->string('original_name', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_documents');
        Schema::dropIfExists('candidate_offers');
        Schema::dropIfExists('candidate_interviews');
        Schema::dropIfExists('candidates');
    }
};
