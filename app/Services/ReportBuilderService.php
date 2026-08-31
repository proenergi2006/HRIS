<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\HR\LeaveRequest;
use App\Models\HR\PayrollSlip;
use App\Models\TrainingParticipant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Report Builder — pilih dataset + kolom + filter, hasilnya tabel preview / export Excel.
 * Bukan pivot builder bebas — 5 dataset tetap (query sudah didefinisikan), user hanya memilih
 * KOLOM mana yang ditampilkan/diekspor + filter (PT, rentang tanggal/periode). Ini yang paling
 * aman & cukup untuk kebutuhan "tarik data ke Excel/BI" tanpa risiko query sembarangan.
 */
class ReportBuilderService
{
    /** @return array<string, array{label:string, columns:array<string,string>, needs:array}> */
    public static function datasets(): array
    {
        return [
            'employees' => [
                'label' => 'Data Karyawan',
                'columns' => [
                    'nip' => 'NIP', 'name' => 'Nama', 'company' => 'PT', 'department' => 'Departemen',
                    'position' => 'Jabatan', 'level' => 'Level', 'gender' => 'Jenis Kelamin',
                    'employment_status' => 'Status Kepegawaian', 'start_date' => 'Tgl Mulai',
                    'is_active' => 'Aktif', 'email' => 'Email', 'phone' => 'Telepon',
                ],
                'needs' => ['company'],
            ],
            'attendance' => [
                'label' => 'Absensi',
                'columns' => [
                    'date' => 'Tanggal', 'nip' => 'NIP', 'name' => 'Nama', 'department' => 'Departemen',
                    'status' => 'Status', 'check_in' => 'Jam Masuk', 'check_out' => 'Jam Keluar',
                    'late_minutes' => 'Telat (menit)', 'overtime_minutes' => 'Lembur (menit)',
                ],
                'needs' => ['company', 'date_range'],
            ],
            'leave' => [
                'label' => 'Cuti / Izin',
                'columns' => [
                    'nip' => 'NIP', 'name' => 'Nama', 'leave_type' => 'Jenis Cuti', 'start_date' => 'Mulai',
                    'end_date' => 'Selesai', 'total_days' => 'Jumlah Hari', 'status' => 'Status', 'reason' => 'Alasan',
                ],
                'needs' => ['company', 'date_range'],
            ],
            'payroll' => [
                'label' => 'Payroll Summary',
                'columns' => [
                    'period' => 'Periode', 'nip' => 'NIP', 'name' => 'Nama', 'department' => 'Departemen',
                    'gross_salary' => 'Gaji Bruto', 'total_allowances' => 'Total Tunjangan',
                    'total_deductions' => 'Total Potongan', 'net_salary' => 'Gaji Netto',
                ],
                'needs' => ['payroll_period'],
            ],
            'training' => [
                'label' => 'Training',
                'columns' => [
                    'nip' => 'NIP', 'name' => 'Nama', 'program' => 'Program', 'category' => 'Kategori',
                    'status' => 'Status', 'score' => 'Nilai', 'start_date' => 'Mulai', 'end_date' => 'Selesai',
                ],
                'needs' => ['company', 'date_range'],
            ],
        ];
    }

    public static function dataset(string $key): ?array
    {
        return self::datasets()[$key] ?? null;
    }

    /** @return Collection<int, array<string,mixed>> baris data mentah (semua kolom dataset, belum di-filter kolom pilihan). */
    public function rows(string $dataset, array $filters): Collection
    {
        return match ($dataset) {
            'employees'  => $this->employeeRows($filters),
            'attendance' => $this->attendanceRows($filters),
            'leave'      => $this->leaveRows($filters),
            'payroll'    => $this->payrollRows($filters),
            'training'   => $this->trainingRows($filters),
            default      => collect(),
        };
    }

    private function employeeRows(array $f): Collection
    {
        return Employee::query()
            ->when($f['company_id'] ?? null, fn ($q, $v) => $q->where('company_id', $v))
            ->with(['company', 'department', 'position', 'level'])
            ->orderBy('name')->get()
            ->map(fn ($e) => [
                'nip' => $e->nip, 'name' => $e->name, 'company' => $e->company?->name ?? '—',
                'department' => $e->department?->name ?? '—', 'position' => $e->position?->name ?? '—',
                'level' => $e->level?->name ?? '—', 'gender' => $e->gender ?? '—',
                'employment_status' => $e->employment_status_label ?? $e->employment_status,
                'start_date' => $e->start_date?->format('d/m/Y') ?? '—',
                'is_active' => $e->is_active ? 'Aktif' : 'Tidak Aktif',
                'email' => $e->email ?? '—', 'phone' => $e->phone ?? '—',
            ]);
    }

    private function attendanceRows(array $f): Collection
    {
        $from = $f['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $f['date_to'] ?? now()->format('Y-m-d');

        return DB::table('attendance_records')
            ->join('employees', 'employees.id', '=', 'attendance_records.employee_id')
            ->leftJoin('departments', 'departments.id', '=', 'employees.department_id')
            ->when($f['company_id'] ?? null, fn ($q, $v) => $q->where('attendance_records.company_id', $v))
            ->whereBetween('attendance_records.date', [$from, $to])
            ->orderBy('attendance_records.date')
            ->select([
                'attendance_records.date', 'employees.nip', 'employees.name',
                'departments.name as department', 'attendance_records.status',
                'attendance_records.check_in', 'attendance_records.check_out',
                'attendance_records.late_minutes', 'attendance_records.overtime_minutes',
            ])->get()
            ->map(fn ($r) => [
                'date' => \Carbon\Carbon::parse($r->date)->format('d/m/Y'), 'nip' => $r->nip, 'name' => $r->name,
                'department' => $r->department ?? '—', 'status' => ucfirst($r->status),
                'check_in' => $r->check_in ? \Carbon\Carbon::parse($r->check_in)->format('H:i') : '—',
                'check_out' => $r->check_out ? \Carbon\Carbon::parse($r->check_out)->format('H:i') : '—',
                'late_minutes' => $r->late_minutes, 'overtime_minutes' => $r->overtime_minutes,
            ]);
    }

    private function leaveRows(array $f): Collection
    {
        $from = $f['date_from'] ?? now()->startOfYear()->format('Y-m-d');
        $to   = $f['date_to'] ?? now()->format('Y-m-d');

        return LeaveRequest::with(['employee.company', 'leaveType'])
            ->when($f['company_id'] ?? null, fn ($q, $v) => $q->whereHas('employee', fn ($e) => $e->where('company_id', $v)))
            ->whereBetween('start_date', [$from, $to])
            ->orderBy('start_date')->get()
            ->map(fn ($l) => [
                'nip' => $l->employee?->nip ?? '—', 'name' => $l->employee?->name ?? '—',
                'leave_type' => $l->leaveType?->name ?? '—',
                'start_date' => $l->start_date?->format('d/m/Y'), 'end_date' => $l->end_date?->format('d/m/Y'),
                'total_days' => $l->total_days, 'status' => ucfirst($l->status), 'reason' => $l->reason ?? '—',
            ]);
    }

    private function payrollRows(array $f): Collection
    {
        if (empty($f['payroll_period_id'])) {
            return collect();
        }

        return PayrollSlip::where('payroll_period_id', $f['payroll_period_id'])
            ->with(['employee.department', 'period'])
            ->get()->sortBy(fn ($s) => $s->employee?->name)
            ->map(fn ($s) => [
                'period' => $s->period?->period_label, 'nip' => $s->employee?->nip ?? '—',
                'name' => $s->employee?->name ?? '—', 'department' => $s->employee?->department?->name ?? '—',
                'gross_salary' => (int) $s->gross_salary, 'total_allowances' => (int) $s->total_allowances,
                'total_deductions' => (int) $s->total_deductions, 'net_salary' => (int) $s->net_salary,
            ])->values();
    }

    private function trainingRows(array $f): Collection
    {
        $from = $f['date_from'] ?? now()->startOfYear()->format('Y-m-d');
        $to   = $f['date_to'] ?? now()->format('Y-m-d');

        return TrainingParticipant::with(['employee.company', 'program'])
            ->when($f['company_id'] ?? null, fn ($q, $v) => $q->whereHas('employee', fn ($e) => $e->where('company_id', $v)))
            ->whereBetween('start_date', [$from, $to])
            ->orderBy('start_date')->get()
            ->map(fn ($p) => [
                'nip' => $p->employee?->nip ?? '—', 'name' => $p->employee?->name ?? '—',
                'program' => $p->program?->title ?? '—', 'category' => $p->program?->category ?? '—',
                'status' => ucfirst($p->status), 'score' => $p->score ?? '—',
                'start_date' => $p->start_date?->format('d/m/Y') ?? '—', 'end_date' => $p->end_date?->format('d/m/Y') ?? '—',
            ]);
    }
}
