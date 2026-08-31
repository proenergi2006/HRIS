<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\CandidateInterview;
use Illuminate\Http\Request;

class CandidateInterviewController extends Controller
{
    public function store(Request $request, Candidate $candidate)
    {
        $data = $this->validated($request);
        $candidate->interviews()->create($data);

        return back()->with('success', 'Jadwal interview ditambahkan.');
    }

    public function update(Request $request, Candidate $candidate, CandidateInterview $interview)
    {
        abort_if($interview->candidate_id !== $candidate->id, 404);
        $interview->update($this->validated($request));

        return back()->with('success', 'Interview diperbarui.');
    }

    public function destroy(Candidate $candidate, CandidateInterview $interview)
    {
        abort_if($interview->candidate_id !== $candidate->id, 404);
        $interview->delete();

        return back()->with('success', 'Interview dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'stage'                    => 'required|string|max:100',
            'scheduled_at'             => 'nullable|date',
            'interviewer_employee_id'  => 'nullable|exists:employees,id',
            'result'                   => 'required|in:pending,pass,fail',
            'notes'                    => 'nullable|string|max:1000',
        ]);
    }
}
