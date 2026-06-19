<?php

namespace App\Http\Controllers\GA;

use App\Http\Controllers\Controller;
use App\Models\GA\Vehicle;
use App\Models\GA\VehicleUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicVehicleController extends Controller
{
    public function scan(Vehicle $vehicle)
    {
        abort_unless($vehicle->is_active, 404);
        $activeUsage = $vehicle->usages()->where('status', 'checked_in')->latest()->first();
        return view('ga.public.scan', compact('vehicle', 'activeUsage'));
    }

    public function checkin(Request $request, Vehicle $vehicle)
    {
        abort_unless($vehicle->is_active && $vehicle->isAvailable(), 422);

        $data = $request->validate([
            'driver_name' => 'required|string|max:100',
            'driver_phone'=> 'nullable|string|max:30',
            'destination' => 'required|string|max:255',
        ]);

        VehicleUsage::create(array_merge($data, [
            'vehicle_id'  => $vehicle->id,
            'check_in_at' => now(),
            'status'      => 'checked_in',
        ]));

        return redirect()->route('ga.scan', $vehicle)
            ->with('checkin_success', 'Check In berhasil! Selamat perjalanan, ' . $data['driver_name'] . '.');
    }

    public function checkout(Request $request, Vehicle $vehicle)
    {
        $usage = $vehicle->usages()->where('status', 'checked_in')->latest()->firstOrFail();

        $data = $request->validate([
            'km_out'           => 'required|integer|min:0',
            'keluhan'          => 'nullable|string|max:2000',
            'photo_dashboard'  => 'required|image|max:8192',
            'photo_right'      => 'required|image|max:8192',
            'photo_left'       => 'required|image|max:8192',
            'photo_front'      => 'required|image|max:8192',
            'photo_back'       => 'required|image|max:8192',
        ]);

        $photos = [];
        foreach (['photo_dashboard', 'photo_right', 'photo_left', 'photo_front', 'photo_back'] as $field) {
            $photos[$field] = $request->file($field)->store('ga', 'local');
        }

        $usage->update(array_merge([
            'km_out'       => $data['km_out'],
            'keluhan'      => $data['keluhan'],
            'check_out_at' => now(),
            'status'       => 'checked_out',
        ], $photos));

        return redirect()->route('ga.scan', $vehicle)
            ->with('checkout_success', 'Check Out berhasil! Terima kasih, ' . $usage->driver_name . '.');
    }
}
