<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Company;
use App\Models\Employee;
use App\Models\JobRequisition;
use App\Models\Position;
use Illuminate\Http\Request;

/**
 * Candidate — seleksi (screening/interview/offer) sampai diterima (PRD Bab 3 modul #3),
 * lalu konversi jadi Employee (Pre-Employment -> Employee Database, modul #4).
 */
class CandidateController extends Controller
{
    public function index(Request $request)
    {
        $query = Candidate::with(['jobRequisition.company'])->latest();

        if ($request->filled('job_requisition_id')) {
            $query->where('job_requisition_id', $request->job_requisition_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $candidates    = $query->get();
        $requisitions  = JobRequisition::where('status', 'approved')->orderByDesc('id')->get();

        return view('recruitment.candidate.index', compact('candidates', 'requisitions'));
    }

    public function create(Request $request)
    {
        $requisitions = JobRequisition::where('status', 'approved')->orderByDesc('id')->get();
        $candidate    = new Candidate(['job_requisition_id' => $request->get('job_requisition_id')]);

        return view('recruitment.candidate.form', compact('candidate', 'requisitions'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['status'] = 'applied';

        $candidate = Candidate::create($data);

        return redirect()->route('recruitment.candidates.show', $candidate)->with('success', 'Kandidat berhasil ditambahkan.');
    }

    public function edit(Candidate $candidate)
    {
        $requisitions = JobRequisition::where('status', 'approved')->orderByDesc('id')->get();
        return view('recruitment.candidate.form', compact('candidate', 'requisitions'));
    }

    public function update(Request $request, Candidate $candidate)
    {
        $candidate->update($this->validated($request));
        return back()->with('success', 'Data kandidat diperbarui.');
    }

    public function show(Candidate $candidate)
    {
        $candidate->load(['jobRequisition.company', 'interviews.interviewer', 'offers.position', 'documents', 'convertedEmployee']);
        $positions = Position::where('is_active', true)->orderBy('name')->get();

        return view('recruitment.candidate.show', compact('candidate', 'positions'));
    }

    public function destroy(Candidate $candidate)
    {
        abort_if($candidate->isConverted(), 422, 'Kandidat yang sudah jadi karyawan tidak bisa dihapus.');
        $candidate->delete();

        return redirect()->route('recruitment.candidates.index')->with('success', 'Kandidat dihapus.');
    }

    /** Ubah status seleksi (applied/screening/interview/offer/accepted/rejected/withdrawn). */
    public function updateStatus(Request $request, Candidate $candidate)
    {
        abort_if($candidate->isConverted(), 422);

        $data = $request->validate([
            'status'     => 'required|in:applied,screening,interview,offer,accepted,rejected,withdrawn',
            'mcu_result' => 'nullable|in:fit,unfit,conditional',
        ]);

        $candidate->update($data);

        return back()->with('success', 'Status kandidat diperbarui.');
    }

    /**
     * Pre-Employment -> Employee Database: konversi kandidat "accepted" jadi Employee
     * beneran, lalu auto-generate task onboarding dari template checklist company terkait.
     */
    public function convert(Request $request, Candidate $candidate)
    {
        abort_unless($candidate->isAccepted(), 422, 'Kandidat harus berstatus "Diterima" dulu sebelum dikonversi.');

        $data = $request->validate([
            'company_id'    => 'required|exists:companies,id',
            'department_id' => 'nullable|exists:departments,id',
            'section_id'    => 'nullable|exists:sections,id',
            'position_id'   => 'nullable|exists:positions,id',
            'level_id'      => 'nullable|exists:levels,id',
            'start_date'    => 'required|date',
            'employee_type' => 'required|in:local,expat',
        ]);

        $employee = Employee::create($data + [
            'name'              => $candidate->name,
            'email'             => $candidate->email,
            'phone'             => $candidate->phone,
            'employment_status' => 'probation',
            'is_active'         => true,
        ]);

        $candidate->update([
            'status'                => 'converted',
            'converted_employee_id' => $employee->id,
        ]);

        // Auto-generate task onboarding dari template checklist (global + company terkait).
        OnboardingController::materialize($employee);

        return redirect()->route('recruitment.onboarding.show', $employee)
            ->with('success', $candidate->name . ' berhasil dikonversi jadi karyawan. Lanjutkan checklist onboarding.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'job_requisition_id' => 'nullable|exists:job_requisitions,id',
            'name'                => 'required|string|max:150',
            'email'               => 'nullable|email|max:150',
            'phone'               => 'nullable|string|max:30',
            'source'              => 'nullable|string|max:100',
            'notes'               => 'nullable|string|max:2000',
        ]);
    }
}
