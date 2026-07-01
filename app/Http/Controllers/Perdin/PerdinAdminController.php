<?php

namespace App\Http\Controllers\Perdin;

use App\Http\Controllers\Controller;
use App\Models\Perdin\PerdinRequest;
use Illuminate\Http\Request;

class PerdinAdminController extends Controller
{
    /**
     * Admin/HR view of every perdin request.
     */
    public function requests(Request $request)
    {
        $status   = $request->get('status');
        $requests = PerdinRequest::with('user')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('departure_date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.perdin.requests', compact('requests', 'status'));
    }
}
