<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

/**
 * Enkripsi data sensitif karyawan (PRD Bab 9 — NIK, NPWP, No. rekening).
 * Lebarkan kolom ke TEXT dulu (ciphertext jauh lebih panjang dari plaintext),
 * lalu jalankan command `employees:encrypt-sensitive` (butuh APP_KEY, tidak
 * bisa murni SQL) untuk mengenkripsi data yang sudah ada. Cast 'encrypted'
 * ditambahkan terpisah di App\Models\Employee / EmployeeBankAccount.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->text('ktp_number')->nullable()->change();
            $table->text('npwp_number')->nullable()->change();
        });

        Schema::table('employee_bank_accounts', function (Blueprint $table) {
            $table->text('account_number')->change();
        });

        Artisan::call('employees:encrypt-sensitive');
    }

    public function down(): void
    {
        // Rollback skema saja (lebar kolom) — TIDAK mendekripsi balik data
        // yang sudah terenkripsi, karena down() migration bukan tempat yang
        // aman untuk operasi kriptografi masal. Kalau perlu rollback penuh,
        // jalankan dekripsi manual dulu sebelum menyempitkan kolom.
        Schema::table('employees', function (Blueprint $table) {
            $table->string('ktp_number', 30)->nullable()->change();
            $table->string('npwp_number', 30)->nullable()->change();
        });

        Schema::table('employee_bank_accounts', function (Blueprint $table) {
            $table->string('account_number', 50)->change();
        });
    }
};
