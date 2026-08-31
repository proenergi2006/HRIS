<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Riwayat kerja SEBELUM bergabung (bukan histori internal — itu employee_org_experiences).
    public function up(): void
    {
        Schema::create('employee_work_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('company_name', 200);
            $table->string('company_city', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('end_job_title', 150)->nullable();
            $table->bigInteger('end_pay_rate')->nullable();
            $table->text('job_description')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_work_experiences');
    }
};
