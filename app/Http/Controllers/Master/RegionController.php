<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\City;
use App\Models\Master\District;
use App\Models\Master\Province;
use App\Models\Master\Village;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index(Request $request)
    {
        $provinces = Province::orderBy('name')->get();

        $selectedProvince = $request->filled('province')
            ? $provinces->firstWhere('id', (int) $request->province)
            : $provinces->first();

        $search = trim((string) $request->get('q', ''));

        $cities = City::query()
            ->when($selectedProvince, fn ($q) => $q->where('province_id', $selectedProvince->id))
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();

        $selectedCity = $request->filled('city')
            ? $cities->firstWhere('id', (int) $request->city)
            : null;

        $districts = $selectedCity
            ? District::where('city_id', $selectedCity->id)->orderBy('name')->get()
            : collect();

        $selectedDistrict = $request->filled('district')
            ? $districts->firstWhere('id', (int) $request->district)
            : null;

        $villages = $selectedDistrict
            ? Village::where('district_id', $selectedDistrict->id)->orderBy('name')->get()
            : collect();

        return view('master.region.index', compact(
            'provinces', 'selectedProvince', 'cities', 'search',
            'selectedCity', 'districts', 'selectedDistrict', 'villages'
        ));
    }

    // ── Kota / Kabupaten ───────────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            'province_id' => 'required|exists:provinces,id',
            'name'        => 'required|string|max:100',
            'type'        => 'required|in:kota,kabupaten',
        ]);

        $data['code']      = $this->nextCode('cities', Province::findOrFail($data['province_id'])->code, 2, 71);
        $data['is_active'] = true;

        City::create($data);

        return back()->with('status', 'Kota/Kabupaten berhasil ditambahkan.');
    }

    public function update(Request $request, City $city)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'type'      => 'required|in:kota,kabupaten',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', false);

        $city->update($data);

        return back()->with('status', 'Kota/Kabupaten berhasil diperbarui.');
    }

    public function destroy(City $city)
    {
        if (\App\Models\Employee::where('domicile_city_id', $city->id)->orWhere('ktp_city_id', $city->id)->exists()) {
            return back()->with('error', 'Kota/Kabupaten tidak bisa dihapus karena masih dipakai data karyawan.');
        }

        $city->delete();

        return back()->with('status', 'Kota/Kabupaten berhasil dihapus.');
    }

    // ── Kecamatan ──────────────────────────────────────────────────────────

    public function storeDistrict(Request $request)
    {
        $data = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name'    => 'required|string|max:100',
        ]);

        $data['code']      = $this->nextCode('districts', City::findOrFail($data['city_id'])->code, 3, 1);
        $data['is_active'] = true;

        District::create($data);

        return back()->with('status', 'Kecamatan berhasil ditambahkan.');
    }

    public function updateDistrict(Request $request, District $district)
    {
        $district->update($request->validate([
            'name'      => 'required|string|max:100',
            'is_active' => 'boolean',
        ]) + ['is_active' => $request->boolean('is_active', false)]);

        return back()->with('status', 'Kecamatan berhasil diperbarui.');
    }

    public function destroyDistrict(District $district)
    {
        if (\App\Models\Employee::where('domicile_district_id', $district->id)->orWhere('ktp_district_id', $district->id)->exists()) {
            return back()->with('error', 'Kecamatan tidak bisa dihapus karena masih dipakai data karyawan.');
        }

        $district->delete();

        return back()->with('status', 'Kecamatan berhasil dihapus.');
    }

    // ── Kelurahan / Desa ───────────────────────────────────────────────────

    public function storeVillage(Request $request)
    {
        $data = $request->validate([
            'district_id' => 'required|exists:districts,id',
            'name'        => 'required|string|max:100',
            'type'        => 'required|in:kelurahan,desa',
        ]);

        $data['code']      = $this->nextCode('villages', District::findOrFail($data['district_id'])->code, 4, 1);
        $data['is_active'] = true;

        Village::create($data);

        return back()->with('status', 'Kelurahan/Desa berhasil ditambahkan.');
    }

    public function updateVillage(Request $request, Village $village)
    {
        $village->update($request->validate([
            'name'      => 'required|string|max:100',
            'type'      => 'required|in:kelurahan,desa',
            'is_active' => 'boolean',
        ]) + ['is_active' => $request->boolean('is_active', false)]);

        return back()->with('status', 'Kelurahan/Desa berhasil diperbarui.');
    }

    public function destroyVillage(Village $village)
    {
        if (\App\Models\Employee::where('domicile_village_id', $village->id)->orWhere('ktp_village_id', $village->id)->exists()) {
            return back()->with('error', 'Kelurahan/Desa tidak bisa dihapus karena masih dipakai data karyawan.');
        }

        $village->delete();

        return back()->with('status', 'Kelurahan/Desa berhasil dihapus.');
    }

    // ── AJAX cascade (dipakai form Edit Karyawan) ──────────────────────────

    public function apiCities(Request $request)
    {
        return City::active()
            ->where('province_id', $request->integer('province_id'))
            ->orderBy('name')->get(['id', 'name']);
    }

    public function apiDistricts(Request $request)
    {
        return District::active()
            ->where('city_id', $request->integer('city_id'))
            ->orderBy('name')->get(['id', 'name']);
    }

    public function apiVillages(Request $request)
    {
        return Village::active()
            ->where('district_id', $request->integer('district_id'))
            ->orderBy('name')->get(['id', 'name', 'type']);
    }

    // ── Helper ─────────────────────────────────────────────────────────────

    /** Generate kode unik = prefix parent + urut N-digit, mulai dari $start. */
    private function nextCode(string $table, string $prefix, int $pad, int $start): string
    {
        $model = ['cities' => City::class, 'districts' => District::class, 'villages' => Village::class][$table];
        $seq   = $start;

        do {
            $code = $prefix . str_pad((string) $seq, $pad, '0', STR_PAD_LEFT);
            $seq++;
        } while ($model::where('code', $code)->exists());

        return $code;
    }
}
