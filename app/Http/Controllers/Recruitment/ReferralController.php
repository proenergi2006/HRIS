<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\JobRequisition;
use Illuminate\Http\Request;

/** Employee Referral Program — ESS, karyawan mereferensikan kandidat ke lowongan terbuka. */
class ReferralController extends Controller
{
    public function index(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun Anda belum terhubung ke data karyawan.');

        $openRequisitions = JobRequisition::where('status', 'approved')
            ->where('company_id', $employee->company_id)
            ->with(['position', 'department'])
            ->orderBy('title')->get();

        $myReferrals = Candidate::where('referred_by_employee_id', $employee->id)
            ->with(['jobRequisition'])
            ->latest()->get();

        return view('recruitment.referral.index', compact('employee', 'openRequisitions', 'myReferrals'));
    }

    public function store(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun Anda belum terhubung ke data karyawan.');

        $data = $request->validate([
            'job_requisition_id' => 'required|exists:job_requisitions,id',
            'name'                => 'required|string|max:150',
            'email'               => 'nullable|email|max:150',
            'phone'               => 'nullable|string|max:30',
            'notes'               => 'nullable|string|max:1000',
        ]);

        Candidate::create($data + [
            'source'                   => 'Referral Karyawan',
            'status'                   => 'applied',
            'referred_by_employee_id'  => $employee->id,
        ]);

        return back()->with('success', 'Referensi ' . $data['name'] . ' berhasil dikirim ke tim rekrutmen. Terima kasih!');
    }
}
