<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Onboarding checklist (PRD Bab 3 modul #5) — template per company (NULL = berlaku semua
     * company, sama seperti master data lain) + progress per karyawan. Item kategori "aset"
     * yang ditandai selesai membuat baris di employee_facilities (sudah ada, tidak bikin
     * tabel aset baru) — lihat OnboardingController.
     */
    public function up(): void
    {
        Schema::create('onboarding_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label', 200);
            $table->string('category', 20); // dokumen/akun/aset/induction
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('employee_onboarding_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('onboarding_checklist_item_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_done')->default(false);
            $table->dateTime('done_at')->nullable();
            $table->foreignId('done_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'onboarding_checklist_item_id'], 'employee_onboarding_task_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_onboarding_tasks');
        Schema::dropIfExists('onboarding_checklist_items');
    }
};
