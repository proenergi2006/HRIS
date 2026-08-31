<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Surat yang sudah diterbitkan untuk karyawan — snapshot judul/isi (bukan referensi
     * live ke template) supaya riwayat surat tidak berubah kalau templatenya diedit belakangan.
     */
    public function up(): void
    {
        Schema::create('employee_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('letter_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('letter_number', 100);
            $table->string('category', 50);
            $table->string('title', 200);
            $table->longText('body');
            $table->date('issued_date');
            $table->foreignId('issued_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_letters');
    }
};
