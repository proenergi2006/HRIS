<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Bagan Organisasi mengurutkan tier pakai levels.rank ASC (1 = paling senior,
     * lihat placeholder di form Edit Level). Data awal cuma isi rank berbeda utk
     * Senior Staff(4)/Staff(5) — Direksi/Manager/SPV/Admin semua dibiarkan default
     * 99 (seri), jadi org chart tidak bisa membedakan CEO (Direksi) dari Manager/SPV.
     * Diperbaiki jadi hierarki wajar: Direksi(1) > Manager(2) > SPV(3) >
     * Senior Staff(4, tidak berubah) > Staff(5, tidak berubah) > Admin(6).
     */
    public function up(): void
    {
        $map = ['Direksi' => 1, 'Manager' => 2, 'SPV' => 3, 'Senior Staff' => 4, 'Staff' => 5, 'Admin' => 6];

        foreach ($map as $name => $rank) {
            DB::table('levels')->where('name', $name)->update(['rank' => $rank]);
        }
    }

    public function down(): void
    {
        DB::table('levels')->whereIn('name', ['Direksi', 'Manager', 'SPV', 'Admin'])->update(['rank' => 99]);
    }
};
