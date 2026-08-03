<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Header
            $table->string('photo')->nullable()->after('name');

            // Personal Data
            $table->enum('gender', ['L', 'P'])->nullable()->after('photo');
            $table->string('birth_place', 100)->nullable()->after('gender');
            $table->date('birth_date')->nullable()->after('birth_place');
            $table->string('ktp_number', 30)->nullable()->after('birth_date');
            $table->string('npwp_number', 30)->nullable()->after('ktp_number');
            $table->string('npwp_city', 100)->nullable()->after('npwp_number');
            $table->date('npwp_date')->nullable()->after('npwp_city');
            $table->enum('marital_status', ['belum_kawin', 'kawin', 'cerai_hidup', 'cerai_mati'])->nullable()->after('npwp_date');
            $table->string('religion', 30)->nullable()->after('marital_status');
            $table->enum('blood_type', ['A', 'B', 'AB', 'O'])->nullable()->after('religion');
            $table->enum('employee_type', ['local', 'expat'])->default('local')->after('blood_type');
            $table->string('finger_id', 30)->nullable()->after('employee_type');

            // Email & Phone
            $table->string('email', 150)->nullable()->after('finger_id');
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('home_phone', 30)->nullable()->after('phone');

            // Alamat Domisili
            $table->text('domicile_address')->nullable()->after('home_phone');
            $table->string('domicile_city', 100)->nullable()->after('domicile_address');
            $table->string('domicile_district', 100)->nullable()->after('domicile_city');
            $table->string('domicile_subdistrict', 100)->nullable()->after('domicile_district');

            // Alamat KTP
            $table->text('ktp_address')->nullable()->after('domicile_subdistrict');
            $table->string('ktp_city', 100)->nullable()->after('ktp_address');
            $table->string('ktp_district', 100)->nullable()->after('ktp_city');
            $table->string('ktp_subdistrict', 100)->nullable()->after('ktp_district');

            // Kontak Darurat
            $table->string('emergency_contact_name', 150)->nullable()->after('ktp_subdistrict');
            $table->string('emergency_contact_relation', 100)->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_phone', 30)->nullable()->after('emergency_contact_relation');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'photo', 'gender', 'birth_place', 'birth_date', 'ktp_number',
                'npwp_number', 'npwp_city', 'npwp_date', 'marital_status', 'religion',
                'blood_type', 'employee_type', 'finger_id',
                'email', 'phone', 'home_phone',
                'domicile_address', 'domicile_city', 'domicile_district', 'domicile_subdistrict',
                'ktp_address', 'ktp_city', 'ktp_district', 'ktp_subdistrict',
                'emergency_contact_name', 'emergency_contact_relation', 'emergency_contact_phone',
            ]);
        });
    }
};
