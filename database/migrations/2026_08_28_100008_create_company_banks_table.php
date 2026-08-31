<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rekening bank milik masing-masing perusahaan — dipakai payroll sebagai
        // rekening sumber pembayaran gaji per PT.
        Schema::create('company_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_id')->constrained()->restrictOnDelete();
            $table->string('account_number', 50);
            $table->string('account_name', 150);
            $table->string('branch_name', 150)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'bank_id', 'account_number'], 'company_bank_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_banks');
    }
};
