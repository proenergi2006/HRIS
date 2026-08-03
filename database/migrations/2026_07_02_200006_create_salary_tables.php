<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Komponen gaji master per perusahaan
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 100);
            $table->enum('type', ['allowance', 'deduction']); // tunjangan atau potongan
            $table->boolean('is_taxable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed komponen default
        $now = now();
        DB::table('salary_components')->insert([
            ['company_id' => null, 'name' => 'Gaji Pokok',            'type' => 'allowance', 'is_taxable' => true,  'sort_order' => 1,  'created_at' => $now, 'updated_at' => $now],
            ['company_id' => null, 'name' => 'Tunjangan Transportasi', 'type' => 'allowance', 'is_taxable' => false, 'sort_order' => 2,  'created_at' => $now, 'updated_at' => $now],
            ['company_id' => null, 'name' => 'Tunjangan Makan',        'type' => 'allowance', 'is_taxable' => false, 'sort_order' => 3,  'created_at' => $now, 'updated_at' => $now],
            ['company_id' => null, 'name' => 'Tunjangan Jabatan',      'type' => 'allowance', 'is_taxable' => true,  'sort_order' => 4,  'created_at' => $now, 'updated_at' => $now],
            ['company_id' => null, 'name' => 'BPJS Kesehatan',         'type' => 'deduction', 'is_taxable' => false, 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['company_id' => null, 'name' => 'BPJS Ketenagakerjaan',   'type' => 'deduction', 'is_taxable' => false, 'sort_order' => 11, 'created_at' => $now, 'updated_at' => $now],
            ['company_id' => null, 'name' => 'Potongan Absensi',       'type' => 'deduction', 'is_taxable' => false, 'sort_order' => 12, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Nilai komponen per karyawan
        Schema::create('employee_salary_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('salary_component_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('amount')->default(0);
            $table->timestamps();

            $table->unique(['employee_id', 'salary_component_id'], 'emp_sal_comp_unique');
        });

        // Periode penggajian
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('month');
            $table->year('year');
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->text('notes')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'month', 'year']);
        });

        // Slip gaji per karyawan per periode
        Schema::create('payroll_slips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->integer('working_days')->default(0);
            $table->integer('attendance_days')->default(0);
            $table->decimal('leave_days', 5, 1)->default(0);
            $table->integer('alpha_days')->default(0);
            $table->integer('late_minutes')->default(0);
            $table->bigInteger('total_allowances')->default(0);
            $table->bigInteger('total_deductions')->default(0);
            $table->bigInteger('gross_salary')->default(0);
            $table->bigInteger('net_salary')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['payroll_period_id', 'employee_id']);
        });

        // Detail komponen per slip
        Schema::create('payroll_slip_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_slip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('salary_component_id')->nullable()->constrained()->nullOnDelete();
            $table->string('component_name', 100);
            $table->enum('type', ['allowance', 'deduction']);
            $table->bigInteger('amount')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_slip_details');
        Schema::dropIfExists('payroll_slips');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('employee_salary_components');
        Schema::dropIfExists('salary_components');
    }
};
