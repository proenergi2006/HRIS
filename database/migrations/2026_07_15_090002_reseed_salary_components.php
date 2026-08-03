<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // BPJS Kesehatan sudah ada dari seed awal — jadikan otomatis (1% dari Gaji Pokok + Tunjangan Jabatan)
        DB::table('salary_components')
            ->whereNull('company_id')->where('name', 'BPJS Kesehatan')
            ->update([
                'calculation_type' => 'percent_of_base',
                'rate_percent'     => 1.00,
                'updated_at'       => now(),
            ]);

        // Nonaktifkan komponen lama yang digantikan/duplikat dengan skema baru
        DB::table('salary_components')
            ->whereNull('company_id')
            ->whereIn('name', ['Tunjangan Transportasi', 'Tunjangan Makan', 'BPJS Ketenagakerjaan', 'Potongan Absensi'])
            ->update(['is_active' => false, 'updated_at' => now()]);

        $now = now();
        DB::table('salary_components')->insert([
            [
                'company_id' => null, 'name' => 'Tunjangan Operasional', 'type' => 'allowance',
                'calculation_type' => 'manual', 'rate_percent' => null, 'salary_cap' => null,
                'is_taxable' => false, 'is_active' => true, 'sort_order' => 5,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'company_id' => null, 'name' => 'Tunjangan Makan & Transport', 'type' => 'allowance',
                'calculation_type' => 'manual', 'rate_percent' => null, 'salary_cap' => null,
                'is_taxable' => false, 'is_active' => true, 'sort_order' => 6,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'company_id' => null, 'name' => 'Medical Claim', 'type' => 'allowance',
                'calculation_type' => 'medical_claim', 'rate_percent' => null, 'salary_cap' => null,
                'is_taxable' => false, 'is_active' => true, 'sort_order' => 7,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'company_id' => null, 'name' => 'Tunjangan PPh 21', 'type' => 'allowance',
                'calculation_type' => 'mirror_pph21', 'rate_percent' => null, 'salary_cap' => null,
                'is_taxable' => true, 'is_active' => true, 'sort_order' => 8,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'company_id' => null, 'name' => 'Jaminan Pensiun', 'type' => 'deduction',
                'calculation_type' => 'percent_of_base', 'rate_percent' => 1.00, 'salary_cap' => null,
                'is_taxable' => false, 'is_active' => true, 'sort_order' => 11,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'company_id' => null, 'name' => 'Potongan Keterlambatan', 'type' => 'deduction',
                'calculation_type' => 'late_deduction', 'rate_percent' => null, 'salary_cap' => null,
                'is_taxable' => false, 'is_active' => true, 'sort_order' => 12,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'company_id' => null, 'name' => 'Potongan PPh 21', 'type' => 'deduction',
                'calculation_type' => 'manual', 'rate_percent' => null, 'salary_cap' => null,
                'is_taxable' => false, 'is_active' => true, 'sort_order' => 13,
                'created_at' => $now, 'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('salary_components')->whereNull('company_id')->whereIn('name', [
            'Tunjangan Operasional', 'Tunjangan Makan & Transport', 'Medical Claim',
            'Tunjangan PPh 21', 'Jaminan Pensiun', 'Potongan Keterlambatan', 'Potongan PPh 21',
        ])->delete();

        DB::table('salary_components')
            ->whereNull('company_id')
            ->whereIn('name', ['Tunjangan Transportasi', 'Tunjangan Makan', 'BPJS Ketenagakerjaan', 'Potongan Absensi'])
            ->update(['is_active' => true, 'updated_at' => now()]);

        DB::table('salary_components')
            ->whereNull('company_id')->where('name', 'BPJS Kesehatan')
            ->update(['calculation_type' => 'manual', 'rate_percent' => null, 'updated_at' => now()]);
    }
};
