<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Pengumuman / broadcast / papan info (Fase 2 HRD). */
class AnnouncementController extends Controller
{
    // ── Semua karyawan yang login ───────────────────────────────────────────

    public function index(Request $request)
    {
        $announcements = Announcement::published()->visibleTo($request->user())
            ->orderByDesc('is_pinned')->orderByDesc('published_at')->get();

        return view('announcements.index', compact('announcements'));
    }

    public function show(Request $request, Announcement $announcement)
    {
        abort_unless($announcement->is_active, 404);

        \App\Models\AnnouncementRead::firstOrCreate(
            ['announcement_id' => $announcement->id, 'user_id' => $request->user()->id],
            ['read_at' => now()]
        );

        return view('announcements.show', compact('announcement'));
    }

    // ── HR (announcement.edit) ──────────────────────────────────────────────

    public function manage()
    {
        $announcements = Announcement::with('company', 'createdBy')->latest()->get();

        return view('announcements.manage.index', compact('announcements'));
    }

    public function create()
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('announcements.manage.form', ['companies' => $companies, 'announcement' => new Announcement()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by_user_id'] = auth()->id();
        $this->handleAttachment($request, $data);

        Announcement::create($data);

        return redirect()->route('announcements.manage')->with('status', 'Pengumuman berhasil dibuat.');
    }

    public function edit(Announcement $announcement)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('announcements.manage.form', compact('companies', 'announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $data = $this->validated($request);
        $this->handleAttachment($request, $data, $announcement);

        $announcement->update($data);

        return redirect()->route('announcements.manage')->with('status', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement)
    {
        if ($announcement->attachment_path) {
            Storage::disk('local')->delete($announcement->attachment_path);
        }
        $announcement->delete();

        return back()->with('status', 'Pengumuman dihapus.');
    }

    public function togglePublish(Announcement $announcement)
    {
        $announcement->update([
            'is_active'    => ! $announcement->is_active,
            'published_at' => $announcement->published_at ?? now(),
        ]);

        return back()->with('status', $announcement->is_active ? 'Pengumuman dipublikasikan.' : 'Pengumuman disembunyikan.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'company_id'   => 'nullable|exists:companies,id',
            'title'         => 'required|string|max:200',
            'body'          => 'required|string',
            'category'      => 'required|in:' . implode(',', array_keys(Announcement::$categoryLabels)),
            'is_pinned'     => 'boolean',
            'published_at'  => 'nullable|date',
            'expires_at'    => 'nullable|date',
            'attachment'    => 'nullable|file|max:8192',
        ]);
        $data['is_pinned']    = $request->boolean('is_pinned');
        $data['published_at'] = $data['published_at'] ?? now();
        $data['is_active']    = true;
        unset($data['attachment']);

        return $data;
    }

    private function handleAttachment(Request $request, array &$data, ?Announcement $existing = null): void
    {
        if (! $request->hasFile('attachment')) {
            return;
        }
        if ($existing?->attachment_path) {
            Storage::disk('local')->delete($existing->attachment_path);
        }
        $file = $request->file('attachment');
        $data['attachment_path'] = $file->store('announcements', 'local');
        $data['attachment_name'] = $file->getClientOriginalName();
    }

    public function attachment(Announcement $announcement)
    {
        abort_unless($announcement->attachment_path && Storage::disk('local')->exists($announcement->attachment_path), 404);

        return Storage::disk('local')->download($announcement->attachment_path, $announcement->attachment_name);
    }
}
