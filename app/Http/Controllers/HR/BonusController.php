<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\HR\BonusPayment;
use App\Models\HR\BonusPeriod;
use App\Services\Payroll\Pph21Calculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Run Bonus / Insentif (Fase 2 HRD). Pola sama THR — one-off run standalone dari payroll.
 * Nominal diisi manual per karyawan; bila periode is_taxable, PPh21 dihitung otomatis
 * lewat Pph21Calculator (metode TER) dan net = gross - pajak.
 */
class BonusController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $companyId = $request->get('company_id', $companies->first()?->id);

        $periods = BonusPeriod::where('company_id', $companyId)
            ->withCount('payments')
            ->withSum('payments', 'gross_amount')
            ->orderByDesc('payment_date')->get();

        return view('hr.bonus.index', compact('companies', 'companyId', 'periods'));
    }

    public function create(Request $request)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        return view('hr.bonus.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id'   => 'required|exists:companies,id',
            'name'         => 'required|string|max:120',
            'bonus_type'   => 'required|in:bonus,insentif,thr_susulan,other',
            'payment_date' => 'required|date',
            'is_taxable'   => 'boolean',
        ]);
        $data['is_taxable'] = $request->boolean('is_taxable');
        $data['status']     = 'open';

        $period = BonusPeriod::create($data);

        return redirect()->route('hr.bonus.show', $period)->with('status', 'Periode bonus/insentif dibuat.');
    }

    public function show(BonusPeriod $period)
    {
        $period->load('company');
        $employees = Employee::where('company_id', $period->company_id)->where('is_active', true)
            ->orderBy('name')->get();
        $payments  = $period->payments()->get()->keyBy('employee_id');

        return view('hr.bonus.show', compact('period', 'employees', 'payments'));
    }

    /** Buat baris kosong untuk semua karyawan aktif — aman dijalankan ulang. */
    public function generate(BonusPeriod $period)
    {
        abort_if($period->isClosed(), 422, 'Periode sudah ditutup.');

        $employees = Employee::where('company_id', $period->company_id)->where('is_active', true)->get();
        foreach ($employees as $employee) {
            BonusPayment::firstOrCreate(
                ['bonus_period_id' => $period->id, 'employee_id' => $employee->id],
                ['gross_amount' => 0, 'tax_amount' => 0, 'net_amount' => 0]
            );
        }

        return back()->with('status', 'Daftar karyawan disiapkan (' . $employees->count() . ').');
    }

    public function updateAmounts(Request $request, BonusPeriod $period)
    {
        abort_if($period->isClosed(), 422, 'Periode sudah ditutup.');

        $request->validate([
            'amounts'   => 'nullable|array',
            'amounts.*' => 'nullable|integer|min:0',
        ]);

        $calc = app(Pph21Calculator::class);

        DB::transaction(function () use ($request, $period, $calc) {
            foreach ($request->input('amounts', []) as $employeeId => $gross) {
                $gross = (int) $gross;
                $employee = Employee::find($employeeId);
                if (! $employee) {
                    continue;
                }

                $tax = ($period->is_taxable && $gross > 0)
                    ? $calc->calculate($employee, $gross)['amount']
                    : 0;

                BonusPayment::updateOrCreate(
                    ['bonus_period_id' => $period->id, 'employee_id' => $employeeId],
                    [
                        'gross_amount' => $gross,
                        'tax_amount'   => $tax,
                        'net_amount'   => $gross - $tax,
                    ]
                );
            }
        });

        return back()->with('status', 'Nominal bonus diperbarui.');
    }

    public function close(BonusPeriod $period)
    {
        abort_if($period->isClosed(), 422);
        $period->update(['status' => 'closed', 'closed_by' => auth()->id(), 'closed_at' => now()]);

        return back()->with('status', 'Periode bonus/insentif ditutup.');
    }

    public function pdf(BonusPeriod $period, BonusPayment $payment)
    {
        abort_if($payment->bonus_period_id !== $period->id, 404);
        $payment->load(['employee.company', 'employee.position', 'employee.department', 'employee.level']);
        $period->load(['company', 'closedBy']);

        $pdf = Pdf::loadView('hr.bonus.slip-pdf', compact('period', 'payment'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('bonus-' . $payment->employee->nip . '-' . $period->payment_date->format('Ymd') . '.pdf');
    }
}
