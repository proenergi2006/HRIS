<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pengajuan Lembur self-service lewat Approval Engine (PRD Bab 3.2 — Overtime
     * eksplisit disebut butuh Dynamic Approval Workflow). Transaction type
     * 'overtime_request' SUDAH ada di ApprovalWorkflow::$transactionTypes +
     * ApprovalWorkflowSeeder sejak awal (default: direct_manager) tapi sampai
     * sekarang belum ada model yang mengimplementasikannya — tabel ini mengisi
     * gap itu. Begitu disetujui, otomatis tercatat ke AttendanceRecord.overtime_minutes
     * (sumber data yang sama dipakai halaman HR > Lembur & payroll Tunjangan Lembur).
     */
    public function up(): void
    {
        Schema::create('overtime_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->decimal('planned_hours', 4, 1);
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('draft'); // draft/pending/approved/rejected/cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_requests');
    }
};
