<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perdin_requests', function (Blueprint $table) {
            $table->id();
            $table->string('no_advance')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('department')->nullable();
            $table->string('destination');
            $table->date('departure_date');
            $table->time('departure_time')->nullable();
            $table->date('return_date');
            $table->time('return_time')->nullable();
            $table->text('purpose')->nullable();
            $table->enum('status', [
                'draft', 'submitted', 'reviewed_manager', 'reviewed_hr', 'approved', 'rejected',
            ])->default('draft');
            $table->unsignedBigInteger('total_budget')->default(0);
            $table->unsignedBigInteger('total_budget_self')->default(0);
            $table->text('notes_rejection')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perdin_requests');
    }
};
