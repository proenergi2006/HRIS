<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\HR\RosterEntry;
use App\Models\HR\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Shift & Roster (Fase 2 HRD). Roster harian per karyawan dipakai
 * AttendanceController::import() untuk menghitung telat & lembur.
 */
class RosterController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::where('is_active', true)->get();
        $companyId = (int) $request->get('company_id', $companies->first()?->id);
        $month     = (int) $request->get('month', now()->month);
        $year      = (int) $request->get('year', now()->year);

        $employees = Employee::where('company_id', $companyId)->where('is_active', true)
            ->orderBy('name')->get(['id', 'name', 'nip']);

        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;

        $entries = RosterEntry::where('company_id', $companyId)
            ->whereYear('work_date', $year)->whereMonth('work_date', $month)
            ->get()
            ->groupBy('employee_id');

        $shifts = Shift::where('is_active', true)
            ->where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $companyId))
            ->orderBy('start_time')->get();

        return view('hr.roster.index', compact(
            'companies', 'companyId', 'month', 'year', 'employees', 'daysInMonth', 'entries', 'shifts'
        ));
    }

    public function bulkAssign(Request $request)
    {
        $data = $request->validate([
            'company_id'   => 'required|exists:companies,id',
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'date_from'    => 'required|date',
            'date_to'      => 'required|date|after_or_equal:date_from',
            'shift_id'     => 'nullable|exists:shifts,id',
            'skip_weekend' => 'boolean',
        ]);

        $skipWeekend = $request->boolean('skip_weekend');
        $from = Carbon::parse($data['date_from']);
        $to   = Carbon::parse($data['date_to']);

        DB::transaction(function () use ($data, $from, $to, $skipWeekend) {
            foreach ($data['employee_ids'] as $employeeId) {
                for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                    if ($skipWeekend && $d->isWeekend()) {
                        continue;
                    }
                    RosterEntry::updateOrCreate(
                        ['employee_id' => $employeeId, 'work_date' => $d->toDateString()],
                        ['company_id' => $data['company_id'], 'shift_id' => $data['shift_id'] ?: null]
                    );
                }
            }
        });

        return back()->with('status', 'Roster berhasil diterapkan.');
    }

    public function updateCell(Request $request)
    {
        $data = $request->validate([
            'company_id'  => 'required|exists:companies,id',
            'employee_id' => 'required|exists:employees,id',
            'work_date'   => 'required|date',
            'shift_id'    => 'nullable|exists:shifts,id',
        ]);

        RosterEntry::updateOrCreate(
            ['employee_id' => $data['employee_id'], 'work_date' => $data['work_date']],
            ['company_id' => $data['company_id'], 'shift_id' => $data['shift_id'] ?: null]
        );

        return back()->with('status', 'Sel roster diperbarui.');
    }

    public function clear(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'date_from'  => 'required|date',
            'date_to'    => 'required|date|after_or_equal:date_from',
        ]);

        RosterEntry::where('company_id', $data['company_id'])
            ->whereBetween('work_date', [$data['date_from'], $data['date_to']])
            ->delete();

        return back()->with('status', 'Roster pada rentang tanggal dihapus.');
    }

    // ── Master Shift ──────────────────────────────────────────────────────

    public function shifts()
    {
        $companies = Company::where('is_active', true)->get();
        $shifts    = Shift::with('company')->orderBy('start_time')->get();

        return view('hr.roster.shifts', compact('companies', 'shifts'));
    }

    public function storeShift(Request $request)
    {
        Shift::create($this->validatedShift($request));

        return back()->with('status', 'Shift ditambahkan.');
    }

    public function updateShift(Request $request, Shift $shift)
    {
        $shift->update($this->validatedShift($request));

        return back()->with('status', 'Shift diperbarui.');
    }

    public function destroyShift(Shift $shift)
    {
        abort_if($shift->rosterEntries()->exists(), 422, 'Shift dipakai di roster — tidak bisa dihapus.');
        $shift->delete();

        return back()->with('status', 'Shift dihapus.');
    }

    private function validatedShift(Request $request): array
    {
        $data = $request->validate([
            'company_id'         => 'nullable|exists:companies,id',
            'code'               => 'required|string|max:20',
            'name'               => 'required|string|max:60',
            'start_time'         => 'required|date_format:H:i',
            'end_time'           => 'required|date_format:H:i',
            'break_minutes'      => 'nullable|integer|min:0|max:480',
            'late_grace_minutes' => 'nullable|integer|min:0|max:120',
            'crosses_midnight'   => 'boolean',
            'is_active'          => 'boolean',
        ]);
        $data['break_minutes']      = $data['break_minutes'] ?? 0;
        $data['late_grace_minutes'] = $data['late_grace_minutes'] ?? 0;
        $data['crosses_midnight']   = $request->boolean('crosses_midnight');
        $data['is_active']          = $request->boolean('is_active', true);

        return $data;
    }
}
