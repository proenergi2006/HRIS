<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // NSSF (BPJS) — 1 baris per karyawan.
    public function up(): void
    {
        Schema::create('employee_nssf', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained()->cascadeOnDelete();

            // BPJS Kesehatan
            $table->boolean('health_registered')->default(false);
            $table->string('health_number', 50)->nullable();
            $table->date('health_join_date')->nullable();

            // BPJS Ketenagakerjaan
            $table->boolean('employment_registered')->default(false);
            $table->string('employment_number', 50)->nullable();
            $table->date('employment_join_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_nssf');
    }
};
