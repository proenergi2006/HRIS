<?php

namespace App\Http\Controllers\GA;

use App\Http\Controllers\Controller;
use App\Models\GA\MeetingRoom;
use App\Models\GA\RoomCleaningLog;
use App\Models\GA\RoomCleaningPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GaCleaningLogController extends Controller
{
    public function index(Request $request)
    {
        $rooms = MeetingRoom::orderBy('name')->get();

        $query = RoomCleaningLog::with('room')->latest('cleaned_at');

        if ($request->room_id) {
            $query->where('room_id', $request->room_id);
        }
        if ($request->date) {
            $query->whereDate('cleaned_at', $request->date);
        }

        $logs = $query->get();

        return view('ga.admin.cleaning_logs.index', compact('logs', 'rooms'));
    }

    public function show(RoomCleaningLog $log)
    {
        $log->load(['room', 'details.item', 'details.photos']);
        return view('ga.admin.cleaning_logs.show', compact('log'));
    }

    public function photo(RoomCleaningLog $log, RoomCleaningPhoto $photo)
    {
        // verify ownership
        abort_unless($photo->detail->log_id === $log->id, 404);

        return Storage::disk('local')->response($photo->path);
    }
}
