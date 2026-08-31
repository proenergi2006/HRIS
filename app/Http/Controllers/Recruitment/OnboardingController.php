<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeFacility;
use App\Models\EmployeeOnboardingTask;
use App\Models\OnboardingChecklistItem;
use Illuminate\Http\Request;

/**
 * Onboarding (PRD Bab 3 modul #5) — template checklist per company (light-CRUD) +
 * progress per karyawan baru. Item kategori "aset" yang ditandai selesai otomatis
 * membuat baris di tab Fasilitas karyawan (employee_facilities, sudah ada).
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
        $item->update($this->validated($request));

        return back()->with('status', 'Item checklist diperbarui.');
    }

    public function destroyTemplate(OnboardingChecklistItem $item)
    {
        $item->delete();

        return back()->with('status', 'Item checklist dihapus.');
    }

    /** Daftar karyawan yang punya task onboarding (progress belum/sudah lengkap). */
    public function index()
    {
        $employees = Employee::whereHas('onboardingTasks')
            ->withCount(['onboardingTasks', 'onboardingTasks as done_tasks_count' => fn ($q) => $q->where('is_done', true)])
            ->orderByDesc('id')->get();

        // Karyawan baru (probation atau masuk ≤ 90 hari) yang onboarding-nya belum dimulai —
        // supaya tidak ada karyawan baru yang "hilang" gara-gara checklist belum di-generate.
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
        $tasks = $employee->onboardingTasks()->with('item')->get()->sortBy('item.sort_order');

        return view('recruitment.onboarding.show', compact('employee', 'tasks'));
    }

    /** Mulai onboarding manual — generate task dari template aktif untuk karyawan
     *  yang belum punya (mis. dikonversi sebelum checklist dibuat, atau ditambah
     *  langsung lewat Data Karyawan tanpa lewat alur Rekrutmen). */
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

        foreach ($items as $item) {
            EmployeeOnboardingTask::firstOrCreate([
                'employee_id'                  => $employee->id,
                'onboarding_checklist_item_id' => $item->id,
            ]);
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

        // Item kategori "aset" yang ditandai selesai -> catat di tab Fasilitas karyawan.
        if ($isDone && $task->item->category === 'aset') {
            EmployeeFacility::firstOrCreate(
                ['employee_id' => $employee->id, 'name' => $task->item->label],
                ['received_date' => now()->toDateString(), 'remarks' => 'Onboarding checklist']
            );
        }

        return back()->with('status', $isDone ? 'Task ditandai selesai.' : 'Task dibatalkan.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'company_id'  => 'nullable|exists:companies,id',
            'label'       => 'required|string|max:200',
            'category'    => 'required|in:dokumen,akun,aset,induction',
            'is_required' => 'boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);
        $data['is_required'] = $request->boolean('is_required', true);

        return $data;
    }
}
