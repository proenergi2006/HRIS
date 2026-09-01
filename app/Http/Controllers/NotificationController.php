<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/** Notification Center — riwayat notifikasi in-app (bel header), semua user login. */
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /** Klik notifikasi dari dropdown bel — tandai dibaca lalu lempar ke URL tujuannya. */
    public function read(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();
        abort_unless($notification, 404);

        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('notifications.index'));
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
