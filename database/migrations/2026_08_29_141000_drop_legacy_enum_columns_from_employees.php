<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cleanup kolom string lama di `employees` yang sudah digantikan FK master
 * (Master Data PRD Bab 5): `marital_status` -> `marital_status_id` (MaritalStatus),
 * `religion_name` -> `religion_id` (Religion), `blood_type` -> `blood_type_id`
 * (BloodType). FK-nya sudah lama diisi (EmployeeImport & form Edit Karyawan
 * pakai versi _id), dan tidak ada lagi read-path ke kolom string ini setelah
 * accessor getMaritalStatusLabelAttribute() dipindah ke relasi maritalStatus.
 *
 * CATATAN: `contract_end_date` SENGAJA TIDAK di-drop — walau ada tabel
 * `employee_contracts`, kolom denormalisasi ini masih aktif dipakai di banyak
 * tempat (SendContractExpiryReminders, dashboard admin, LaporanController,
 * header notifikasi, halaman & export karyawan). Bukan kolom mati.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['marital_status', 'religion_name', 'blood_type']);
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('marital_status', 30)->nullable()->after('npwp_date');
            $table->string('religion_name', 50)->nullable()->after('marital_status_id');
            $table->string('blood_type', 5)->nullable()->after('religion_id');
        });
    }
};
