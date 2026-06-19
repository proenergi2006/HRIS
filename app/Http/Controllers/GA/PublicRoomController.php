<?php

namespace App\Http\Controllers\GA;

use App\Http\Controllers\Controller;
use App\Models\GA\MeetingRoom;
use App\Models\GA\RoomCleaningLog;
use App\Models\GA\RoomCleaningLogDetail;
use App\Models\GA\RoomCleaningPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicRoomController extends Controller
{
    public function scan(MeetingRoom $room)
    {
        if (! $room->is_active) {
            abort(404);
        }

        $items = $room->cleaningItems()->orderBy('order')->get();

        return view('ga.public.room_scan', compact('room', 'items'));
    }

    public function submit(Request $request, MeetingRoom $room)
    {
        if (! $room->is_active) {
            abort(404);
        }

        $request->validate([
            'cleaner_name'       => 'required|string|max:100',
            'items'              => 'required|array',
            'items.*.notes'      => 'nullable|string|max:1000',
            'items.*.photos'     => 'nullable|array',
            'items.*.photos.*'   => 'image|max:5120',
        ]);

        $log = RoomCleaningLog::create([
            'room_id'      => $room->id,
            'cleaner_name' => $request->cleaner_name,
            'cleaned_at'   => now(),
        ]);

        $items = $room->cleaningItems()->orderBy('order')->get();

        foreach ($items as $item) {
            $itemData = $request->input("items.{$item->id}", []);
            $notes    = $itemData['notes'] ?? null;

            $detail = RoomCleaningLogDetail::create([
                'log_id'  => $log->id,
                'item_id' => $item->id,
                'notes'   => $notes,
            ]);

            $photos = $request->file("items.{$item->id}.photos") ?? [];
            foreach ($photos as $photo) {
                $path = $photo->store("ga/rooms/{$room->id}", 'local');
                RoomCleaningPhoto::create([
                    'detail_id' => $detail->id,
                    'path'      => $path,
                ]);
            }
        }

        return redirect()->route('ga.room.success', $room)
            ->with('success', 'Laporan kebersihan berhasil disimpan. Terima kasih!');
    }

    public function success(MeetingRoom $room)
    {
        return view('ga.public.room_success', compact('room'));
    }
}
