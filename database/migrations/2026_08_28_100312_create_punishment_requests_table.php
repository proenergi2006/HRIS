<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('punishment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('violation_type', 150);
            $table->enum('sanction_level', ['teguran_lisan', 'sp1', 'sp2', 'sp3', 'demosi', 'phk', 'other'])->default('sp1');
            $table->text('description')->nullable();
            $table->date('incident_date')->nullable();
            $table->date('effective_date')->nullable();
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('punishment_requests');
    }
};
