<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Satu baris per pengajuan — nunjuk ke transaksi aslinya (polymorphic).
    public function up(): void
    {
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->morphs('approvable');   // approvable_type + approvable_id
            $table->foreignId('approval_workflow_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('transaction_type', 50);
            $table->string('summary', 255)->nullable();   // teks ringkas utk inbox

            $table->foreignId('requester_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('subject_employee_id')->nullable()->constrained('employees')->nullOnDelete();

            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->unsignedTinyInteger('current_step_order')->default(0);

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'transaction_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
