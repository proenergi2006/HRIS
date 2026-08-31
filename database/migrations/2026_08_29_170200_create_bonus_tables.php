<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run Bonus / Insentif (Fase 2 HRD) — pola sama THR: one-off run, standalone dari
     * payroll_slips. Bila is_taxable, PPh21 dihitung per pembayaran lewat Pph21Calculator
     * dan ikut dijumlah di Bukti Potong PPh21 tahunan.
     */
    public function up(): void
    {
        Schema::create('bonus_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('bonus_type', 20)->default('bonus'); // bonus / insentif / thr_susulan / other
            $table->date('payment_date');
            $table->boolean('is_taxable')->default(true);
            $table->string('status', 20)->default('open'); // open / closed
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('bonus_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bonus_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('base_amount')->nullable();
            $table->bigInteger('gross_amount')->default(0);
            $table->bigInteger('tax_amount')->default(0);
            $table->bigInteger('net_amount')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['bonus_period_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_payments');
        Schema::dropIfExists('bonus_periods');
    }
};
