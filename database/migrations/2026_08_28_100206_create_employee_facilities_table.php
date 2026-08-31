<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Fasilitas yang diterima karyawan (laptop, kendaraan, tunjangan non-cash, dll).
    public function up(): void
    {
        Schema::create('employee_facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('description', 255)->nullable();
            $table->date('received_date')->nullable();
            $table->date('returned_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_facilities');
    }
};
