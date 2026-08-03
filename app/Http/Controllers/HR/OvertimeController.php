<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\HR\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OvertimeController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::where('is_active', true)->get();
        $companyId = $request->get('company_id', $companies->first()?->id);
        $month     = (int) $request->get('month', now()->month);
        $year      = (int) $request->get('year', now()->year);

        $records = AttendanceRecord::where('company_id', $companyId)
            ->where('overtime_minutes', '>', 0)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with('employee')
            ->orderBy('date')
            ->get();

        return view('hr.overtime.index', compact('companies', 'companyId', 'month', 'year', 'records'));
    }

    public function create(Request $request)
    {
        $companies = Company::where('is_active', true)->get();
        $companyId = $request->get('company_id', $companies->first()?->id);
        $date      = $request->get('date', now()->format('Y-m-d'));

        $employees = Employee::where('company_id', $companyId)
            ->where('is_active', true)->orderBy('name')->get();

        $existingMinutes = AttendanceRecord::where('company_id', $companyId)
            ->where('date', $date)->pluck('overtime_minutes', 'employee_id');

        return view('hr.overtime.create', compact('companies', 'companyId', 'date', 'employees', 'existingMinutes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id'  => 'required|exists:companies,id',
            'date'        => 'required|date',
            'overtime'    => 'nullable|array',
            'overtime.*'  => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['overtime'] ?? [] as $employeeId => $hours) {
                $minutes = (int) round(((float) $hours) * 60);
                $exists  = AttendanceRecord::where('employee_id', $employeeId)->where('date', $data['date'])->exists();

                // Jangan buat record absensi kosong hanya karena jam lembur 0 —
                // tapi tetap izinkan mengosongkan lembur yang sudah ada.
                if ($minutes <= 0 && ! $exists) continue;

                AttendanceRecord::updateOrCreate(
                    ['employee_id' => $employeeId, 'date' => $data['date']],
                    ['company_id' => $data['company_id'], 'overtime_minutes' => $minutes]
                );
            }
        });

        return redirect()->route('hr.overtime.index', [
            'company_id' => $data['company_id'],
            'month' => date('n', strtotime($data['date'])),
            'year'  => date('Y', strtotime($data['date'])),
        ])->with('success', 'Data lembur ' . date('d/m/Y', strtotime($data['date'])) . ' berhasil disimpan.');
    }
}
