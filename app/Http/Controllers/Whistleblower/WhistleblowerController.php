<?php

namespace App\Http\Controllers\Whistleblower;

use App\Http\Controllers\Controller;
use App\Models\WhistleblowerReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class WhistleblowerController extends Controller
{
    public function index(Request $request)
    {
        $query = WhistleblowerReport::latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('branch')) {
            $query->where('branch_location', $request->branch);
        }

        $reports    = $query->get();
        $statuses   = WhistleblowerReport::$statuses;
        $categories = WhistleblowerReport::$categories;

        return view('whistleblower.admin.index', compact('reports', 'statuses', 'categories'));
    }

    public function show(WhistleblowerReport $report)
    {
        return view('whistleblower.admin.show', compact('report'));
    }

    public function updateStatus(Request $request, WhistleblowerReport $report)
    {
        $validated = $request->validate([
            'status'      => 'required|in:new,in_review,resolved,closed',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $report->update([
            'status'      => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? $report->admin_notes,
            'reviewed_at' => $report->reviewed_at ?? now(),
        ]);

        return back()->with('status', 'Status laporan berhasil diperbarui.');
    }

    public function download(WhistleblowerReport $report)
    {
        if (! $report->attachment_path) {
            return back()->with('error', 'Laporan ini tidak memiliki lampiran.');
        }

        if (! Storage::disk('local')->exists($report->attachment_path)) {
            return back()->with('error', 'File lampiran tidak ditemukan di server. Hubungi administrator.');
        }

        return Storage::disk('local')->download(
            $report->attachment_path,
            $report->attachment_original_name ?? 'lampiran'
        );
    }

    public function qrcode()
    {
        $url = route('whistleblower.form');
        $qr  = QrCode::format('svg')->size(300)->errorCorrection('H')->generate($url);
        return view('whistleblower.admin.qrcode', compact('qr', 'url'));
    }
}
