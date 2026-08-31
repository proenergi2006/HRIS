<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Materialisasi step per pengajuan (snapshot dari workflow_steps saat submit)
    // + approver yang sudah di-resolve + jejak tindakan.
    public function up(): void
    {
        Schema::create('approval_request_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_request_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('step_order');
            $table->enum('approver_type', [
                'direct_manager', 'section_head', 'department_head',
                'division_head', 'specific_position', 'specific_role',
            ]);
            $table->string('approver_label', 150)->nullable();
            $table->foreignId('approver_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status', ['pending', 'approved', 'rejected', 'skipped'])->default('pending');
            $table->foreignId('acted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acted_at')->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamps();

            $table->index(['approver_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_request_steps');
    }
};
