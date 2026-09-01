<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\HR\ProbationReview;
use App\Notifications\GenericNotification;
use Illuminate\Http\Request;

/** Probation Review — evaluasi akhir masa probation, terpisah dari appraisal reguler. */
class ProbationReviewController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->filled('company_id') ? (int) $request->company_id : null;
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        $employees = Employee::where('is_active', true)
            ->where('employment_status', 'probation')
            ->when($companyId, fn ($q, $v) => $q->where('company_id', $v))
            ->with(['department', 'position', 'contracts' => fn ($q) => $q->where('contract_type', 'probation')->latest('end_date')])
            ->orderBy('name')->get()
            ->map(function ($e) {
                $contract = $e->contracts->first();

                return [
                    'employee'    => $e,
                    'contract'    => $contract,
                    'daysLeft'    => $contract?->end_date ? now()->startOfDay()->diffInDays($contract->end_date, false) : null,
                    'lastReview'  => ProbationReview::where('employee_id', $e->id)->latest('review_date')->first(),
                ];
            })
            ->sortBy(fn ($r) => $r['daysLeft'] ?? 9999);

        return view('hr.probation.index', compact('employees', 'companies', 'companyId'));
    }

    public function show(Employee $employee)
    {
        $employee->loadMissing(['department', 'position', 'level', 'company']);
        $contract = $employee->contracts()->where('contract_type', 'probation')->latest('end_date')->first();
        $reviews = ProbationReview::where('employee_id', $employee->id)->latest('review_date')->get();

        return view('hr.probation.show', compact('employee', 'contract', 'reviews'));
    }

    public function store(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'decision'           => 'required|in:passed,extended,failed',
            'performance_notes'  => 'nullable|string|max:2000',
            'extended_until'     => 'required_if:decision,extended|nullable|date|after:today',
        ]);

        $review = ProbationReview::create($data + [
            'employee_id'          => $employee->id,
            'review_date'          => now(),
            'reviewed_by_user_id'  => auth()->id(),
        ]);

        $message = match ($data['decision']) {
            'passed'   => 'Selamat! Anda dinyatakan lulus masa probation.',
            'extended' => 'Masa probation Anda diperpanjang hingga ' . \Carbon\Carbon::parse($data['extended_until'])->format('d/m/Y') . '.',
            'failed'   => 'Hasil evaluasi masa probation Anda — hubungi HR untuk informasi lebih lanjut.',
        };

        if ($data['decision'] === 'passed') {
            $employee->update(['employment_status' => 'permanent']);
        } elseif ($data['decision'] === 'extended') {
            $contract = $employee->contracts()->where('contract_type', 'probation')->latest('end_date')->first();
            $contract?->update(['end_date' => $data['extended_until']]);
        }

        $employee->loadMissing('user');
        $employee->user?->notify(new GenericNotification('Hasil Evaluasi Probation', $message, route('profile.edit'), 'gd-clipboard'));

        return redirect()->route('hr.probation.index')->with('success', 'Evaluasi probation ' . $employee->name . ' disimpan.');
    }
}
