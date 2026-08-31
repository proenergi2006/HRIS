<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            // Nilai enum lama employees.employment_status yang dipetakan ke baris ini
            // (permanent / contract / probation). Boleh null untuk tipe baru.
            $table->string('legacy_key', 30)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now  = now();
        $rows = [
            ['TETAP', 'Karyawan Tetap', 'permanent', 1],
            ['KONTRAK', 'Kontrak (PKWT)', 'contract', 2],
            ['PROBATION', 'Probation', 'probation', 3],
            ['HARIAN', 'Harian Lepas', null, 4],
            ['OUTSOURCE', 'Outsource', null, 5],
            ['MAGANG', 'Magang / Intern', null, 6],
        ];

        DB::table('employee_types')->insert(
            collect($rows)->map(fn ($r) => [
                'code'       => $r[0],
                'name'       => $r[1],
                'legacy_key' => $r[2],
                'sort_order' => $r[3],
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all()
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_types');
    }
};
