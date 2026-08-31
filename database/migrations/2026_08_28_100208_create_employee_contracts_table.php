<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Kontrak kerja — 1:banyak (menggantikan kolom tunggal employees.contract_end_date,
    // yang dibiarkan untuk sementara sebagai ringkasan "kontrak aktif berakhir").
    public function up(): void
    {
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->enum('contract_type', ['pkwtt', 'pkwt', 'probation', 'magang', 'harian', 'other'])->default('pkwt');
            $table->string('number', 100)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'expired', 'terminated', 'renewed'])->default('active');
            $table->string('document_path', 500)->nullable();
            $table->string('original_name', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Backfill dari contract_end_date yang sudah ada.
        DB::table('employees')
            ->whereNotNull('contract_end_date')
            ->orderBy('id')
            ->each(function ($emp) {
                DB::table('employee_contracts')->insert([
                    'employee_id'   => $emp->id,
                    'contract_type' => $emp->employment_status === 'probation' ? 'probation' : 'pkwt',
                    'start_date'    => $emp->start_date ?? $emp->contract_end_date,
                    'end_date'      => $emp->contract_end_date,
                    'status'        => 'active',
                    'notes'         => 'Migrasi otomatis dari kolom contract_end_date.',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_contracts');
    }
};
