<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\HR\EmployeeLoan;
use App\Models\HR\LoanInstallment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Kasbon / Pinjaman karyawan (Fase 2 HRD). Dikelola HR langsung — tanpa Approval Engine.
 * Cicilan otomatis dipotong di PayrollController::generate() lewat komponen
 * "Potongan Kasbon/Pinjaman" (calculation_type = loan_installment).
 */
class LoanController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $companyId = $request->get('company_id', $companies->first()?->id);
        $status    = $request->get('status');

        $loans = EmployeeLoan::with(['employee', 'installments'])
            ->where('company_id', $companyId)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()->get();

        return view('hr.loan.index', compact('companies', 'companyId', 'status', 'loans'));
    }

    public function create(Request $request)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $companyId = $request->get('company_id', $companies->first()?->id);
        $employees = Employee::where('company_id', $companyId)->where('is_active', true)
            ->orderBy('name')->get(['id', 'name', 'nip']);

        return view('hr.loan.create', compact('companies', 'companyId', 'employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'        => 'required|exists:employees,id',
            'loan_type'          => 'required|in:kasbon,pinjaman',
            'reference_no'       => 'nullable|string|max:50',
            'principal'          => 'required|integer|min:1',
            'installment_count'  => 'required|integer|min:1|max:60',
            'start_month'        => 'required|integer|between:1,12',
            'start_year'         => 'required|integer|min:2020',
            'notes'              => 'nullable|string|max:1000',
        ]);

        $employee = Employee::findOrFail($data['employee_id']);
        $data['company_id']         = $employee->company_id;
        $data['installment_amount'] = intdiv($data['principal'], $data['installment_count']);
        $data['status']             = 'active';
        $data['created_by']         = auth()->id();

        $loan = EmployeeLoan::create($data);
        $loan->generateSchedule();

        return redirect()->route('hr.loans.show', $loan)
            ->with('status', 'Kasbon/pinjaman berhasil dicatat. Jadwal cicilan dibuat.');
    }

    public function show(EmployeeLoan $loan)
    {
        $loan->load(['employee.company', 'createdBy', 'installments.slip.period']);

        return view('hr.loan.show', compact('loan'));
    }

    public function cancel(EmployeeLoan $loan)
    {
        abort_if($loan->status === 'completed', 422, 'Pinjaman sudah lunas.');

        DB::transaction(function () use ($loan) {
            $loan->installments()->where('status', 'pending')->delete();
            $loan->update(['status' => 'cancelled']);
        });

        return back()->with('status', 'Kasbon/pinjaman dibatalkan. Cicilan yang belum terpotong dihapus.');
    }

    public function waiveInstallment(EmployeeLoan $loan, LoanInstallment $installment)
    {
        abort_if($installment->employee_loan_id !== $loan->id, 404);
        abort_unless($installment->status === 'pending', 422, 'Cicilan ini sudah terpotong.');

        $installment->update(['status' => 'waived']);

        if (! $loan->installments()->where('status', 'pending')->exists()) {
            $loan->update(['status' => 'completed']);
        }

        return back()->with('status', 'Cicilan ditandai sebagai di-waive.');
    }
}
