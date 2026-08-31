<?php

namespace Database\Seeders;

use App\Models\Master\City;
use App\Models\Master\Province;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $data = require database_path('data/regions.php');

        $provinceIdByCode = [];

        foreach ($data['provinces'] as [$code, $name]) {
            $province = Province::firstOrCreate(
                ['code' => $code],
                ['name' => $name, 'is_active' => true],
            );
            $provinceIdByCode[$code] = $province->id;
        }

        foreach ($data['cities'] as [$provinceCode, $cityCode, $name, $type]) {
            $provinceId = $provinceIdByCode[$provinceCode] ?? null;
            if (! $provinceId) {
                continue;
            }

            // firstOrCreate pada `code` — baris dengan kode duplikat di file data
            // (kalau ada) otomatis dilewati tanpa error.
            City::firstOrCreate(
                ['code' => $cityCode],
                [
                    'province_id' => $provinceId,
                    'name'        => $name,
                    'type'        => $type,
                    'is_active'   => true,
                ],
            );
        }

        $this->command?->info('Region seeded: ' . Province::count() . ' provinsi, ' . City::count() . ' kota/kabupaten.');
    }
}
