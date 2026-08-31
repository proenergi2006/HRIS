<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\HR\EmployeeSalaryComponent;
use App\Models\HR\SalaryComponent;
use App\Models\HR\ThrPayment;
use App\Models\HR\ThrPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * THR (Tunjangan Hari Raya) — PRD Bab 3 modul #9. Mengikuti Permenaker No. 6/2016:
 * masa kerja >=1 bulan berhak THR proporsional (bulan kerja / 12, maks 12) x
 * (Gaji Pokok + Tunjangan Jabatan) — definisi "gaji tetap" yang sama dipakai
 * PayrollController utk Potongan Keterlambatan.
 */
class ThrController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $companyId = $request->get('company_id', $companies->first()?->id);

        $periods = ThrPeriod::where('company_id', $companyId)
            ->withCount('payments')
            ->withSum('payments', 'thr_amount')
            ->orderByDesc('year')->orderByDesc('payment_date')->get();

        return view('hr.thr.index', compact('companies', 'companyId', 'periods'));
    }

    public function create(Request $request)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        return view('hr.thr.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id'   => 'required|exists:companies,id',
            'year'         => 'required|integer|min:2020',
            'holiday_name' => 'required|string|max:100',
            'payment_date' => 'required|date',
        ]);

        $period = ThrPeriod::create($data + ['status' => 'open']);

        return redirect()->route('hr.thr.show', $period)->with('success', 'Periode THR berhasil dibuat.');
    }

    public function show(ThrPeriod $period)
    {
        $period->load(['company']);
        $employees = Employee::where('company_id', $period->company_id)->where('is_active', true)->orderBy('name')->get();
        $payments  = $period->payments()->with('employee')->get()->keyBy('employee_id');

        return view('hr.thr.show', compact('period', 'employees', 'payments'));
    }

    /** Hitung/generate THR semua karyawan aktif company ini — aman dijalankan ulang. */
    public function generate(ThrPeriod $period)
    {
        abort_if($period->isClosed(), 422, 'Periode THR sudah ditutup.');

        $employees = Employee::where('company_id', $period->company_id)->where('is_active', true)->get();

        $gajiPokokComponentId = SalaryComponent::where('name', 'Gaji Pokok')->value('id');

        DB::transaction(function () use ($employees, $period, $gajiPokokComponentId) {
            foreach ($employees as $employee) {
                if (! $employee->start_date) {
                    continue; // tidak bisa hitung masa kerja tanpa tanggal mulai
                }

                $monthsWorked = min(12, max(0, $employee->start_date->diffInMonths($period->payment_date)));
                if ($monthsWorked < 1) {
                    continue; // Permenaker 6/2016: minimal masa kerja 1 bulan
                }

                $gajiPokok = $gajiPokokComponentId
                    ? (int) (EmployeeSalaryComponent::where('employee_id', $employee->id)
                        ->where('salary_component_id', $gajiPokokComponentId)->value('amount') ?? 0)
                    : 0;
                $tunjanganJabatan = (int) ($employee->position?->tunjangan_jabatan ?? 0);
                $baseSalary = $gajiPokok + $tunjanganJabatan;

                $ratio  = round($monthsWorked / 12, 3);
                $amount = (int) round($ratio * $baseSalary);

                ThrPayment::updateOrCreate(
                    ['thr_period_id' => $period->id, 'employee_id' => $employee->id],
                    [
                        'base_salary'      => $baseSalary,
                        'months_worked'    => $monthsWorked,
                        'proration_ratio'  => $ratio,
                        'thr_amount'       => $amount,
                    ]
                );
            }
        });

        return back()->with('success', 'THR berhasil dihitung untuk ' . $employees->count() . ' karyawan.');
    }

    public function close(ThrPeriod $period)
    {
        abort_if($period->isClosed(), 422);
        $period->update(['status' => 'closed', 'closed_by' => auth()->id(), 'closed_at' => now()]);

        return back()->with('success', 'Periode THR berhasil ditutup.');
    }

    public function pdf(ThrPeriod $period, ThrPayment $payment)
    {
        abort_if($payment->thr_period_id !== $period->id, 404);
        $payment->load(['employee.company', 'employee.position', 'employee.department', 'employee.level']);
        $period->load(['company', 'closedBy']);

        $pdf = Pdf::loadView('hr.thr.slip-pdf', compact('period', 'payment'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('thr-' . $payment->employee->nip . '-' . $period->year . '.pdf');
    }
}
