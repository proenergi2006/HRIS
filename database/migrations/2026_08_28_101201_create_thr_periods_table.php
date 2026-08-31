<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * THR (Tunjangan Hari Raya) — PRD Bab 3 modul #9 secara eksplisit menyebut THR,
     * belum ada implementasinya sama sekali sampai batch ini. Mengikuti Permenaker
     * No. 6/2016: karyawan masa kerja >=1 bulan berhak THR proporsional
     * (bulan kerja / 12) x (Gaji Pokok + Tunjangan Tetap), penuh 1x gaji kalau >=12 bulan.
     */
    public function up(): void
    {
        Schema::create('thr_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('holiday_name', 100); // mis. "Idul Fitri 1447H", "Natal 2026"
            $table->date('payment_date');
            $table->string('status', 20)->default('open'); // open/closed
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('thr_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thr_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('base_salary'); // snapshot Gaji Pokok + Tunjangan Jabatan saat generate
            $table->unsignedTinyInteger('months_worked'); // masa kerja dlm bulan, dibatasi maks 12
            $table->decimal('proration_ratio', 4, 3); // months_worked / 12
            $table->bigInteger('thr_amount');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['thr_period_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thr_payments');
        Schema::dropIfExists('thr_periods');
    }
};
