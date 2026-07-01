<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perdin_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perdin_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approver_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('role', ['direct_manager', 'hr_manager', 'ceo']);
            $table->enum('action', ['approve', 'reject']);
            $table->text('notes')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perdin_approvals');
    }
};
