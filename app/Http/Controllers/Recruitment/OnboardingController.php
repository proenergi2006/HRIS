<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeFacility;
use App\Models\EmployeeOnboardingTask;
use App\Models\OnboardingChecklistItem;
use App\Notifications\GenericNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Onboarding (PRD Bab 3 modul #5) — template checklist per company + progress per
 * karyawan baru. Item kategori "aset" selesai -> baris di tab Fasilitas karyawan.
 * Item induction bisa dilampiri materi (file/link) + ditandai "perlu konfirmasi
 * karyawan" — diselesaikan lewat halaman "Onboarding Saya" (ESS), bukan HR.
 */
class OnboardingController extends Controller
{
    public function templates()
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $items     = OnboardingChecklistItem::with('company')->orderBy('category')->orderBy('sort_order')->get();

        return view('recruitment.onboarding.templates', compact('items', 'companies'));
    }

    public function storeTemplate(Request $request)
    {
        OnboardingChecklistItem::create($this->validated($request));

        return back()->with('status', 'Item checklist ditambahkan.');
    }

    public function updateTemplate(Request $request, OnboardingChecklistItem $item)
    {
        $item->update($this->validated($request, $item));

        return back()->with('status', 'Item checklist diperbarui.');
    }

    public function destroyTemplate(OnboardingChecklistItem $item)
    {
        if ($item->material_path) {
            Storage::disk('public')->delete($item->material_path);
        }
        $item->delete();

        return back()->with('status', 'Item checklist dihapus.');
    }

    /** Baca materi induction langsung di sistem — inline (PDF/video tampil di browser, bukan diunduh). */
    public function viewMaterial(OnboardingChecklistItem $item)
    {
        abort_unless($item->material_path && Storage::disk('public')->exists($item->material_path), 404);

        return Storage::disk('public')->response(
            $item->material_path,
            $item->material_original_name ?: basename($item->material_path)
        );
    }

    /** Unduh materi induction sebagai file (attachment). */
    public function downloadMaterial(OnboardingChecklistItem $item)
    {
        abort_unless($item->material_path && Storage::disk('public')->exists($item->material_path), 404);

        return Storage::disk('public')->download($item->material_path, $item->material_original_name ?: basename($item->material_path));
    }

    /** Daftar karyawan yang punya task onboarding (progress belum/sudah lengkap). */
    public function index()
    {
        $employees = Employee::whereHas('onboardingTasks')
            ->withCount(['onboardingTasks', 'onboardingTasks as done_tasks_count' => fn ($q) => $q->where('is_done', true)])
            ->orderByDesc('id')->get();

        $pending = Employee::where('is_active', true)
            ->whereDoesntHave('onboardingTasks')
            ->where(fn ($q) => $q->where('employment_status', 'probation')
                ->orWhere('start_date', '>=', now()->subDays(90)->toDateString()))
            ->with(['department', 'position'])
            ->orderByDesc('start_date')
            ->get();

        return view('recruitment.onboarding.index', compact('employees', 'pending'));
    }

    public function show(Employee $employee)
    {
        $tasks = $employee->onboardingTasks()->with(['item', 'doneBy'])->get()->sortBy('item.sort_order');

        return view('recruitment.onboarding.show', compact('employee', 'tasks'));
    }

    public function start(Employee $employee)
    {
        self::materialize($employee);

        if ($employee->onboardingTasks()->count() === 0) {
            return redirect()->route('recruitment.onboarding.templates')
                ->with('warning', 'Belum ada item checklist onboarding yang aktif — buat dulu di Template Checklist.');
        }

        return redirect()->route('recruitment.onboarding.show', $employee)
            ->with('status', 'Onboarding untuk ' . $employee->name . ' dimulai.');
    }

    /** Materialisasi task onboarding dari template aktif (company_id null = global) untuk 1 karyawan. */
    public static function materialize(Employee $employee): void
    {
        $items = OnboardingChecklistItem::where('is_active', true)
            ->where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $employee->company_id))
            ->get();

        $created = 0;
        foreach ($items as $item) {
            $task = EmployeeOnboardingTask::firstOrCreate([
                'employee_id'                  => $employee->id,
                'onboarding_checklist_item_id' => $item->id,
            ]);
            if ($task->wasRecentlyCreated) {
                $created++;
            }
        }

        if ($created > 0) {
            $employee->loadMissing('user');
            $employee->user?->notify(new GenericNotification(
                'Onboarding Anda Dimulai',
                $created . ' tugas onboarding menunggu Anda selesaikan.',
                route('onboarding.mine'),
                'gd-book'
            ));
        }
    }

    public function toggleTask(Request $request, Employee $employee, EmployeeOnboardingTask $task)
    {
        abort_if($task->employee_id !== $employee->id, 404);

        $isDone = ! $task->is_done;
        $task->update([
            'is_done'         => $isDone,
            'done_at'         => $isDone ? now() : null,
            'done_by_user_id' => $isDone ? auth()->id() : null,
        ]);

        if ($isDone && $task->item->category === 'aset') {
            EmployeeFacility::firstOrCreate(
                ['employee_id' => $employee->id, 'name' => $task->item->label],
                ['received_date' => now()->toDateString(), 'remarks' => 'Onboarding checklist']
            );
        }

        return back()->with('status', $isDone ? 'Task ditandai selesai.' : 'Task dibatalkan.');
    }

    // ── Sisi karyawan (ESS) — "Onboarding Saya" ─────────────────────────────

    public function mine()
    {
        $employee = auth()->user()?->employee;
        abort_unless($employee, 404, 'Akun Anda belum terhubung ke data karyawan.');

        $tasks = $employee->onboardingTasks()->with('item')->get()->sortBy('item.sort_order');

        return view('onboarding.my', compact('employee', 'tasks'));
    }

    /** Karyawan menyatakan sudah membaca & memahami materi induction. */
    public function acknowledge(Request $request, EmployeeOnboardingTask $task)
    {
        $employee = auth()->user()?->employee;
        abort_unless($employee && $task->employee_id === $employee->id, 403);
        abort_unless($task->item->requires_acknowledgement, 422, 'Item ini tidak butuh konfirmasi.');

        $request->validate(['agree' => 'accepted']);

        $task->update([
            'is_done'              => true,
            'done_at'              => now(),
            'done_by_user_id'      => auth()->id(),
            'acknowledged_at'      => now(),
            'acknowledgement_note' => $request->string('note')->toString() ?: null,
        ]);

        return back()->with('success', 'Terima kasih — "' . $task->item->label . '" ditandai sudah Anda pahami.');
    }

    private function validated(Request $request, ?OnboardingChecklistItem $item = null): array
    {
        $data = $request->validate([
            'company_id'               => 'nullable|exists:companies,id',
            'label'                    => 'required|string|max:200',
            'description'              => 'nullable|string|max:5000',
            'material_url'             => 'nullable|url|max:500',
            'material'                 => 'nullable|file|mimes:pdf,ppt,pptx,doc,docx,mp4|max:51200',
            'category'                 => 'required|in:dokumen,akun,aset,induction',
            'is_required'              => 'boolean',
            'requires_acknowledgement' => 'boolean',
            'sort_order'               => 'nullable|integer|min:0',
        ]);

        $data['is_required']              = $request->boolean('is_required', true);
        $data['requires_acknowledgement'] = $request->boolean('requires_acknowledgement');

        if ($request->hasFile('material')) {
            if ($item?->material_path) {
                Storage::disk('public')->delete($item->material_path);
            }
            $file = $request->file('material');
            $data['material_path']          = $file->store('onboarding-materials', 'public');
            $data['material_original_name'] = $file->getClientOriginalName();
        }

        unset($data['material']);

        return $data;
    }
}
