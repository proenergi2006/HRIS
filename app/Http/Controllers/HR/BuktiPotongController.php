<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\HR\BonusPayment;
use App\Models\HR\PayrollSlip;
use App\Models\HR\SalaryComponent;
use App\Services\Payroll\Pph21Calculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

/**
 * Bukti Potong PPh21 tahunan — format ringkas internal (bukan replika Form 1721-A1 resmi).
 * Menjumlahkan bruto kena pajak + PPh21 yang dipotong dari slip gaji yang sudah closed
 * dalam 1 tahun pajak + pajak bonus taxable. Untuk arsip & referensi karyawan.
 */
class BuktiPotongController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $companyId = (int) $request->get('company_id', $companies->first()?->id);
        $year      = (int) $request->get('year', now()->year - 1);

        $rows = $this->build($companyId, $year);
        $company = $companies->firstWhere('id', $companyId);

        return view('hr.payroll.bukti-potong.index', compact('companies', 'companyId', 'year', 'rows', 'company'));
    }

    /** ESS: karyawan unduh bukti potong tahunan miliknya sendiri. */
    public function myPdf(Request $request, int $year)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun Anda belum terhubung ke data karyawan.');
        $company = Company::findOrFail($employee->company_id);

        return $this->pdf($company, $employee, $year);
    }

    public function pdf(Company $company, Employee $employee, int $year)
    {
        abort_unless($employee->company_id === $company->id, 404);

        $rows = $this->build($company->id, $year);
        $data = collect($rows)->firstWhere('employee_id', $employee->id);
        abort_unless($data, 404, 'Tidak ada data pajak untuk karyawan/tahun ini.');

        $pdf = Pdf::loadView('hr.payroll.bukti-potong.pdf', [
            'company'  => $company,
            'employee' => $employee,
            'year'     => $year,
            'data'     => $data,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('bukti-potong-pph21-' . $employee->nip . '-' . $year . '.pdf');
    }

    /** @return array<int, array<string, mixed>> */
    private function build(int $companyId, int $year): array
    {
        $taxableComponentIds = SalaryComponent::where('is_taxable', true)->pluck('id')->all();

        $slips = PayrollSlip::with(['employee', 'details', 'period'])
            ->whereHas('period', fn ($q) => $q
                ->where('company_id', $companyId)->where('year', $year)->where('status', 'closed'))
            ->get();

        $bonusTaxByEmployee = BonusPayment::query()
            ->selectRaw('employee_id, SUM(tax_amount) as tax')
            ->whereHas('period', fn ($q) => $q
                ->where('company_id', $companyId)->whereYear('payment_date', $year)->where('is_taxable', true))
            ->groupBy('employee_id')
            ->pluck('tax', 'employee_id');

        $calc = app(Pph21Calculator::class);
        $result = [];

        foreach ($slips->groupBy('employee_id') as $employeeId => $empSlips) {
            $employee = $empSlips->first()->employee;
            if (! $employee) {
                continue;
            }

            $brutoTaxable = 0;
            $pph21        = 0;
            $months       = [];

            foreach ($empSlips as $slip) {
                $months[] = (int) $slip->period->month;
                foreach ($slip->details as $d) {
                    if ($d->type === 'allowance' && $d->salary_component_id && in_array($d->salary_component_id, $taxableComponentIds)) {
                        $brutoTaxable += (int) $d->amount;
                    }
                    if ($d->type === 'deduction' && $d->component_name === 'Potongan PPh 21') {
                        $pph21 += (int) $d->amount;
                    }
                }
            }

            $bonusTax     = (int) ($bonusTaxByEmployee[$employeeId] ?? 0);
            $pph21        += $bonusTax;

            sort($months);
            $result[] = [
                'employee_id'   => $employeeId,
                'employee'      => $employee,
                'bruto_taxable' => $brutoTaxable,
                'bonus_tax'     => $bonusTax,
                'pph21'         => $pph21,
                'ptkp_status'   => $calc->resolvePtkpStatus($employee),
                'month_from'    => $months ? $months[0] : null,
                'month_to'      => $months ? end($months) : null,
                'period_count'  => count($months),
            ];
        }

        usort($result, fn ($a, $b) => strcmp($a['employee']->name, $b['employee']->name));

        return $result;
    }
}
