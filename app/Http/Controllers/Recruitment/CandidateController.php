<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Company;
use App\Models\Employee;
use App\Models\JobRequisition;
use App\Models\Master\Bank;
use App\Models\Master\BloodType;
use App\Models\Master\MaritalStatus;
use App\Models\Master\Religion;
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
        // Backfill checklist pre-employment untuk kandidat lama / yang belum punya.
        if (! $candidate->isConverted() && $candidate->preEmploymentTasks()->doesntExist()) {
            CandidatePreEmploymentController::materialize($candidate);
        }

        $candidate->load([
            'jobRequisition.company', 'interviews.interviewer', 'offers.position', 'documents', 'convertedEmployee',
            'educations', 'experiences', 'skills', 'certifications',
            'preEmployment', 'preEmploymentTasks.item',
        ]);

        $positions       = Position::where('is_active', true)->orderBy('name')->get();
        $banks           = Bank::where('is_active', true)->orderBy('name')->get();
        $maritalStatuses = MaritalStatus::where('is_active', true)->orderBy('sort_order')->get();
        $religions       = Religion::where('is_active', true)->orderBy('sort_order')->get();
        $bloodTypes      = BloodType::where('is_active', true)->orderBy('sort_order')->get();

        return view('recruitment.candidate.show', compact(
            'candidate', 'positions', 'banks', 'maritalStatuses', 'religions', 'bloodTypes'
        ));
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
            'status'            => 'required|in:applied,screening,interview,offer,accepted,rejected,withdrawn',
            'mcu_result'        => 'nullable|in:fit,unfit,conditional',
            'assessment_result' => 'nullable|in:pass,hold,fail',
            'assessment_score'  => 'nullable|numeric|min:0|max:100',
            'assessment_notes'  => 'nullable|string|max:1000',
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

        // Gate Pre-Employment — item checklist wajib harus selesai dulu.
        $candidate->load('preEmploymentTasks.item');
        if (! $candidate->preEmploymentComplete()) {
            $missing = $candidate->preEmploymentMissing();

            return back()->with('error', 'Pre-Employment belum lengkap. Item wajib yang belum: '
                . ($missing->isEmpty() ? '(checklist belum dibuat — buka detail kandidat dulu)' : $missing->implode(', ')));
        }

        $data = $request->validate([
            'company_id'    => 'required|exists:companies,id',
            'department_id' => 'nullable|exists:departments,id',
            'section_id'    => 'nullable|exists:sections,id',
            'position_id'   => 'nullable|exists:positions,id',
            'level_id'      => 'nullable|exists:levels,id',
            'start_date'    => 'required|date',
            'employee_type' => 'required|in:local,expat',
        ]);

        $pe = $candidate->preEmployment;

        $employee = Employee::create($data + [
            'name'              => $candidate->name,
            'email'             => $candidate->email,
            'phone'             => $candidate->phone,
            'employment_status' => 'probation',
            'is_active'         => true,
        ] + ($pe ? array_filter([
            'gender'                     => $pe->gender,
            'birth_place'                => $pe->birth_place,
            'birth_date'                 => $pe->birth_date,
            'marital_status_id'          => $pe->marital_status_id,
            'religion_id'                => $pe->religion_id,
            'blood_type_id'              => $pe->blood_type_id,
            'ktp_number'                 => $pe->ktp_number,
            'npwp_number'                => $pe->npwp_number,
            'ktp_address'                => $pe->ktp_address,
            'ktp_city'                   => $pe->ktp_city,
            'domicile_address'           => $pe->domicile_address,
            'domicile_city'              => $pe->domicile_city,
            'emergency_contact_name'     => $pe->emergency_contact_name,
            'emergency_contact_relation' => $pe->emergency_contact_relation,
            'emergency_contact_phone'    => $pe->emergency_contact_phone,
        ], fn ($v) => $v !== null && $v !== '') : []));

        $candidate->update([
            'status'                => 'converted',
            'converted_employee_id' => $employee->id,
        ]);

        // Pindahkan CV terstruktur + data pre-employment kandidat -> record karyawan.
        $this->carryOverProfile($candidate, $employee);
        $this->carryOverPreEmployment($pe, $employee);

        // Auto-generate task onboarding dari template checklist (global + company terkait).
        OnboardingController::materialize($employee);

        return redirect()->route('recruitment.onboarding.show', $employee)
            ->with('success', $candidate->name . ' berhasil dikonversi jadi karyawan. Lanjutkan checklist onboarding.');
    }

    /** Salin pendidikan / pengalaman / skill kandidat ke record karyawan barunya. */
    private function carryOverProfile(Candidate $candidate, Employee $employee): void
    {
        foreach ($candidate->educations as $e) {
            $employee->educations()->create([
                'education_level_id' => \App\Models\Master\EducationLevel::where('name', $e->education_level)
                    ->orWhere('code', $e->education_level)->value('id'),
                'education_major_id' => \App\Models\Master\EducationMajor::where('name', $e->major)->value('id'),
                'institution'        => $e->institution,
                'graduation_year'    => $e->graduation_year,
                'gpa'                => $e->gpa,
                'notes'              => trim('Dari kandidat. ' . ($e->notes ?? '')),
            ]);
        }

        foreach ($candidate->experiences as $x) {
            $employee->workExperiences()->create([
                'company_name'   => $x->company_name,
                'company_city'   => $x->company_city,
                'start_date'     => $x->start_date,
                'end_date'       => $x->end_date,
                'end_job_title'  => $x->job_title,
                'end_pay_rate'   => $x->last_salary,
                'job_description'=> $x->job_description,
                'remarks'        => $x->notes,
            ]);
        }

        foreach ($candidate->skills as $s) {
            $employee->skills()->create([
                'name'        => $s->name,
                'proficiency' => $s->proficiency,
                'notes'       => $s->notes,
            ]);
        }
    }

    /** Rekening bank + data BPJS dari pre-employment -> tabel karyawan terkait. */
    private function carryOverPreEmployment(?\App\Models\CandidatePreEmployment $pe, Employee $employee): void
    {
        if (! $pe) {
            return;
        }

        if ($pe->bank_id && $pe->bank_account_number) {
            $employee->bankAccounts()->create([
                'bank_id'             => $pe->bank_id,
                'account_number'      => $pe->bank_account_number,
                'account_holder_name' => $pe->bank_account_holder ?: $employee->name,
                'is_primary'          => true,
                'is_active'           => true,
            ]);
        }

        if ($pe->bpjs_health_number || $pe->bpjs_employment_number) {
            $employee->nssf()->updateOrCreate([], [
                'health_registered'     => (bool) $pe->bpjs_health_number,
                'health_number'         => $pe->bpjs_health_number,
                'health_join_date'      => $pe->bpjs_health_date,
                'employment_registered' => (bool) $pe->bpjs_employment_number,
                'employment_number'     => $pe->bpjs_employment_number,
                'employment_join_date'  => $pe->bpjs_employment_date,
            ]);
        }
    }

    private function validated(Request $request): array
    {
        if ($request->filled('expected_salary')) {
            $request->merge(['expected_salary' => preg_replace('/\D/', '', (string) $request->input('expected_salary'))]);
        }

        return $request->validate([
            'job_requisition_id' => 'nullable|exists:job_requisitions,id',
            'name'                => 'required|string|max:150',
            'email'               => 'nullable|email|max:150',
            'phone'               => 'nullable|string|max:30',
            'source'              => 'nullable|string|max:100',
            'expected_salary'     => 'nullable|integer|min:0',
            'notes'               => 'nullable|string|max:2000',
        ]);
    }
}
