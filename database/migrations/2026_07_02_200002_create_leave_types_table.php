<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 100);
            $table->integer('days_per_year')->default(0); // 0 = tidak dibatasi (sakit, dll)
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_doc')->default(false); // butuh surat dokter, dll
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default leave types untuk semua perusahaan
        $now = now();
        DB::table('leave_types')->insert([
            ['company_id' => null, 'name' => 'Cuti Tahunan',     'days_per_year' => 12, 'is_paid' => true,  'requires_doc' => false, 'created_at' => $now, 'updated_at' => $now],
            ['company_id' => null, 'name' => 'Cuti Sakit',       'days_per_year' => 0,  'is_paid' => true,  'requires_doc' => true,  'created_at' => $now, 'updated_at' => $now],
            ['company_id' => null, 'name' => 'Cuti Melahirkan',  'days_per_year' => 90, 'is_paid' => true,  'requires_doc' => true,  'created_at' => $now, 'updated_at' => $now],
            ['company_id' => null, 'name' => 'Cuti Menikah',     'days_per_year' => 3,  'is_paid' => true,  'requires_doc' => false,  'created_at' => $now, 'updated_at' => $now],
            ['company_id' => null, 'name' => 'Cuti Duka',        'days_per_year' => 2,  'is_paid' => true,  'requires_doc' => false,  'created_at' => $now, 'updated_at' => $now],
            ['company_id' => null, 'name' => 'Izin Tidak Masuk', 'days_per_year' => 0,  'is_paid' => false, 'requires_doc' => false,  'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
