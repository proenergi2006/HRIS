<?php

namespace Database\Seeders;

use App\Models\OffboardingChecklistItem;
use Illuminate\Database\Seeder;

/** Item checklist clearance resign default (berlaku semua perusahaan). */
class OffboardingChecklistItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['label' => 'Kembalikan laptop/aset kantor',        'category' => 'aset',     'sort_order' => 1],
            ['label' => 'Kembalikan ID card & akses gedung',    'category' => 'aset',     'sort_order' => 2],
            ['label' => 'Nonaktifkan email & akun sistem',      'category' => 'akun',     'sort_order' => 1],
            ['label' => 'Serah terima pekerjaan',                'category' => 'exit',     'sort_order' => 1],
            ['label' => 'Exit interview',                        'category' => 'exit',     'sort_order' => 2],
            ['label' => 'Pelunasan kasbon/pinjaman',            'category' => 'keuangan', 'sort_order' => 1],
            ['label' => 'Surat pengalaman kerja',                'category' => 'dokumen',  'sort_order' => 1],
        ];

        foreach ($items as $item) {
            OffboardingChecklistItem::firstOrCreate(
                ['company_id' => null, 'label' => $item['label']],
                ['category' => $item['category'], 'sort_order' => $item['sort_order'], 'is_required' => true, 'is_active' => true]
            );
        }
    }
}
