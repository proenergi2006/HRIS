<?php

namespace Database\Seeders;

use App\Models\Master\City;
use App\Models\Master\District;
use App\Models\Master\Village;
use Illuminate\Database\Seeder;

/**
 * Starter Kecamatan & Kelurahan untuk kota-kota besar. Jalankan SETELAH
 * RegionSeeder. Dataset penuh Indonesia di-import terpisah (wilayah.id/Kemendagri).
 */
class RegionDistrictSeeder extends Seeder
{
    public function run(): void
    {
        $data = require database_path('data/regions-districts.php');

        $districtIdByKey = []; // "<city_code>::<district name>" => id
        $dSeq = [];

        foreach ($data['districts'] as $cityCode => $names) {
            $city = City::where('code', $cityCode)->first();
            if (! $city) {
                continue;
            }

            $seq = 1;
            foreach ($names as $name) {
                $district = District::firstOrCreate(
                    ['city_id' => $city->id, 'name' => $name],
                    ['code' => $cityCode . str_pad((string) $seq, 3, '0', STR_PAD_LEFT), 'is_active' => true],
                );
                $districtIdByKey["{$cityCode}::{$name}"] = $district->id;
                $dSeq[$district->id] = 1;
                $seq++;
            }
        }

        foreach ($data['villages'] ?? [] as $key => $names) {
            $districtId = $districtIdByKey[$key] ?? null;
            if (! $districtId) {
                continue;
            }

            $district = District::find($districtId);
            $seq = 1;
            foreach ($names as $name) {
                Village::firstOrCreate(
                    ['district_id' => $districtId, 'name' => $name],
                    [
                        'code'      => $district->code . str_pad((string) $seq, 4, '0', STR_PAD_LEFT),
                        'type'      => 'kelurahan',
                        'is_active' => true,
                    ],
                );
                $seq++;
            }
        }

        $this->command?->info('Region detail seeded: ' . District::count() . ' kecamatan, ' . Village::count() . ' kelurahan/desa (starter set).');
    }
}
