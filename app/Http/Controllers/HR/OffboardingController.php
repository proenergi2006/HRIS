<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeFacility;
use App\Models\EmployeeOffboardingTask;
use App\Models\HR\EmployeeLoan;
use App\Models\OffboardingChecklistItem;
use App\Models\TerminationRequest;
use Illuminate\Http\Request;

/**
 * Checklist clearance saat resign (Fase 2 HRD) — pola sama Recruitment\OnboardingController.
 * Dikelola HR langsung (tanpa Approval Engine). Task dimaterialisasi otomatis saat
 * TerminationRequest dibuat (lihat HrRequestController::store()) atau manual via "Mulai Clearance".
 */
class OffboardingController extends Controller
{
    public function templates()
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $items     = OffboardingChecklistItem::with('company')->orderBy('category')->orderBy('sort_order')->get();

        return view('hr.offboarding.templates', compact('items', 'companies'));
    }

    public function storeTemplate(Request $request)
    {
        OffboardingChecklistItem::create($this->validated($request));

        return back()->with('status', 'Item checklist ditambahkan.');
    }

    public function updateTemplate(Request $request, OffboardingChecklistItem $item)
    {
        $item->update($this->validated($request));

        return back()->with('status', 'Item checklist diperbarui.');
    }

    public function destroyTemplate(OffboardingChecklistItem $item)
    {
        $item->delete();

        return back()->with('status', 'Item checklist dihapus.');
    }

    public function index()
    {
        $employees = Employee::whereHas('offboardingTasks')
            ->withCount(['offboardingTasks', 'offboardingTasks as done_tasks_count' => fn ($q) => $q->where('is_done', true)])
            ->orderByDesc('id')->get();

        return view('hr.offboarding.index', compact('employees'));
    }

    /** Mulai clearance manual untuk karyawan yang belum punya task (di luar alur TerminationRequest). */
    public function start(Employee $employee)
    {
        self::materialize($employee);

        return redirect()->route('hr.offboarding.show', $employee)->with('status', 'Clearance resign dimulai.');
    }

    public function show(Employee $employee)
    {
        $tasks           = $employee->offboardingTasks()->with('item')->get()->sortBy('item.sort_order');
        $outstandingLoans = EmployeeLoan::where('employee_id', $employee->id)->where('status', 'active')->get();
        $termination     = TerminationRequest::where('employee_id', $employee->id)->latest('id')->first();

        return view('hr.offboarding.show', compact('employee', 'tasks', 'outstandingLoans', 'termination'));
    }

    /** Exit Interview & Final Settlement — dicatat di TerminationRequest terkait. */
    public function updateExit(Request $request, Employee $employee)
    {
        $termination = TerminationRequest::where('employee_id', $employee->id)->latest('id')->firstOrFail();

        $data = $request->validate([
            'exit_interview_date'    => 'nullable|date',
            'exit_interview_notes'   => 'nullable|string|max:2000',
            'final_settlement_amount'=> 'nullable|string',
            'final_settlement_date'  => 'nullable|date',
            'final_settlement_notes' => 'nullable|string|max:2000',
        ]);

        if (! empty($data['final_settlement_amount'])) {
            $data['final_settlement_amount'] = (int) preg_replace('/\D/', '', $data['final_settlement_amount']);
        }
        if (! empty($data['exit_interview_date']) && empty($data['exit_interview_by_user_id'] ?? null)) {
            $data['exit_interview_by_user_id'] = auth()->id();
        }

        $termination->update($data);

        return back()->with('status', 'Data Exit Interview / Final Settlement disimpan.');
    }

    public function toggleTask(Request $request, Employee $employee, EmployeeOffboardingTask $task)
    {
        abort_if($task->employee_id !== $employee->id, 404);

        $isDone = ! $task->is_done;
        $task->update([
            'is_done'         => $isDone,
            'done_at'         => $isDone ? now() : null,
            'done_by_user_id' => $isDone ? auth()->id() : null,
        ]);

        // Kategori "aset" selesai -> tandai SEMUA fasilitas milik karyawan yang belum
        // dikembalikan sebagai kembali. Item checklist aset biasanya berupa aksi umum
        // ("Kembalikan laptop/aset kantor"), bukan nama aset spesifik seperti di
        // onboarding — jadi dicocokkan per-employee, bukan per-nama-item.
        if ($isDone && $task->item->category === 'aset') {
            EmployeeFacility::where('employee_id', $employee->id)
                ->whereNull('returned_date')
                ->update(['returned_date' => now()->toDateString()]);
        }

        return back()->with('status', $isDone ? 'Task ditandai selesai.' : 'Task dibatalkan.');
    }

    /** Materialisasi task dari template aktif (company_id null = global) untuk 1 karyawan. */
    public static function materialize(Employee $employee): void
    {
        $items = OffboardingChecklistItem::where('is_active', true)
            ->where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $employee->company_id))
            ->get();

        foreach ($items as $item) {
            EmployeeOffboardingTask::firstOrCreate([
                'employee_id'                   => $employee->id,
                'offboarding_checklist_item_id' => $item->id,
            ]);
        }
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'company_id'  => 'nullable|exists:companies,id',
            'label'       => 'required|string|max:200',
            'category'    => 'required|in:aset,akun,dokumen,keuangan,exit',
            'is_required' => 'boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);
        $data['is_required'] = $request->boolean('is_required', true);

        return $data;
    }
}
