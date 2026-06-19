<?php

namespace App\Http\Controllers\GA;

use App\Http\Controllers\Controller;
use App\Models\GA\MeetingRoom;
use App\Models\GA\RoomCleaningItem;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GaRoomController extends Controller
{
    public function index()
    {
        $rooms = MeetingRoom::withCount('cleaningLogs')->orderBy('name')->get();
        return view('ga.admin.rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('ga.admin.rooms.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'location'  => 'nullable|string|max:150',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        MeetingRoom::create($data);

        return redirect()->route('ga.admin.rooms.index')
            ->with('status', 'Ruangan berhasil ditambahkan.');
    }

    public function edit(MeetingRoom $room)
    {
        $items = $room->cleaningItems()->orderBy('order')->get();
        return view('ga.admin.rooms.edit', compact('room', 'items'));
    }

    public function update(Request $request, MeetingRoom $room)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'location'  => 'nullable|string|max:150',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $room->update($data);

        return redirect()->route('ga.admin.rooms.index')
            ->with('status', 'Ruangan berhasil diperbarui.');
    }

    public function destroy(MeetingRoom $room)
    {
        $room->delete();
        return redirect()->route('ga.admin.rooms.index')
            ->with('status', 'Ruangan berhasil dihapus.');
    }

    public function qrcode(MeetingRoom $room)
    {
        $url = route('ga.room.scan', $room);
        $qr  = QrCode::format('svg')->size(300)->generate($url);
        return view('ga.admin.rooms.qrcode', compact('room', 'qr', 'url'));
    }

    // ── Cleaning items (checklist per room) ─────────────────────────────

    public function storeItem(Request $request, MeetingRoom $room)
    {
        $request->validate(['name' => 'required|string|max:150']);

        $maxOrder = $room->cleaningItems()->max('order') ?? 0;
        RoomCleaningItem::create([
            'room_id' => $room->id,
            'name'    => $request->name,
            'order'   => $maxOrder + 1,
        ]);

        return back()->with('status', 'Item berhasil ditambahkan.');
    }

    public function updateItem(Request $request, MeetingRoom $room, RoomCleaningItem $item)
    {
        $request->validate(['name' => 'required|string|max:150']);
        $item->update(['name' => $request->name]);
        return back()->with('status', 'Item berhasil diperbarui.');
    }

    public function destroyItem(MeetingRoom $room, RoomCleaningItem $item)
    {
        $item->delete();
        return back()->with('status', 'Item berhasil dihapus.');
    }
}
