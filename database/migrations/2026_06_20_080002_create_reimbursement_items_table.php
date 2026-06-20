<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reimbursement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reimbursement_request_id')->constrained()->cascadeOnDelete();
            $table->string('patient_name');
            $table->date('treatment_date');
            $table->string('institution');
            $table->string('diagnose')->nullable();
            $table->unsignedBigInteger('amount_administration')->default(0);
            $table->unsignedBigInteger('amount_doctor')->default(0);
            $table->unsignedBigInteger('amount_medicine')->default(0);
            $table->unsignedBigInteger('amount_lab')->default(0);
            $table->unsignedBigInteger('amount_consultation')->default(0);
            $table->unsignedBigInteger('amount_dental')->default(0);
            $table->unsignedBigInteger('amount_glasses')->default(0);
            $table->unsignedBigInteger('amount_lens')->default(0);
            $table->unsignedBigInteger('amount_pregnancy')->default(0);
            $table->unsignedBigInteger('total_claim')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reimbursement_items');
    }
};
