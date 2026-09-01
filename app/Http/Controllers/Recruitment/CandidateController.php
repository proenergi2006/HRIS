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
use App\Notifications\GenericNotification;
use Illuminate\Http\Request;

/**
 * Candidate — seleksi (screening/interview/offer) sampai diterima (PRD Bab 3 modul #3),
 * lalu konversi jadi Employee (Pre-Employment -> Employee Database, modul #4).
 */
class CandidateController extends Controller
{
    public function index(Request $request)
    {
        $query = Candidate::with(['jobRequisition.company', 'preEmploymentTasks'])->latest();

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

    public function updateReferralBonus(Request $request, Candidate $candidate)
    {
        abort_unless($candidate->referred_by_employee_id, 422, 'Kandidat ini bukan hasil referral karyawan.');

        $data = $request->validate(['referral_bonus_amount' => 'nullable|integer|min:0']);
        $data['referral_bonus_paid_at'] = $request->boolean('mark_paid') ? ($candidate->referral_bonus_paid_at ?? now()) : null;

        $candidate->update($data);

        if ($data['referral_bonus_paid_at']) {
            $candidate->referredBy?->user?->notify(new \App\Notifications\GenericNotification(
                'Bonus Referral Dibayarkan',
                'Terima kasih atas referral ' . $candidate->name . '! Bonus Rp ' . number_format($candidate->referral_bonus_amount ?? 0, 0, ',', '.') . ' sudah diproses.',
                route('recruitment.referrals.index'),
                'gd-money'
            ));
        }

        return back()->with('success', 'Bonus referral diperbarui.');
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
            'notify_candidate'  => 'nullable|boolean',
        ]);

        $wasRejected = $candidate->status === 'rejected';
        $wasAccepted = $candidate->status === 'accepted';
        $notify = $request->boolean('notify_candidate');
        unset($data['notify_candidate']);

        $candidate->update($data);

        // Notifikasi in-app ke requester Job Requisition saat kandidat pindah ke "Diterima".
        if ($data['status'] === 'accepted' && ! $wasAccepted) {
            $requester = $candidate->jobRequisition?->requestedBy;
            $requester?->notify(new GenericNotification(
                'Kandidat Diterima',
                $candidate->name . ' diterima untuk posisi ' . ($candidate->jobRequisition?->title ?? '-') . '.',
                route('recruitment.candidates.show', $candidate),
                'gd-check'
            ));
        }

        // Auto-reject email — cuma saat status BARU pindah ke rejected (bukan tiap update lain
        // saat kandidat memang sudah rejected), dan HR centang "beri tahu kandidat".
        if ($data['status'] === 'rejected' && ! $wasRejected && $notify && $candidate->email) {
            try {
                \Illuminate\Support\Facades\Mail::to($candidate->email)->send(new \App\Mail\CandidateRejectedMail($candidate));
            } catch (\Throwable $e) {
                report($e);
                return back()->with('warning', 'Status diperbarui, tapi email penolakan gagal terkirim: ' . $e->getMessage());
            }

            return back()->with('success', 'Status kandidat diperbarui. Email pemberitahuan terkirim ke ' . $candidate->email . '.');
        }

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
            'company_id'      => 'required|exists:companies,id',
            'department_id'   => 'nullable|exists:departments,id',
            'section_id'      => 'nullable|exists:sections,id',
            'position_id'     => 'nullable|exists:positions,id',
            'level_id'        => 'nullable|exists:levels,id',
            'start_date'      => 'required|date',
            'employee_type'   => 'required|in:local,expat',
            'contract_type'   => 'required|in:pkwt,pkwtt,probation,magang',
            'probation_months'=> 'nullable|integer|min:1|max:24',
            'create_account'  => 'nullable|boolean',
        ]);

        $company = Company::find($data['company_id']);
        $pe      = $candidate->preEmployment;

        $employee = Employee::create(collect($data)->only([
            'company_id', 'department_id', 'section_id', 'position_id', 'level_id', 'start_date', 'employee_type',
        ])->all() + [
            'name'              => $candidate->name,
            'email'             => $candidate->email,
            'phone'             => $candidate->phone,
            'nip'               => $this->generateNip($company, $data['start_date']),
            'employment_status' => match ($data['contract_type']) {
                'pkwtt' => 'permanent',
                'pkwt'  => 'contract',
                default => 'probation',
            },
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

        // Perjanjian kerja awal — dibuat otomatis dari pilihan konversi.
        $months  = (int) ($data['probation_months'] ?? 3);
        $endDate = in_array($data['contract_type'], ['pkwt', 'probation', 'magang'])
            ? \Carbon\Carbon::parse($data['start_date'])->addMonths($months)->toDateString()
            : null;

        $employee->contracts()->create([
            'contract_type' => $data['contract_type'],
            'number'        => $employee->nip . '/HR/' . now()->format('Y'),
            'start_date'    => $data['start_date'],
            'end_date'      => $endDate,
            'status'        => 'active',
            'notes'         => 'Dibuat otomatis saat konversi kandidat.',
        ]);
        if ($endDate) {
            $employee->update(['contract_end_date' => $endDate]);
        }

        // Akun login (opsional).
        $account = null;
        $accountNote = null;
        if ($request->boolean('create_account') && $employee->email) {
            if (\App\Models\User::where('email', $employee->email)->exists()) {
                $accountNote = 'Akun login TIDAK dibuat — email ' . $employee->email . ' sudah dipakai user lain.';
            } else {
                $account = $this->createUserAccount($employee);
            }
        }

        // Auto-generate task onboarding dari template checklist (global + company terkait).
        OnboardingController::materialize($employee);
        $this->autoCompleteOnboarding($employee, (bool) $account);

        $msg = $candidate->name . ' berhasil dikonversi jadi karyawan (NIP ' . $employee->nip . '). Lanjutkan checklist onboarding.';
        if ($account) {
            $msg .= ' Akun login dibuat — email: ' . $account['email'] . ', password sementara: ' . $account['password'];
        } elseif ($accountNote) {
            $msg .= ' ' . $accountNote;
        }

        return redirect()->route('recruitment.onboarding.show', $employee)->with('success', $msg);
    }

    /** NIP unik: 3 huruf kode PT + tahun + urutan 4 digit. */
    private function generateNip(?Company $company, string $startDate): string
    {
        $prefix = strtoupper(substr($company?->code ?? 'EMP', 0, 3));
        $year   = \Carbon\Carbon::parse($startDate)->format('Y');
        $seq    = Employee::where('company_id', $company?->id)
            ->whereYear('start_date', $year)->count() + 1;

        do {
            $nip = sprintf('%s-%s-%04d', $prefix, $year, $seq++);
        } while (Employee::where('nip', $nip)->exists());

        return $nip;
    }

    private function createUserAccount(Employee $employee): array
    {
        $password = \Illuminate\Support\Str::password(12, symbols: false);

        $user = \App\Models\User::create([
            'name'     => $employee->name,
            'email'    => $employee->email,
            'password' => bcrypt($password),
        ]);
        $user->assignRole('karyawan');
        $employee->update(['user_id' => $user->id]);

        return ['email' => $employee->email, 'password' => $password];
    }

    /** Tandai selesai item onboarding yang sudah otomatis beres saat konversi. */
    private function autoCompleteOnboarding(Employee $employee, bool $accountCreated): void
    {
        $done = ['Nomor Induk Karyawan (NIP) diterbitkan', 'Perjanjian kerja ditandatangani'];
        if ($accountCreated) {
            $done[] = 'Email & akun sistem dibuat';
        }

        $employee->onboardingTasks()->whereHas('item', fn ($q) => $q->whereIn('label', $done))
            ->update(['is_done' => true, 'done_at' => now(), 'done_by_user_id' => auth()->id()]);
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
