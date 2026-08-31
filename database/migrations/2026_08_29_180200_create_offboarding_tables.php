<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Checklist clearance saat resign (Fase 2 HRD) — mirror onboarding_checklist_items /
     * employee_onboarding_tasks. Task dimaterialisasi saat TerminationRequest dibuat
     * (lihat HrRequestController::store()). Item kategori "aset" yang ditandai selesai
     * mengisi employee_facilities.returned_date (sudah ada, tidak bikin tabel aset baru).
     */
    public function up(): void
    {
        Schema::create('offboarding_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label', 200);
            $table->string('category', 20); // aset/akun/dokumen/keuangan/exit
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('employee_offboarding_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('offboarding_checklist_item_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_done')->default(false);
            $table->dateTime('done_at')->nullable();
            $table->foreignId('done_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'offboarding_checklist_item_id'], 'employee_offboarding_task_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_offboarding_tasks');
        Schema::dropIfExists('offboarding_checklist_items');
    }
};
