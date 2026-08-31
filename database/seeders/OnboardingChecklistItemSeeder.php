<?php

namespace Database\Seeders;

use App\Models\OnboardingChecklistItem;
use Illuminate\Database\Seeder;

class OnboardingChecklistItemSeeder extends Seeder
{
    public function run(): void
    {
        // Operasional — diceklis HR. [label, category, required, sort]
        $ops = [
            ['Background check / verifikasi latar belakang', 'dokumen',   true,  10],
            ['Perjanjian kerja ditandatangani',             'dokumen',   true,  20],
            ['Nomor Induk Karyawan (NIP) diterbitkan',       'akun',      true,  30],
            ['Email & akun sistem dibuat',                   'akun',      true,  40],
            ['Kartu akses / kartu absensi',                  'aset',      true,  50],
            ['Laptop / perangkat kerja',                     'aset',      true,  60],
            ['Meja kerja / workstation',                     'aset',      true,  70],
            ['Seragam / APD',                                'aset',      false, 80],
        ];

        foreach ($ops as [$label, $category, $required, $sort]) {
            OnboardingChecklistItem::firstOrCreate(
                ['company_id' => null, 'label' => $label],
                ['category' => $category, 'is_required' => $required, 'sort_order' => $sort, 'is_active' => true],
            );
        }

        // Kurikulum Induction — dibaca & dikonfirmasi KARYAWAN. [label, desc, required, sort]
        $induction = [
            ['Welcome', 'Sambutan manajemen & gambaran umum hari pertama.', true, 100],
            ['Company Introduction / Orientation', 'Sejarah, visi-misi, nilai perusahaan, lini bisnis, dan lokasi kerja.', true, 110],
            ['Organization', 'Struktur organisasi, jenjang jabatan, dan alur pelaporan.', true, 120],
            ['HR Procedure', 'Kehadiran, cuti, lembur, penilaian kinerja, tata tertib, dan sanksi.', true, 130],
            ['Fakta Integritas', 'Pernyataan integritas, benturan kepentingan, anti-suap & gratifikasi. Wajib dibaca dan disetujui.', true, 140],
            ['HR Operation & Incentive', 'Penggajian, komponen upah, THR/bonus, insentif, BPJS, dan reimbursement.', true, 150],
            ['GA Procedure', 'Fasilitas kantor, aset, kendaraan, perjalanan dinas, kebersihan & keamanan.', true, 160],
            ['Vopak Procedure', 'Prosedur operasi & HSSE terminal Vopak yang berlaku di area kerja.', true, 170],
            ['Logistic & Operational Procedure', 'Alur logistik, penerimaan/pengiriman, dan SOP operasional lapangan.', false, 180],
            ['Sales Administration & Finance Procedure', 'Administrasi penjualan, invoicing, penagihan, dan pelaporan keuangan.', false, 190],
            ['Legal & Collection Procedure', 'Kontrak, kepatuhan hukum, dan prosedur penagihan piutang.', false, 200],
            ['Business Overview (Commercial)', 'Peta pasar, pelanggan utama, dan strategi komersial.', false, 210],
            ['Selling Skill & Product Knowledge', 'Pengetahuan produk dan keterampilan penjualan dasar.', false, 220],
            ['Procurement Procedure', 'Permintaan pembelian, vendor, dan proses pengadaan.', false, 230],
        ];

        foreach ($induction as [$label, $desc, $required, $sort]) {
            OnboardingChecklistItem::firstOrCreate(
                ['company_id' => null, 'label' => $label],
                [
                    'description'              => $desc,
                    'category'                => 'induction',
                    'is_required'             => $required,
                    'requires_acknowledgement' => true,
                    'sort_order'              => $sort,
                    'is_active'               => true,
                ],
            );
        }
    }
}
