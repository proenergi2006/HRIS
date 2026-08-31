<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('levels', function (Blueprint $table) {
            // Urutan tingkatan (1 = tertinggi/paling senior). Dipakai untuk
            // menyusun Struktur Organisasi supaya level lebih tinggi tampil
            // di atas level di bawahnya, bukan sejajar/abjad.
            $table->unsignedSmallInteger('rank')->default(99)->after('description');
        });

        // Urutan awal berdasar hierarki jabatan umum — bisa diubah lagi
        // lewat menu Data Karyawan > Job Levels.
        $defaults = [
            'Direksi'      => 1,
            'Manager'      => 2,
            'SPV'          => 3,
            'Senior Staff' => 4,
            'Staff'        => 5,
            'Admin'        => 6,
        ];

        foreach ($defaults as $name => $rank) {
            DB::table('levels')->where('name', $name)->update(['rank' => $rank]);
        }
    }

    public function down(): void
    {
        Schema::table('levels', function (Blueprint $table) {
            $table->dropColumn('rank');
        });
    }
};
