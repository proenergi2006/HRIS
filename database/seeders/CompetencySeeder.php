<?php

namespace Database\Seeders;

use App\Models\Competency\Competency;
use Illuminate\Database\Seeder;

/**
 * Kamus kompetensi default (Competency Framework). Company-agnostic
 * (company_id null = berlaku semua PT) — HR bisa menambah/menyesuaikan lewat UI.
 */
class CompetencySeeder extends Seeder
{
    public function run(): void
    {
        $competencies = [
            // Core / Behavioral
            ['code' => 'CORE-INT',  'name' => 'Integritas',                       'category' => 'Core'],
            ['code' => 'CORE-COM',  'name' => 'Komunikasi',                        'category' => 'Core'],
            ['code' => 'CORE-SVC',  'name' => 'Orientasi Pelayanan',               'category' => 'Core'],
            ['code' => 'CORE-TEAM', 'name' => 'Kerja Sama Tim',                    'category' => 'Core'],
            ['code' => 'CORE-ADP',  'name' => 'Adaptasi & Fleksibilitas',          'category' => 'Core'],

            // Managerial
            ['code' => 'MGR-LEAD',  'name' => 'Kepemimpinan',                      'category' => 'Managerial'],
            ['code' => 'MGR-DEC',   'name' => 'Pengambilan Keputusan',             'category' => 'Managerial'],
            ['code' => 'MGR-PLAN',  'name' => 'Perencanaan & Pengorganisasian',    'category' => 'Managerial'],
            ['code' => 'MGR-DEV',   'name' => 'Pengembangan Orang Lain',           'category' => 'Managerial'],

            // Technical
            ['code' => 'TECH-JOB',  'name' => 'Penguasaan Teknis Jabatan',         'category' => 'Technical'],
        ];

        foreach ($competencies as $c) {
            Competency::firstOrCreate(
                ['code' => $c['code']],
                [
                    'company_id'  => null,
                    'name'        => $c['name'],
                    'category'    => $c['category'],
                    'is_active'   => true,
                ]
            );
        }
    }
}
