<?php

namespace Database\Seeders;

use App\Models\Master\City;
use App\Models\Master\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Dataset penuh wilayah Indonesia (Kepmendagri 300.2.2-2138/2025, sumber
 * github.com/cahyadsn/wilayah, MIT): 514 kab/kota, ±7.285 kecamatan,
 * ±83.762 kelurahan/desa. Data ada di database/data/wilayah-kemendagri.csv
 * (kolom: kode bertitik, nama).
 *
 * Aman dijalankan berulang & di atas data yang sudah di-input manual:
 *  - Kota dicocokkan lewat provinsi + jenis + nama (BUKAN kode — kode Jakarta di
 *    master lama beda dengan Kemendagri). Kab/kota yang belum ada ditambah.
 *  - Kecamatan/kelurahan dicocokkan lewat nama per induk (spasi/tanda baca
 *    diabaikan: "Medan Satria" = "Medansatria"); yang sudah ada tidak diubah.
 *  - Kode baris baru mengikuti pola app: kota(4) + 3 digit, kecamatan + 4 digit,
 *    dilanjutkan dari nomor terbesar yang sudah terpakai.
 * Terakhir FK alamat karyawan di-backfill dari kolom teks lama.
 */
class WilayahKemendagriSeeder extends Seeder
{
    public function run(): void
    {
        [$officialCities, $officialDistricts, $officialVillages] = $this->loadCsv();

        $provinceIdByCode = Province::pluck('id', 'code')->all();
        $now = now();
        $stats = ['city' => 0, 'district' => 0, 'village' => 0];
        $skipped = $reassigned = [];

        foreach ($officialCities as $kode => $officialName) {
            $provinceId = $provinceIdByCode[substr($kode, 0, 2)] ?? null;
            if (! $provinceId) {
                $skipped[] = "{$kode} {$officialName} (provinsi tidak ada)";
                continue;
            }

            $type = str_starts_with($officialName, 'Kota') ? 'kota' : 'kabupaten';
            $name = preg_replace('/^(Kabupaten Administrasi|Kabupaten|Kota Administrasi|Kota)\s+/i', '', $officialName);
            $name = trim($name);

            $city = City::where('province_id', $provinceId)
                ->where('type', $type)
                ->get()
                ->first(fn (City $c) => $this->norm($c->name) === $this->norm($name));

            if (! $city) {
                $code = str_replace('.', '', $kode);
                if (City::where('code', $code)->exists()) {
                    // Master lama punya kode kab yang tidak sama dengan Kemendagri
                    // (mis. 1308 = Agam, resminya Pasaman). Jangan utak-atik data
                    // lama: pakai kode cadangan 80–99 (tidak dipakai kode resmi).
                    $fallback = $this->fallbackCityCode(substr($kode, 0, 2));
                    if (! $fallback) {
                        $skipped[] = "{$kode} {$officialName} (kode {$code} dipakai kota lain, kode cadangan habis)";
                        continue;
                    }
                    $reassigned[] = "{$kode} {$officialName} => kode {$fallback}";
                    $code = $fallback;
                }
                $city = City::create([
                    'province_id' => $provinceId,
                    'code'        => $code,
                    'name'        => $name,
                    'type'        => $type,
                    'is_active'   => true,
                ]);
                $stats['city']++;
            }

            DB::transaction(function () use ($city, $kode, $officialDistricts, $officialVillages, $now, &$stats) {
                $this->seedCity($city, $officialDistricts[$kode] ?? [], $officialVillages, $now, $stats);
            });
        }

        $this->backfillEmployees();

        $this->command?->info(sprintf(
            'Wilayah Kemendagri: +%d kab/kota, +%d kecamatan, +%d kelurahan/desa (total sekarang %d kecamatan, %d kelurahan/desa).',
            $stats['city'], $stats['district'], $stats['village'],
            DB::table('districts')->count(), DB::table('villages')->count(),
        ));
        foreach ($reassigned as $line) {
            $this->command?->warn("Kode cadangan: {$line}");
        }
        foreach ($skipped as $line) {
            $this->command?->warn("Dilewati: {$line}");
        }
    }

    private function fallbackCityCode(string $provinceCode): ?string
    {
        for ($n = 80; $n <= 99; $n++) {
            $code = $provinceCode . $n;
            if (! City::where('code', $code)->exists()) {
                return $code;
            }
        }

        return null;
    }

    /** @return array{0: array<string,string>, 1: array<string,array<string,string>>, 2: array<string,array<string,string>>} */
    private function loadCsv(): array
    {
        $cities = $districts = $villages = [];

        $fh = fopen(database_path('data/wilayah-kemendagri.csv'), 'r');
        while (($row = fgetcsv($fh, 0, ',', '"', '')) !== false) {
            [$kode, $nama] = $row;
            match (strlen($kode)) {
                5       => $cities[$kode] = $nama,
                8       => $districts[substr($kode, 0, 5)][$kode] = $nama,
                13      => $villages[substr($kode, 0, 8)][$kode] = $nama,
                default => null,
            };
        }
        fclose($fh);

        return [$cities, $districts, $villages];
    }

    private function seedCity(City $city, array $districts, array $villagesByDistrict, $now, array &$stats): void
    {
        // Kecamatan yang sudah ada: nama-ternormalisasi => [id, ...] + nomor kode terbesar.
        $existing = DB::table('districts')->where('city_id', $city->id)->get(['id', 'name', 'code']);
        $pool = [];
        $maxSeq = 0;
        foreach ($existing as $d) {
            $pool[$this->norm($d->name)][] = $d->id;
            $maxSeq = max($maxSeq, (int) substr($d->code, -3));
        }

        $districtIdByKode = [];
        $newDistricts = [];
        foreach ($districts as $kode => $name) {
            $key = $this->norm($name);
            if (! empty($pool[$key])) {
                $districtIdByKode[$kode] = array_shift($pool[$key]);
                continue;
            }
            $code = $city->code . str_pad((string) ++$maxSeq, 3, '0', STR_PAD_LEFT);
            $newDistricts[$kode] = [
                'city_id'    => $city->id,
                'code'       => $code,
                'name'       => $name,
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($newDistricts) {
            DB::table('districts')->insert(array_values($newDistricts));
            $idByCode = DB::table('districts')
                ->whereIn('code', array_column($newDistricts, 'code'))
                ->pluck('id', 'code');
            foreach ($newDistricts as $kode => $row) {
                $districtIdByKode[$kode] = $idByCode[$row['code']];
            }
            $stats['district'] += count($newDistricts);
        }

        $districtCodeById = DB::table('districts')->where('city_id', $city->id)->pluck('code', 'id');

        // Kelurahan/desa yang sudah ada, per kecamatan.
        $existingVillages = DB::table('villages')
            ->whereIn('district_id', array_values($districtIdByKode))
            ->get(['district_id', 'name', 'code'])
            ->groupBy('district_id');

        $newVillages = [];
        foreach ($districtIdByKode as $dKode => $districtId) {
            $pool = [];
            $maxSeq = 0;
            foreach ($existingVillages[$districtId] ?? [] as $v) {
                $pool[$this->norm($v->name)] = ($pool[$this->norm($v->name)] ?? 0) + 1;
                $maxSeq = max($maxSeq, (int) substr($v->code, -4));
            }

            foreach ($villagesByDistrict[$dKode] ?? [] as $vKode => $name) {
                $key = $this->norm($name);
                if (($pool[$key] ?? 0) > 0) {
                    $pool[$key]--;
                    continue;
                }
                $newVillages[] = [
                    'district_id' => $districtId,
                    'code'        => $districtCodeById[$districtId] . str_pad((string) ++$maxSeq, 4, '0', STR_PAD_LEFT),
                    'name'        => $name,
                    // Kemendagri: nomor urut kelurahan 1xxx, desa 2xxx.
                    'type'        => substr($vKode, -4, 1) === '2' ? 'desa' : 'kelurahan',
                    'is_active'   => true,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }
        }

        foreach (array_chunk($newVillages, 500) as $chunk) {
            DB::table('villages')->insert($chunk);
        }
        $stats['village'] += count($newVillages);
    }

    /**
     * Isi FK kecamatan/kelurahan karyawan dari teks lama — dibatasi pada kota
     * (dan kecamatan) yang sama supaya "Cipayung" Jakarta Timur tidak tertukar
     * dengan "Cipayung" Depok.
     */
    private function backfillEmployees(): void
    {
        foreach (['domicile', 'ktp'] as $p) {
            DB::statement("
                UPDATE employees e
                JOIN districts d ON d.city_id = e.{$p}_city_id AND LOWER(TRIM(e.{$p}_district)) = LOWER(d.name)
                SET e.{$p}_district_id = d.id
                WHERE e.{$p}_district_id IS NULL AND e.{$p}_district <> ''
            ");
            DB::statement("
                UPDATE employees e
                JOIN villages v ON v.district_id = e.{$p}_district_id AND LOWER(TRIM(e.{$p}_subdistrict)) = LOWER(v.name)
                SET e.{$p}_village_id = v.id
                WHERE e.{$p}_village_id IS NULL AND e.{$p}_subdistrict <> ''
            ");
        }
    }

    private function norm(string $name): string
    {
        return preg_replace('/[^a-z0-9]/', '', mb_strtolower($name));
    }
}
