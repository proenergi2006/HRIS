<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Tambah tipe karyawan "Mitra" — master data saja, tidak perlu kolom baru
     *  (dropdown Tipe Karyawan di form Tambah/Edit Karyawan sudah dinamis dari
     *  tabel employee_types). legacy_key null, sama seperti Harian Lepas/
     *  Outsource/Magang — tidak dipetakan ke enum employees.employment_status lama. */
    public function up(): void
    {
        DB::table('employee_types')->insert([
            'code'       => 'MITRA',
            'name'       => 'Mitra',
            'legacy_key' => null,
            'sort_order' => 7,
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('employee_types')->where('code', 'MITRA')->delete();
    }
};
