<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\HR\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $companies  = Company::where('is_active', true)->get();
        $companyId  = $request->get('company_id', $companies->first()?->id);
        $month      = (int) $request->get('month', now()->month);
        $year       = (int) $request->get('year', now()->year);

        $employees = Employee::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Build rekap: employee -> array of days in month
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $records = AttendanceRecord::where('company_id', $companyId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->groupBy('employee_id');

        return view('hr.attendance.index', compact(
            'companies', 'companyId', 'month', 'year',
            'employees', 'records', 'daysInMonth'
        ));
    }

    public function create(Request $request)
    {
        $companies = Company::where('is_active', true)->get();
        $companyId = $request->get('company_id', $companies->first()?->id);
        $employees = Employee::where('company_id', $companyId)
            ->where('is_active', true)->orderBy('name')->get();

        return view('hr.attendance.create', compact('companies', 'companyId', 'employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id'       => 'required|exists:companies,id',
            'employee_id'      => 'required|exists:employees,id',
            'date'             => 'required|date',
            'check_in'         => 'nullable|date_format:H:i',
            'check_out'        => 'nullable|date_format:H:i',
            'status'           => 'required|in:hadir,telat,izin,sakit,cuti,alpha,libur',
            'late_minutes'     => 'nullable|integer|min:0',
            'overtime_minutes' => 'nullable|integer|min:0',
            'notes'            => 'nullable|string|max:500',
        ]);

        AttendanceRecord::updateOrCreate(
            ['employee_id' => $data['employee_id'], 'date' => $data['date']],
            array_merge($data, ['source' => 'manual'])
        );

        return redirect()->route('hr.attendance.index', [
            'company_id' => $data['company_id'],
            'month' => date('n', strtotime($data['date'])),
            'year'  => date('Y', strtotime($data['date'])),
        ])->with('success', 'Data absensi berhasil disimpan.');
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'company_id'  => 'required|exists:companies,id',
            'date'        => 'required|date',
            'attendance'  => 'required|array',
            'attendance.*.employee_id' => 'required|exists:employees,id',
            'attendance.*.status'      => 'required|in:hadir,telat,izin,sakit,cuti,alpha,libur',
            'attendance.*.check_in'    => 'nullable|date_format:H:i',
            'attendance.*.check_out'   => 'nullable|date_format:H:i',
        ]);

        $companyId = $request->company_id;
        $date      = $request->date;

        DB::transaction(function () use ($request, $companyId, $date) {
            foreach ($request->attendance as $row) {
                AttendanceRecord::updateOrCreate(
                    ['employee_id' => $row['employee_id'], 'date' => $date],
                    [
                        'company_id'       => $companyId,
                        'check_in'         => $row['check_in'] ?? null,
                        'check_out'        => $row['check_out'] ?? null,
                        'status'           => $row['status'],
                        'late_minutes'     => $row['late_minutes'] ?? 0,
                        'overtime_minutes' => $row['overtime_minutes'] ?? 0,
                        'notes'            => $row['notes'] ?? null,
                        'source'           => 'manual',
                    ]
                );
            }
        });

        return redirect()->route('hr.attendance.index', [
            'company_id' => $companyId,
            'month' => date('n', strtotime($date)),
            'year'  => date('Y', strtotime($date)),
        ])->with('success', 'Absensi ' . date('d/m/Y', strtotime($date)) . ' berhasil disimpan.');
    }

    public function importForm(Request $request)
    {
        $companies = Company::where('is_active', true)->get();
        return view('hr.attendance.import', compact('companies'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'file'       => 'required|file|mimes:txt,dat,csv|max:10240',
            'month'      => 'required|integer|between:1,12',
            'year'       => 'required|integer|min:2020',
        ]);

        $companyId = $request->company_id;
        $content   = file_get_contents($request->file('file')->getRealPath());
        $lines     = preg_split('/\r?\n/', trim($content));

        // Format Solution X606/X601: employee_id  date  time  device  status
        // Contoh: 1    2026-07-01    08:00:10    1    0
        // status 0=check-in, 1=check-out
        $rawData = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            $parts = preg_split('/\s+/', $line);
            if (count($parts) < 3) continue;

            $empCode = trim($parts[0]);
            $dateStr = trim($parts[1]);
            $timeStr = substr(trim($parts[2]), 0, 5); // HH:MM
            $inOut   = isset($parts[4]) ? (int) $parts[4] : 0;

            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) continue;

            $key = $empCode . '|' . $dateStr;
            if ($inOut === 0) {
                $rawData[$key]['check_in'] = $timeStr;
            } else {
                $rawData[$key]['check_out'] = $timeStr;
            }
            $rawData[$key]['emp_code'] = $empCode;
            $rawData[$key]['date']     = $dateStr;
        }

        $imported = 0;
        $skipped  = 0;
        foreach ($rawData as $row) {
            // Cari employee by NIP / user_id
            $employee = Employee::where('company_id', $companyId)
                ->where(function ($q) use ($row) {
                    $q->where('nip', $row['emp_code'])
                      ->orWhereHas('user', fn($u) => $u->where('id', $row['emp_code']));
                })->first();

            if (! $employee) { $skipped++; continue; }

            $checkIn  = $row['check_in'] ?? null;
            $checkOut = $row['check_out'] ?? null;

            // Hitung keterlambatan (jam kerja default 08:00)
            $lateMinutes = 0;
            if ($checkIn) {
                [$h, $m] = explode(':', $checkIn);
                $arrivalMins = $h * 60 + $m;
                $startMins   = 8 * 60;
                $lateMinutes = max(0, $arrivalMins - $startMins);
            }

            AttendanceRecord::updateOrCreate(
                ['employee_id' => $employee->id, 'date' => $row['date']],
                [
                    'company_id'   => $companyId,
                    'check_in'     => $checkIn,
                    'check_out'    => $checkOut,
                    'status'       => $lateMinutes > 0 ? 'telat' : 'hadir',
                    'late_minutes' => $lateMinutes,
                    'source'       => 'import',
                ]
            );
            $imported++;
        }

        return redirect()->route('hr.attendance.index', [
            'company_id' => $companyId,
            'month'      => $request->month,
            'year'       => $request->year,
        ])->with('success', "Import selesai: {$imported} record berhasil, {$skipped} karyawan tidak ditemukan.");
    }
}
