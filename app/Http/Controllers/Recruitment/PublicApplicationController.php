<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\JobRequisition;
use App\Models\Master\Bank;
use App\Models\Master\BloodType;
use App\Models\Master\EducationLevel;
use App\Models\Master\MaritalStatus;
use App\Models\Master\Religion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Formulir Lamaran Kerja ProEnergi — publik (tanpa login). Data masuk sebagai Candidate
 * (status "applied") + CV terstruktur + data pre-employment, jadi nanti ikut terbawa
 * ke master karyawan lewat alur konversi kandidat yang sudah ada.
 */
class PublicApplicationController extends Controller
{
    public const SOURCE = 'Formulir Lamaran Online';

    public function show()
    {
        return view('recruitment.public.apply', [
            'requisitions'    => JobRequisition::where('status', 'approved')->with('company')->orderBy('title')->get(),
            'maritalStatuses' => MaritalStatus::where('is_active', true)->orderBy('sort_order')->get(),
            'religions'       => Religion::where('is_active', true)->orderBy('sort_order')->get(),
            'bloodTypes'      => BloodType::where('is_active', true)->orderBy('sort_order')->get(),
            'banks'           => Bank::where('is_active', true)->orderBy('name')->get(),
            'eduLevels'       => EducationLevel::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        // Honeypot sederhana — bot biasanya mengisi semua field.
        if ($request->filled('website')) {
            return redirect()->route('lamaran.success');
        }

        foreach (['expected_salary', 'ktp_number', 'npwp_number', 'bank_account_number'] as $f) {
            if ($request->filled($f)) {
                $request->merge([$f => preg_replace('/\D/', '', (string) $request->input($f))]);
            }
        }

        $data = $request->validate([
            'job_requisition_id'         => 'nullable|exists:job_requisitions,id',
            'applied_position'           => 'required_without:job_requisition_id|nullable|string|max:150',
            'name'                       => 'required|string|max:150',
            'email'                      => 'required|email|max:150',
            'phone'                      => 'required|string|max:30',
            'expected_salary'            => 'nullable|integer|min:0',

            'gender'                     => 'required|in:L,P',
            'birth_place'                => 'required|string|max:100',
            'birth_date'                 => 'required|date|before:today',
            'marital_status_id'          => 'nullable|exists:marital_statuses,id',
            'religion_id'                => 'nullable|exists:religions,id',
            'blood_type_id'              => 'nullable|exists:blood_types,id',
            'ktp_number'                 => 'required|digits:16',
            'npwp_number'                => 'nullable|string|max:30',
            'ktp_address'                => 'required|string|max:255',
            'ktp_city'                   => 'required|string|max:100',
            'domicile_address'           => 'nullable|string|max:255',
            'domicile_city'              => 'nullable|string|max:100',

            'bank_id'                    => 'nullable|exists:banks,id',
            'bank_account_number'        => 'nullable|string|max:40',
            'bank_account_holder'        => 'nullable|string|max:150',
            'bpjs_health_number'         => 'nullable|string|max:50',
            'bpjs_employment_number'     => 'nullable|string|max:50',

            'emergency_contact_name'     => 'required|string|max:120',
            'emergency_contact_relation' => 'required|string|max:60',
            'emergency_contact_phone'    => 'required|string|max:30',

            'educations'                     => 'nullable|array|max:10',
            'educations.*.education_level'   => 'nullable|string|max:50',
            'educations.*.major'             => 'nullable|string|max:150',
            'educations.*.institution'       => 'nullable|string|max:200',
            'educations.*.graduation_year'   => 'nullable|integer|min:1960|max:' . (date('Y') + 6),
            'educations.*.gpa'               => 'nullable|numeric|min:0|max:100',

            'experiences'                    => 'nullable|array|max:10',
            'experiences.*.company_name'     => 'nullable|string|max:200',
            'experiences.*.job_title'        => 'nullable|string|max:150',
            'experiences.*.start_date'       => 'nullable|date',
            'experiences.*.end_date'         => 'nullable|date',
            'experiences.*.last_salary'      => 'nullable|string|max:20',
            'experiences.*.job_description'  => 'nullable|string|max:2000',

            'skills'                         => 'nullable|string|max:1000',

            'doc_cv'                     => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'doc_ktp'                    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_ijazah'                 => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'declaration'                => 'accepted',
        ], [
            'declaration.accepted'          => 'Anda harus menyatakan bahwa data yang diisi benar.',
            'applied_position.required_without' => 'Pilih lowongan atau isi posisi yang dilamar.',
            'ktp_number.digits'             => 'No. KTP harus 16 digit.',
        ]);

        $candidate = DB::transaction(function () use ($request, $data) {
            $candidate = Candidate::create([
                'job_requisition_id' => $data['job_requisition_id'] ?? null,
                'applied_position'   => $data['applied_position'] ?? null,
                'name'               => $data['name'],
                'email'              => $data['email'],
                'phone'              => $data['phone'],
                'expected_salary'    => $data['expected_salary'] ?? null,
                'source'             => self::SOURCE,
                'status'             => 'applied',
            ]);

            $candidate->preEmployment()->create(collect($data)->only([
                'gender', 'birth_place', 'birth_date', 'marital_status_id', 'religion_id', 'blood_type_id',
                'ktp_number', 'npwp_number', 'ktp_address', 'ktp_city', 'domicile_address', 'domicile_city',
                'bank_id', 'bank_account_number', 'bank_account_holder',
                'bpjs_health_number', 'bpjs_employment_number',
                'emergency_contact_name', 'emergency_contact_relation', 'emergency_contact_phone',
            ])->all());

            foreach ($data['educations'] ?? [] as $e) {
                if (! empty($e['institution']) || ! empty($e['education_level'])) {
                    $candidate->educations()->create($e);
                }
            }

            foreach ($data['experiences'] ?? [] as $x) {
                if (empty($x['company_name'])) {
                    continue;
                }
                $x['last_salary'] = isset($x['last_salary']) && $x['last_salary'] !== ''
                    ? (int) preg_replace('/\D/', '', $x['last_salary']) : null;
                $candidate->experiences()->create($x);
            }

            foreach (preg_split('/[,\n]+/', (string) ($data['skills'] ?? '')) as $name) {
                $name = trim($name);
                if ($name !== '') {
                    $candidate->skills()->create(['name' => mb_substr($name, 0, 150)]);
                }
            }

            foreach (['doc_cv' => ['cv', 'CV / Resume'], 'doc_ktp' => ['ktp', 'KTP'], 'doc_ijazah' => ['ijazah', 'Ijazah']] as $field => [$type, $title]) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $candidate->documents()->create([
                        'doc_type'      => $type,
                        'title'         => $title,
                        'file_path'     => $file->store('candidate-documents/' . $candidate->id, 'local'),
                        'original_name' => $file->getClientOriginalName(),
                        'notes'         => 'Diunggah pelamar lewat formulir online.',
                    ]);
                }
            }

            return $candidate;
        });

        // Checklist pre-employment disiapkan supaya HR bisa langsung memprosesnya.
        CandidatePreEmploymentController::materialize($candidate->fresh());

        return redirect()->route('lamaran.success');
    }

    public function success()
    {
        return view('recruitment.public.success');
    }
}
