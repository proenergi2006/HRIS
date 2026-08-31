<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Biaya rekrutmen (Fase 2 HRD) — input untuk metrik cost-per-hire di HR Analytics. */
    public function up(): void
    {
        Schema::create('recruitment_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_requisition_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category', 40); // iklan/agency/assessment/referral/lainnya
            $table->bigInteger('amount');
            $table->date('incurred_on');
            $table->string('notes', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_costs');
    }
};
