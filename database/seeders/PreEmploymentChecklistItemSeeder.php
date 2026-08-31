<?php

namespace Database\Seeders;

use App\Models\PreEmploymentChecklistItem;
use Illuminate\Database\Seeder;

class PreEmploymentChecklistItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['Offer diterima & ditandatangani',        'verifikasi', true,  10],
            ['Data pribadi lengkap (TTL, alamat, dll)', 'data',       true,  20],
            ['KTP',                                     'dokumen',    true,  30],
            ['NPWP',                                    'dokumen',    true,  40],
            ['Kartu Keluarga',                          'dokumen',    true,  50],
            ['Rekening bank (bank, no. rek, a.n.)',     'data',       true,  60],
            ['Data BPJS Kesehatan',                     'data',       false, 70],
            ['Data BPJS Ketenagakerjaan',               'data',       false, 80],
            ['Ijazah & transkrip nilai',               'dokumen',    true,  90],
            ['SKCK',                                    'dokumen',    false, 100],
            ['Surat pengalaman kerja / paklaring',      'dokumen',    false, 110],
            ['Hasil Medical Check-Up (MCU)',            'dokumen',    true,  120],
            ['Pas foto',                                'dokumen',    true,  130],
        ];

        foreach ($items as [$label, $category, $required, $sort]) {
            PreEmploymentChecklistItem::firstOrCreate(
                ['company_id' => null, 'label' => $label],
                ['category' => $category, 'is_required' => $required, 'sort_order' => $sort, 'is_active' => true],
            );
        }
    }
}
