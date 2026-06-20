<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('role:admin'),
        ];
    }

    public function index(Request $request)
    {
        $query = Activity::with('causer')
            ->latest();

        if ($log = $request->log_name) {
            $query->where('log_name', $log);
        }

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('properties', 'like', "%{$search}%")
                  ->orWhereHas('causer', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if ($date = $request->date) {
            $query->whereDate('created_at', $date);
        }

        $logs = $query->paginate(30)->withQueryString();

        return view('admin.activity-log.index', compact('logs'));
    }
}
