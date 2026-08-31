<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Header — 1 workflow per jenis transaksi per perusahaan.
    public function up(): void
    {
        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_type', 50);   // leave_request, reward_request, ...
            $table->string('name', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'transaction_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_workflows');
    }
};
