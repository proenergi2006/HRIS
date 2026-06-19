<?php

namespace App\Http\Controllers\GA;

use App\Http\Controllers\Controller;
use App\Models\GA\Vehicle;
use App\Models\GA\VehicleUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GaUsageController extends Controller
{
    public function index(Request $request)
    {
        $query = VehicleUsage::with('vehicle')->latest('check_in_at');

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $usages   = $query->get();
        $vehicles = Vehicle::orderBy('name')->get();
        return view('ga.admin.usages.index', compact('usages', 'vehicles'));
    }

    public function show(VehicleUsage $usage)
    {
        $usage->load('vehicle');
        return view('ga.admin.usages.show', compact('usage'));
    }

    public function photo(VehicleUsage $usage, string $side)
    {
        $allowed = ['dashboard', 'right', 'left', 'front', 'back'];
        abort_unless(in_array($side, $allowed), 404);

        $field = 'photo_' . $side;
        $path  = $usage->$field;

        abort_unless($path && Storage::disk('local')->exists($path), 404);
        return Storage::disk('local')->response($path);
    }
}
