<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pre-Employment (PRD Bab 3 modul #4) — data & checklist yang dilengkapi
     * SEBELUM kandidat "accepted" dikonversi jadi Employee. Data terstruktur
     * (KTP/NPWP/rekening/BPJS/personal) + checklist wajib (mirror pola onboarding).
     * Saat convert, data ini dipindah ke employees + employee_bank_accounts +
     * employee_nssf sehingga HR tidak input ulang.
     */
    public function up(): void
    {
        Schema::create('candidate_preemployment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->unique()->constrained()->cascadeOnDelete();

            // Data pribadi
            $table->string('gender', 10)->nullable(); // male/female
            $table->string('birth_place', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->foreignId('marital_status_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('religion_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('blood_type_id')->nullable()->constrained()->nullOnDelete();

            // Identitas (ciphertext — sejajar employees)
            $table->text('ktp_number')->nullable();
            $table->text('npwp_number')->nullable();

            // Alamat
            $table->string('ktp_address', 255)->nullable();
            $table->string('ktp_city', 100)->nullable();
            $table->string('domicile_address', 255)->nullable();
            $table->string('domicile_city', 100)->nullable();

            // Rekening bank
            $table->foreignId('bank_id')->nullable()->constrained()->nullOnDelete();
            $table->text('bank_account_number')->nullable();
            $table->string('bank_account_holder', 150)->nullable();

            // BPJS
            $table->string('bpjs_health_number', 50)->nullable();
            $table->date('bpjs_health_date')->nullable();
            $table->string('bpjs_employment_number', 50)->nullable();
            $table->date('bpjs_employment_date')->nullable();

            // Kontak darurat
            $table->string('emergency_contact_name', 120)->nullable();
            $table->string('emergency_contact_relation', 60)->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('preemployment_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete(); // NULL = global
            $table->string('label', 200);
            $table->string('category', 20)->default('dokumen'); // dokumen/data/verifikasi
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('candidate_preemployment_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('preemployment_checklist_item_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_done')->default(false);
            $table->dateTime('done_at')->nullable();
            $table->foreignId('done_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->unique(['candidate_id', 'preemployment_checklist_item_id'], 'cpt_candidate_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_preemployment_tasks');
        Schema::dropIfExists('preemployment_checklist_items');
        Schema::dropIfExists('candidate_preemployment');
    }
};
