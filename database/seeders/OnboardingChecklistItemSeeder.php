<?php

namespace Database\Seeders;

use App\Models\OnboardingChecklistItem;
use Illuminate\Database\Seeder;

class OnboardingChecklistItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // label, category, is_required, sort_order
            ['Background check / verifikasi latar belakang', 'dokumen',   true,  10],
            ['Perjanjian kerja ditandatangani',             'dokumen',   true,  20],
            ['Nomor Induk Karyawan (NIP) diterbitkan',       'akun',      true,  30],
            ['Email & akun sistem dibuat',                   'akun',      true,  40],
            ['Kartu akses / kartu absensi',                  'aset',      true,  50],
            ['Laptop / perangkat kerja',                     'aset',      true,  60],
            ['Meja kerja / workstation',                     'aset',      true,  70],
            ['Seragam / APD',                                'aset',      false, 80],
            ['Induction / orientasi perusahaan',             'induction', true,  90],
            ['Pengenalan tim & atasan langsung',             'induction', false, 100],
            ['Serah terima SOP & uraian jabatan',            'induction', false, 110],
        ];

        foreach ($items as [$label, $category, $required, $sort]) {
            OnboardingChecklistItem::firstOrCreate(
                ['company_id' => null, 'label' => $label],
                ['category' => $category, 'is_required' => $required, 'sort_order' => $sort, 'is_active' => true],
            );
        }
    }
}
