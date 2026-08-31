<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Self-service permintaan surat (Fase 2 HRD) — lightweight, HR-managed langsung
     * (tanpa Approval Engine, sesuai keputusan user). Karyawan mengajukan; HR memproses
     * lewat form terbitkan surat existing (EmployeeLetterController) dan hasilnya ditaut
     * balik ke request ini.
     */
    public function up(): void
    {
        Schema::table('letter_templates', function (Blueprint $table) {
            $table->boolean('self_service')->default(false)->after('is_active');
        });

        Schema::create('letter_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('letter_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('purpose', 60)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('pending'); // pending/processed/rejected/cancelled
            $table->foreignId('employee_letter_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('handled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('handled_at')->nullable();
            $table->string('rejection_note', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_requests');
        Schema::table('letter_templates', function (Blueprint $table) {
            $table->dropColumn('self_service');
        });
    }
};
