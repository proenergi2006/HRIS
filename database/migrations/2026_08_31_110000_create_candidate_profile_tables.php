<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Candidate Database (ATS) — lengkapi profil kandidat: expected salary, hasil
     * assessment (di luar MCU), dan CV terstruktur (pendidikan / pengalaman /
     * skill / sertifikasi). Data ini ikut dipindah ke tab Data Karyawan saat
     * kandidat dikonversi jadi Employee.
     */
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->bigInteger('expected_salary')->nullable()->after('source');
            $table->string('assessment_result', 20)->nullable()->after('mcu_result'); // pass/fail/hold
            $table->decimal('assessment_score', 5, 2)->nullable()->after('assessment_result');
            $table->text('assessment_notes')->nullable()->after('assessment_score');
        });

        Schema::create('candidate_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->string('education_level', 50)->nullable(); // SD/SMP/SMA/D3/S1/S2/S3 — teks bebas
            $table->string('major', 150)->nullable();
            $table->string('institution', 200)->nullable();
            $table->year('graduation_year')->nullable();
            $table->decimal('gpa', 4, 2)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('candidate_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->string('company_name', 200);
            $table->string('job_title', 150)->nullable();
            $table->string('company_city', 100)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->bigInteger('last_salary')->nullable();
            $table->text('job_description')->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('candidate_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->enum('proficiency', ['basic', 'intermediate', 'advanced', 'expert'])->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('candidate_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('issuer', 150)->nullable();
            $table->date('issued_date')->nullable();
            $table->date('expires_date')->nullable();
            $table->string('credential_id', 100)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_certifications');
        Schema::dropIfExists('candidate_skills');
        Schema::dropIfExists('candidate_experiences');
        Schema::dropIfExists('candidate_educations');

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn(['expected_salary', 'assessment_result', 'assessment_score', 'assessment_notes']);
        });
    }
};
