<?php

namespace App\Http\Controllers\GA;

use App\Http\Controllers\Controller;
use App\Models\GA\Vehicle;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GaVehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::withCount(['usages', 'usages as active_count' => fn($q) => $q->where('status', 'checked_in')])
            ->orderBy('name')->get();
        return view('ga.admin.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        return view('ga.admin.vehicles.create', ['vehicle' => new Vehicle]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'plate'     => 'required|string|max:20|unique:vehicles,plate',
            'type'      => 'nullable|string|max:50',
            'color'     => 'nullable|string|max:50',
            'year'      => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        Vehicle::create($data);
        return redirect()->route('ga.admin.vehicles.index')->with('status', 'Kendaraan berhasil ditambahkan.');
    }

    public function edit(Vehicle $vehicle)
    {
        return view('ga.admin.vehicles.create', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'plate'     => 'required|string|max:20|unique:vehicles,plate,' . $vehicle->id,
            'type'      => 'nullable|string|max:50',
            'color'     => 'nullable|string|max:50',
            'year'      => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $vehicle->update($data);
        return redirect()->route('ga.admin.vehicles.index')->with('status', 'Kendaraan berhasil diperbarui.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return redirect()->route('ga.admin.vehicles.index')->with('status', 'Kendaraan berhasil dihapus.');
    }

    public function qrcode(Vehicle $vehicle)
    {
        $url = route('ga.scan', $vehicle);
        $qr  = QrCode::format('svg')->size(300)->errorCorrection('H')->generate($url);
        return view('ga.admin.vehicles.qrcode', compact('vehicle', 'qr', 'url'));
    }
}
