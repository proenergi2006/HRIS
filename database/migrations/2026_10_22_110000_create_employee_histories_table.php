<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Riwayat perubahan data kepegawaian per kategori: basic_salary, title,
     * grade, cost_center, employee_status, driver_mutation.
     * Setiap baris = tanggal berlaku + nilai lama + nilai terbaru.
     */
    public function up(): void
    {
        Schema::create('employee_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('category', 30);
            $table->date('effective_date');
            $table->string('old_value', 255)->nullable();
            $table->string('new_value', 255)->nullable();
            $table->string('note', 255)->nullable();
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_id', 'category', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_histories');
    }
};
