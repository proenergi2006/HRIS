<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kasbon / pinjaman karyawan + potong cicilan otomatis di payroll (Fase 2 HRD).
     * Dikelola HR langsung (tanpa Approval Engine). Tiap pinjaman punya jadwal cicilan
     * (loan_installments) per bulan; PayrollController::generate() menariknya sebagai
     * potongan lewat calculation_type 'loan_installment'.
     */
    public function up(): void
    {
        Schema::create('employee_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('loan_type', 20)->default('kasbon'); // kasbon / pinjaman
            $table->string('reference_no', 50)->nullable();
            $table->bigInteger('principal');
            $table->unsignedSmallInteger('installment_count');
            $table->bigInteger('installment_amount');
            $table->unsignedTinyInteger('start_month');
            $table->unsignedSmallInteger('start_year');
            $table->string('status', 20)->default('active'); // active / completed / cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('loan_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_slip_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->bigInteger('amount');
            $table->string('status', 20)->default('pending'); // pending / deducted / waived
            $table->dateTime('deducted_at')->nullable();
            $table->timestamps();

            $table->index(['employee_loan_id', 'period_year', 'period_month'], 'loan_inst_period_idx');
        });

        // Komponen potongan cicilan — dihitung otomatis saat generate slip.
        DB::statement("ALTER TABLE salary_components MODIFY COLUMN calculation_type ENUM(
            'manual','percent_of_base','late_deduction','medical_claim','mirror_pph21',
            'position_fixed','position_daily','overtime','pph21_ter','loan_installment'
        ) NOT NULL DEFAULT 'manual'");

        if (! DB::table('salary_components')->where('name', 'Potongan Kasbon/Pinjaman')->exists()) {
            DB::table('salary_components')->insert([
                'company_id'       => null,
                'name'             => 'Potongan Kasbon/Pinjaman',
                'type'             => 'deduction',
                'calculation_type' => 'loan_installment',
                'is_taxable'       => false,
                'is_active'        => true,
                'sort_order'       => 15,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('salary_components')->where('name', 'Potongan Kasbon/Pinjaman')->delete();

        DB::statement("ALTER TABLE salary_components MODIFY COLUMN calculation_type ENUM(
            'manual','percent_of_base','late_deduction','medical_claim','mirror_pph21',
            'position_fixed','position_daily','overtime','pph21_ter'
        ) NOT NULL DEFAULT 'manual'");

        Schema::dropIfExists('loan_installments');
        Schema::dropIfExists('employee_loans');
    }
};
