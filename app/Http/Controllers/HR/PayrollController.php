<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\HR\AttendanceRecord;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\PayrollSlip;
use App\Models\HR\PayrollSlipDetail;
use App\Models\HR\SalaryComponent;
use App\Models\HR\EmployeeSalaryComponent;
use App\Models\Reimbursement\ReimbursementRequest;
use App\Services\Payroll\Pph21Calculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    // ── Periods ──────────────────────────────────────────────────────────

    public function periods(Request $request)
    {
        $companies = Company::where('is_active', true)->get();
        $companyId = $request->get('company_id', $companies->first()?->id);

        $periods = PayrollPeriod::where('company_id', $companyId)
            ->orderByDesc('year')->orderByDesc('month')
            ->paginate(12)->withQueryString();

        return view('hr.payroll.periods', compact('companies', 'companyId', 'periods'));
    }

    public function createPeriod(Request $request)
    {
        $companies = Company::where('is_active', true)->get();
        return view('hr.payroll.create-period', compact('companies'));
    }

    public function storePeriod(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'month'      => 'required|integer|between:1,12',
            'year'       => 'required|integer|min:2020',
            'notes'      => 'nullable|string|max:500',
        ]);

        $period = PayrollPeriod::firstOrCreate(
            ['company_id' => $data['company_id'], 'month' => $data['month'], 'year' => $data['year']],
            ['status' => 'open', 'notes' => $data['notes'] ?? null]
        );

        return redirect()->route('hr.payroll.show', $period)
            ->with('success', 'Periode penggajian berhasil dibuat.');
    }

    // ── Slips ─────────────────────────────────────────────────────────────

    public function show(PayrollPeriod $period)
    {
        $period->load(['company', 'slips.employee']);
        $employees = Employee::where('company_id', $period->company_id)
            ->where('is_active', true)->orderBy('name')->get();

        $slipByEmp = $period->slips->keyBy('employee_id');

        return view('hr.payroll.show', compact('period', 'employees', 'slipByEmp'));
    }

    public function generate(Request $request, PayrollPeriod $period)
    {
        abort_if($period->status === 'closed', 422, 'Periode sudah ditutup.');

        $employeeIds = $request->input('employee_ids', []);
        if (empty($employeeIds)) {
            $employeeIds = Employee::where('company_id', $period->company_id)
                ->where('is_active', true)->pluck('id')->toArray();
        }

        $components = SalaryComponent::where(function ($q) use ($period) {
                $q->whereNull('company_id')->orWhere('company_id', $period->company_id);
            })
            ->where('is_active', true)->orderBy('sort_order')->get();

        $cutoffStart = $period->cutoffStart();
        $cutoffEnd   = $period->cutoffEnd();
        $workingDays = $this->countWorkingDays($cutoffStart, $cutoffEnd);

        DB::transaction(function () use ($period, $employeeIds, $components, $workingDays, $cutoffStart, $cutoffEnd) {
            foreach ($employeeIds as $empId) {
                $employee = Employee::find($empId);
                if (! $employee) continue;

                // Attendance summary for this period (cut-off tgl 17 bulan lalu s/d tgl 16 bulan ini)
                $attendances = AttendanceRecord::where('employee_id', $empId)
                    ->whereBetween('date', [$cutoffStart, $cutoffEnd])
                    ->get();

                $attendanceDays  = $attendances->whereIn('status', ['hadir', 'telat'])->count();
                $leaveDays       = $attendances->whereIn('status', ['izin', 'sakit', 'cuti'])->count();
                $alphaDays       = $attendances->where('status', 'alpha')->count();
                $lateMinutes     = $attendances->sum('late_minutes');
                $overtimeMinutes = $attendances->sum('overtime_minutes');

                // Salary components for this employee
                $empComponents = EmployeeSalaryComponent::where('employee_id', $empId)
                    ->get()->keyBy('salary_component_id');

                $totalAllowances = 0;
                $totalDeductions = 0;
                $details = [];

                // Komponen diproses berurutan sesuai ketergantungan data:
                // 1. manual          — nominal diketik admin (Gaji Pokok, Tunjangan Operasional, Potongan PPh 21)
                // 2. position_fixed  — Tunjangan Jabatan: nominal tetap dari master Jabatan
                // 3. position_daily  — Tunjangan Makan & Transport: tarif harian (master Jabatan) x hari hadir
                // 4. overtime        — Tunjangan Lembur: tarif/jam (master Jabatan) x jam lembur
                // 5. percent_of_base / late_deduction — butuh Gaji Tetap (Gaji Pokok + Tunjangan Jabatan) dari langkah 1-2
                // 6. medical_claim   — independen, dari reimbursement medical approved
                // 7. pph21_ter       — PPh21 TER otomatis, dari total komponen is_taxable=true yang SUDAH terhitung
                //                      (lihat App\Services\Payroll\Pph21Calculator — masih perlu validasi Finance)
                // 8. mirror_pph21    — butuh nominal Potongan PPh 21 dari langkah 7 (gross-up)
                // 9. loan_installment — Potongan Kasbon/Pinjaman: cicilan jatuh tempo periode ini
                $calcOrder = ['manual', 'position_fixed', 'position_daily', 'overtime', 'percent_of_base', 'late_deduction', 'medical_claim', 'pph21_ter', 'mirror_pph21', 'loan_installment'];

                // Reset cicilan pinjaman yang pernah ditandai slip ini — supaya generate ulang idempotent.
                $existingSlipId = PayrollSlip::where('payroll_period_id', $period->id)
                    ->where('employee_id', $empId)->value('id');
                if ($existingSlipId) {
                    \App\Models\HR\LoanInstallment::where('payroll_slip_id', $existingSlipId)
                        ->update(['status' => 'pending', 'payroll_slip_id' => null, 'deducted_at' => null]);
                }

                foreach ($calcOrder as $calcType) {
                    foreach ($components->where('calculation_type', $calcType) as $comp) {
                        $gajiTetap = $this->findAmount($details, 'Gaji Pokok') + $this->findAmount($details, 'Tunjangan Jabatan');

                        $amount = match ($calcType) {
                            'manual'          => (int) ($empComponents[$comp->id]->amount ?? 0),
                            'position_fixed'  => (int) ($employee->position?->tunjangan_jabatan ?? 0),
                            'position_daily'  => (int) round(($employee->position?->tunjangan_harian ?? 0) * $attendanceDays),
                            'overtime'        => $this->calcOvertime($overtimeMinutes, $employee->position?->tarif_lembur),
                            'percent_of_base' => $this->calcPercentOfBase($gajiTetap, $comp->rate_percent, $comp->salary_cap),
                            'late_deduction'  => $this->calcLateDeduction($lateMinutes, $gajiTetap),
                            'medical_claim'   => $this->calcMedicalClaim($employee, $period),
                            'pph21_ter'       => $this->calcPph21Ter($employee, $details, $components),
                            'mirror_pph21'    => $this->findAmount($details, 'Potongan PPh 21', 'deduction'),
                            'loan_installment' => $this->calcLoanInstallment($empId, $period),
                            default           => 0,
                        };
                        if ($amount === 0) continue;

                        $this->addDetail($details, $totalAllowances, $totalDeductions, $comp, $amount);
                    }
                }

                $gajiPokok = $this->findAmount($details, 'Gaji Pokok');

                // Potongan absensi: (alpha_days / working_days) * gaji_pokok
                $potonganAlpha = $workingDays > 0
                    ? (int) round(($alphaDays / $workingDays) * $gajiPokok)
                    : 0;
                if ($potonganAlpha > 0) {
                    $totalDeductions += $potonganAlpha;
                    $details[] = [
                        'salary_component_id' => null,
                        'component_name'      => 'Potongan Alpha (' . $alphaDays . ' hari)',
                        'type'                => 'deduction',
                        'amount'              => $potonganAlpha,
                    ];
                }

                $grossSalary = $totalAllowances;
                $netSalary   = $grossSalary - $totalDeductions;

                $slip = PayrollSlip::updateOrCreate(
                    ['payroll_period_id' => $period->id, 'employee_id' => $empId],
                    [
                        'working_days'     => $workingDays,
                        'attendance_days'  => $attendanceDays,
                        'leave_days'       => $leaveDays,
                        'alpha_days'       => $alphaDays,
                        'late_minutes'     => $lateMinutes,
                        'total_allowances' => $totalAllowances,
                        'total_deductions' => $totalDeductions,
                        'gross_salary'     => $grossSalary,
                        'net_salary'       => $netSalary,
                    ]
                );

                $slip->details()->delete();
                foreach ($details as $d) {
                    $slip->details()->create($d);
                }

                // Tandai cicilan pinjaman yang benar-benar terpotong di slip ini.
                if ($this->findAmount($details, 'Potongan Kasbon/Pinjaman', 'deduction') > 0) {
                    \App\Models\HR\LoanInstallment::whereHas('loan', fn ($q) => $q
                            ->where('employee_id', $empId)->where('status', 'active'))
                        ->where('period_month', $period->month)
                        ->where('period_year', $period->year)
                        ->where('status', 'pending')
                        ->update(['status' => 'deducted', 'payroll_slip_id' => $slip->id, 'deducted_at' => now()]);
                }
            }
        });

        return redirect()->route('hr.payroll.show', $period)
            ->with('success', 'Slip gaji berhasil digenerate untuk ' . count($employeeIds) . ' karyawan.');
    }

    // ── ESS — karyawan lihat slip gaji sendiri (PRD Bab 4: "lihat slip gaji") ──────

    public function mySlips(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun Anda belum terhubung ke data karyawan.');

        $slips = PayrollSlip::where('employee_id', $employee->id)
            ->with('period')
            ->whereHas('period', fn ($q) => $q->where('status', 'closed'))
            ->orderByDesc('id')->get();

        $taxYears = $slips->pluck('period.year')->unique()->sortDesc()->values();

        return view('hr.payroll.my-slips', compact('slips', 'taxYears'));
    }

    public function mySlipPdf(Request $request, PayrollSlip $slip)
    {
        $employee = $request->user()->employee;
        abort_unless($employee && $slip->employee_id === $employee->id, 403);
        abort_unless($slip->period?->status === 'closed', 403, 'Slip belum final.');

        return $this->renderSlipPdf($slip, $slip->period);
    }

    public function slipPdf(PayrollPeriod $period, PayrollSlip $slip)
    {
        return $this->renderSlipPdf($slip, $period);
    }

    private function renderSlipPdf(PayrollSlip $slip, PayrollPeriod $period)
    {
        $slip->load(['employee.company', 'employee.level', 'employee.department', 'employee.position', 'details', 'period.company', 'period.closedBy']);
        $pdf = Pdf::loadView('hr.payroll.slip-pdf', compact('slip', 'period'))
            ->setPaper('a4', 'landscape');
        $filename = 'slip-gaji-' . $slip->employee->nip . '-' . str_pad($period->month, 2, '0', STR_PAD_LEFT) . $period->year . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * File transfer bank (CSV) untuk pembayaran gaji satu periode. Nomor rekening
     * karyawan ter-enkripsi (cast 'encrypted') jadi harus di-decrypt di PHP, bukan SQL.
     * Format kolom per bank hanya PERKIRAAN — verifikasikan dengan pihak bank.
     */
    public function disbursement(Request $request, PayrollPeriod $period)
    {
        $format = $request->get('format', 'generic');

        $slips = PayrollSlip::with(['employee.bankAccounts.bank'])
            ->where('payroll_period_id', $period->id)
            ->get()
            ->sortBy(fn ($s) => $s->employee?->name);

        $period->loadMissing('company');
        $filename = 'transfer-gaji-' . ($period->company?->code ?? 'co') . '-'
            . str_pad($period->month, 2, '0', STR_PAD_LEFT) . $period->year . '-' . $format . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->streamDownload(function () use ($slips, $period, $format) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['No', 'NIP', 'Nama', 'Bank', 'Kode Bank', 'No Rekening', 'Jumlah', 'Keterangan']);

            $no = 0;
            foreach ($slips as $slip) {
                $emp  = $slip->employee;
                $acct = $emp?->bankAccounts->firstWhere('is_primary', true) ?? $emp?->bankAccounts->first();
                if (! $emp || ! $acct) {
                    continue; // dilewati — tidak ada rekening
                }
                $no++;
                $ket = 'Gaji ' . $period->period_label;

                $number = '';
                try {
                    $number = (string) $acct->account_number;
                } catch (\Throwable $e) {
                    $number = '';
                }

                if ($format === 'bca') {
                    // BCA payroll: No Rekening Tujuan | Nominal | (kosong) | Nama | Keterangan
                    fputcsv($out, [$number, (int) $slip->net_salary, '', $emp->name, $ket]);
                } elseif ($format === 'mandiri') {
                    fputcsv($out, [$acct->bank?->code, $number, $emp->name, (int) $slip->net_salary, $ket]);
                } else {
                    fputcsv($out, [$no, $emp->nip, $emp->name, $acct->bank?->name, $acct->bank?->code, $number, (int) $slip->net_salary, $ket]);
                }
            }
            fclose($out);
        }, $filename, $headers);
    }

    public function close(PayrollPeriod $period)
    {
        abort_if($period->status === 'closed', 422);
        $period->update(['status' => 'closed', 'closed_by' => auth()->id(), 'closed_at' => now()]);

        // Tandai pinjaman lunas bila semua cicilannya sudah terpotong/di-waive.
        \App\Models\HR\EmployeeLoan::where('company_id', $period->company_id)
            ->where('status', 'active')
            ->whereDoesntHave('installments', fn ($q) => $q->where('status', 'pending'))
            ->update(['status' => 'completed']);

        return back()->with('success', 'Periode penggajian berhasil ditutup.');
    }

    // ── Salary structure ──────────────────────────────────────────────────

    public function salaryIndex(Request $request)
    {
        $companies  = Company::where('is_active', true)->get();
        $companyId  = $request->get('company_id', $companies->first()?->id);
        $components = SalaryComponent::where(function ($q) use ($companyId) {
                $q->whereNull('company_id')->orWhere('company_id', $companyId);
            })
            ->where('is_active', true)->where('calculation_type', 'manual')->orderBy('sort_order')->get();

        $employees = Employee::where('company_id', $companyId)
            ->where('is_active', true)
            ->with(['salaryComponents.component'])
            ->orderBy('name')->get();

        return view('hr.payroll.salary-index', compact('companies', 'companyId', 'components', 'employees'));
    }

    public function salaryEdit(Request $request, Employee $employee)
    {
        $components = SalaryComponent::where(function ($q) use ($employee) {
                $q->whereNull('company_id')->orWhere('company_id', $employee->company_id);
            })
            ->where('is_active', true)->where('calculation_type', 'manual')->orderBy('sort_order')->get();

        $autoComponents = SalaryComponent::where(function ($q) use ($employee) {
                $q->whereNull('company_id')->orWhere('company_id', $employee->company_id);
            })
            ->where('is_active', true)->where('calculation_type', '!=', 'manual')->orderBy('sort_order')->get();

        $existing = EmployeeSalaryComponent::where('employee_id', $employee->id)
            ->pluck('amount', 'salary_component_id');

        return view('hr.payroll.salary-edit', compact('employee', 'components', 'autoComponents', 'existing'));
    }

    public function salaryUpdate(Request $request, Employee $employee)
    {
        $request->validate([
            'components'   => 'nullable|array',
            'components.*' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $employee) {
            foreach ($request->input('components', []) as $componentId => $amount) {
                if ($amount === null || $amount === '') {
                    EmployeeSalaryComponent::where('employee_id', $employee->id)
                        ->where('salary_component_id', $componentId)->delete();
                    continue;
                }
                EmployeeSalaryComponent::updateOrCreate(
                    ['employee_id' => $employee->id, 'salary_component_id' => $componentId],
                    ['amount' => (int) $amount]
                );
            }
        });

        return redirect()->route('hr.payroll.salary.index', ['company_id' => $employee->company_id])
            ->with('success', 'Komponen gaji ' . $employee->name . ' berhasil diperbarui.');
    }

    // ── Salary Components Master ──────────────────────────────────────────

    public function components(Request $request)
    {
        $companies  = Company::where('is_active', true)->get();
        $companyId  = $request->get('company_id');
        $components = SalaryComponent::whereNull('company_id')
            ->orWhere('company_id', $companyId)
            ->orderBy('sort_order')->get();

        return view('hr.payroll.components', compact('companies', 'companyId', 'components'));
    }

    public function storeComponent(Request $request)
    {
        $data = $request->validate([
            'company_id'  => 'nullable|exists:companies,id',
            'name'        => 'required|string|max:100',
            'type'        => 'required|in:allowance,deduction',
            'is_taxable'  => 'boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        // Komponen baru lewat form ini selalu manual — kalkulasi otomatis
        // (persen gaji, keterlambatan, medical claim, gross-up PPh21) hanya
        // untuk komponen bawaan sistem yang sudah di-seed.
        SalaryComponent::create($data + ['is_taxable' => $request->boolean('is_taxable'), 'calculation_type' => 'manual']);
        return back()->with('success', 'Komponen gaji berhasil ditambahkan.');
    }

    public function updateComponentRate(Request $request, SalaryComponent $component)
    {
        abort_unless($component->calculation_type === 'percent_of_base', 422, 'Komponen ini tidak memakai skema persentase.');

        $data = $request->validate([
            'rate_percent' => 'required|numeric|min:0|max:100',
            'salary_cap'   => 'nullable|integer|min:0',
        ]);

        $component->update([
            'rate_percent' => $data['rate_percent'],
            'salary_cap'   => $data['salary_cap'] ?: null,
        ]);

        return back()->with('success', 'Tarif ' . $component->name . ' berhasil diperbarui.');
    }

    public function destroyComponent(SalaryComponent $component)
    {
        $component->delete();
        return back()->with('success', 'Komponen gaji berhasil dihapus.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function countWorkingDays(Carbon $start, Carbon $end): int
    {
        $working = 0;
        $cursor  = $start->copy();
        while ($cursor->lte($end)) {
            if ($cursor->dayOfWeekIso < 6) $working++;
            $cursor->addDay();
        }
        return $working;
    }

    private function addDetail(array &$details, int &$totalAllowances, int &$totalDeductions, SalaryComponent $comp, int $amount): void
    {
        if ($comp->type === 'allowance') {
            $totalAllowances += $amount;
        } else {
            $totalDeductions += $amount;
        }

        $details[] = [
            'salary_component_id' => $comp->id,
            'component_name'      => $comp->name,
            'type'                => $comp->type,
            'amount'              => $amount,
        ];
    }

    private function findAmount(array $details, string $name, string $type = 'allowance'): int
    {
        foreach ($details as $d) {
            if ($d['component_name'] === $name && $d['type'] === $type) {
                return $d['amount'];
            }
        }

        return 0;
    }

    /**
     * BPJS Kesehatan / Jaminan Pensiun: persen x (Gaji Pokok + Tunjangan Jabatan),
     * dibatasi salary_cap jika diisi.
     */
    private function calcPercentOfBase(int $gajiTetap, ?float $ratePercent, ?int $cap): int
    {
        if (! $ratePercent || $gajiTetap <= 0) return 0;
        $basis = $cap ? min($gajiTetap, $cap) : $gajiTetap;

        return (int) round($ratePercent / 100 * $basis);
    }

    /**
     * Potongan Keterlambatan: menit terlambat x tarif per menit.
     * Tarif per menit = gaji tetap / 173 jam kerja sebulan / 60 (Kepmenakertrans No. 102/2004).
     */
    private function calcLateDeduction(int $lateMinutes, int $gajiTetap): int
    {
        if ($lateMinutes <= 0 || $gajiTetap <= 0) return 0;
        $perMinute = $gajiTetap / 173 / 60;

        return (int) round($lateMinutes * $perMinute);
    }

    /**
     * Medical Claim: total reimbursement medical yang sudah approved dalam periode cut-off
     * tanggal 16 bulan sebelumnya s/d tanggal 16 bulan periode berjalan.
     */
    private function calcMedicalClaim(Employee $employee, PayrollPeriod $period): int
    {
        if (! $employee->user_id) return 0;

        return (int) ReimbursementRequest::where('user_id', $employee->user_id)
            ->where('status', 'approved')
            ->whereBetween('request_date', [$period->cutoffStart(), $period->cutoffEnd()])
            ->sum('total_claim');
    }

    /**
     * Tunjangan Lembur: total menit lembur (dari absensi) / 60 x tarif per jam (master Jabatan).
     */
    private function calcOvertime(int $overtimeMinutes, ?int $ratePerHour): int
    {
        if ($overtimeMinutes <= 0 || ! $ratePerHour) return 0;

        return (int) round(($overtimeMinutes / 60) * $ratePerHour);
    }

    /**
     * Potongan Kasbon/Pinjaman: total cicilan jatuh tempo (period_month/year periode ini)
     * dari semua pinjaman aktif karyawan yang belum terpotong.
     */
    private function calcLoanInstallment(int $employeeId, PayrollPeriod $period): int
    {
        return (int) \App\Models\HR\LoanInstallment::whereHas('loan', fn ($q) => $q
                ->where('employee_id', $employeeId)->where('status', 'active'))
            ->where('period_month', $period->month)
            ->where('period_year', $period->year)
            ->where('status', 'pending')
            ->sum('amount');
    }

    /**
     * PPh21 TER — jumlahkan komponen allowance yang SUDAH terhitung ($details) dan bertanda
     * is_taxable, lalu hitung lewat Pph21Calculator (PTKP dari status kawin + tanggungan,
     * kategori TER, tarif progresif). Lihat catatan validasi di Pph21Calculator.
     */
    private function calcPph21Ter(Employee $employee, array $details, $components): int
    {
        $taxableGross = 0;
        foreach ($details as $d) {
            if ($d['type'] !== 'allowance' || ! $d['salary_component_id']) continue;
            $comp = $components->firstWhere('id', $d['salary_component_id']);
            if ($comp?->is_taxable) {
                $taxableGross += $d['amount'];
            }
        }

        if ($taxableGross <= 0) return 0;

        return app(Pph21Calculator::class)->calculate($employee, $taxableGross)['amount'];
    }
}
