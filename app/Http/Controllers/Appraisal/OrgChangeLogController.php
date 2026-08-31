<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\OrgChangeLog;
use Illuminate\Http\Request;

class OrgChangeLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = OrgChangeLog::with('changedBy')
            ->when($request->filled('unit_type'), fn ($q) => $q->where('unit_type', $request->unit_type))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('effective_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('effective_date', '<=', $request->to))
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        return view('appraisal.org-log.index', compact('logs'));
    }
}
