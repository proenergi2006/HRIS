<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use Illuminate\Http\Request;

/**
 * CV terstruktur kandidat (ATS) — pendidikan / pengalaman / skill / sertifikasi.
 * Satu controller generik: {type} menentukan relasi + aturan validasi.
 */
class CandidateProfileController extends Controller
{
    private const TYPES = [
        'education'     => ['relation' => 'educations',     'label' => 'Pendidikan'],
        'experience'    => ['relation' => 'experiences',    'label' => 'Pengalaman Kerja'],
        'skill'         => ['relation' => 'skills',         'label' => 'Skill'],
        'certification' => ['relation' => 'certifications', 'label' => 'Sertifikasi'],
    ];

    public function store(Request $request, Candidate $candidate, string $type)
    {
        $cfg = $this->config($type);
        $candidate->{$cfg['relation']}()->create($this->validated($request, $type));

        return back()->with('success', $cfg['label'] . ' ditambahkan.');
    }

    public function update(Request $request, Candidate $candidate, string $type, int $id)
    {
        $cfg = $this->config($type);
        $row = $candidate->{$cfg['relation']}()->findOrFail($id);
        $row->update($this->validated($request, $type));

        return back()->with('success', $cfg['label'] . ' diperbarui.');
    }

    public function destroy(Candidate $candidate, string $type, int $id)
    {
        $cfg = $this->config($type);
        $candidate->{$cfg['relation']}()->findOrFail($id)->delete();

        return back()->with('success', $cfg['label'] . ' dihapus.');
    }

    private function config(string $type): array
    {
        abort_unless(isset(self::TYPES[$type]), 404);

        return self::TYPES[$type];
    }

    private function validated(Request $request, string $type): array
    {
        return $request->validate(match ($type) {
            'education' => [
                'education_level' => 'nullable|string|max:50',
                'major'           => 'nullable|string|max:150',
                'institution'     => 'nullable|string|max:200',
                'graduation_year' => 'nullable|integer|min:1950|max:' . (now()->year + 10),
                'gpa'             => 'nullable|numeric|min:0|max:4',
                'notes'           => 'nullable|string|max:255',
            ],
            'experience' => [
                'company_name'    => 'required|string|max:200',
                'job_title'       => 'nullable|string|max:150',
                'company_city'    => 'nullable|string|max:100',
                'start_date'      => 'nullable|date',
                'end_date'        => 'nullable|date|after_or_equal:start_date',
                'last_salary'     => 'nullable|integer|min:0',
                'job_description' => 'nullable|string|max:2000',
                'notes'           => 'nullable|string|max:255',
            ],
            'skill' => [
                'name'        => 'required|string|max:150',
                'proficiency' => 'nullable|in:basic,intermediate,advanced,expert',
                'notes'       => 'nullable|string|max:255',
            ],
            'certification' => [
                'name'          => 'required|string|max:200',
                'issuer'        => 'nullable|string|max:150',
                'issued_date'   => 'nullable|date',
                'expires_date'  => 'nullable|date|after_or_equal:issued_date',
                'credential_id' => 'nullable|string|max:100',
                'notes'         => 'nullable|string|max:255',
            ],
        });
    }
}
