<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\CandidateInterview;
use App\Models\Employee;
use Illuminate\Http\Request;

class CandidateInterviewController extends Controller
{
    /** Kalender interview internal — jadwal wawancara kandidat per bulan. */
    public function calendar(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);
        $start = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $query = CandidateInterview::whereBetween('scheduled_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->whereNotNull('scheduled_at')
            ->with(['candidate.jobRequisition', 'interviewer']);

        if ($request->filled('interviewer_employee_id')) {
            $query->where('interviewer_employee_id', $request->interviewer_employee_id);
        }

        $interviews = $query->orderBy('scheduled_at')->get();
        $byDate = $interviews->groupBy(fn ($i) => $i->scheduled_at->format('Y-m-d'));

        $interviewers = Employee::where('is_active', true)
            ->whereIn('id', CandidateInterview::whereNotNull('interviewer_employee_id')->distinct()->pluck('interviewer_employee_id'))
            ->orderBy('name')->get();

        // Grid kalender: mulai Senin, sampai penuh 6 baris x 7 kolom.
        $gridStart = $start->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $gridEnd   = $end->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
        $days = [];
        for ($d = $gridStart->copy(); $d->lte($gridEnd); $d->addDay()) {
            $days[] = $d->copy();
        }

        return view('recruitment.interview-calendar', compact(
            'days', 'byDate', 'month', 'year', 'start', 'interviewers', 'interviews'
        ));
    }

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
