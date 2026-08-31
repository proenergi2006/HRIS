<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail perubahan aturan approval (PRD Bab 7.5 — "Histori perubahan
 * approval matrix agar bisa ditelusuri kalau ada perubahan aturan").
 * Dicatat eksplisit di ApprovalWorkflowController (pola sama org_change_logs).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_workflow_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_type', 50);
            $table->string('transaction_label', 100)->nullable();
            $table->string('action', 20); // created / updated / copied
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->index(['company_id', 'transaction_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_workflow_change_logs');
    }
};
