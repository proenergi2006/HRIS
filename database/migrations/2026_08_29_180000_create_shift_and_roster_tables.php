<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Shift & Roster (Fase 2 HRD). Roster harian per karyawan menentukan jam masuk
     * yang dipakai AttendanceController::import() untuk menghitung keterlambatan &
     * lembur (menggantikan asumsi hardcoded 08:00).
     */
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 20);
            $table->string('name', 60);
            $table->time('start_time');
            $table->time('end_time');
            $table->smallInteger('break_minutes')->default(0);
            $table->boolean('crosses_midnight')->default(false);
            $table->smallInteger('late_grace_minutes')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('roster_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->date('work_date');
            $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete(); // null = libur
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'work_date']);
        });

        Schema::table('attendance_records', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
            $table->time('scheduled_start')->nullable()->after('shift_id');
            $table->time('scheduled_end')->nullable()->after('scheduled_start');
        });

        $now = now();
        DB::table('shifts')->insert([
            ['company_id' => null, 'code' => 'PAGI',  'name' => 'Pagi (08:00–17:00)',  'start_time' => '08:00:00', 'end_time' => '17:00:00', 'break_minutes' => 60, 'crosses_midnight' => false, 'late_grace_minutes' => 15, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['company_id' => null, 'code' => 'SIANG', 'name' => 'Siang (15:00–23:00)', 'start_time' => '15:00:00', 'end_time' => '23:00:00', 'break_minutes' => 60, 'crosses_midnight' => false, 'late_grace_minutes' => 15, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['company_id' => null, 'code' => 'MALAM', 'name' => 'Malam (23:00–07:00)', 'start_time' => '23:00:00', 'end_time' => '07:00:00', 'break_minutes' => 60, 'crosses_midnight' => true,  'late_grace_minutes' => 15, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shift_id');
            $table->dropColumn(['scheduled_start', 'scheduled_end']);
        });
        Schema::dropIfExists('roster_entries');
        Schema::dropIfExists('shifts');
    }
};
