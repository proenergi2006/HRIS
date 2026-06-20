<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reimbursement_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('period_year');
            $table->string('balance_type', 20)->default('medical');
            $table->unsignedBigInteger('initial_balance')->default(0);
            $table->unsignedBigInteger('used_balance')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'period_year', 'balance_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reimbursement_balances');
    }
};
