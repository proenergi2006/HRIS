<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Appraisal\Feedback360Answer;
use App\Models\Appraisal\Feedback360Cycle;
use App\Models\Appraisal\Feedback360Review;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Http\Request;

/** 360° Feedback — 1 subjek dinilai banyak rater (diri sendiri/atasan/rekan/bawahan) per cycle. */
class Feedback360Controller extends Controller
{
    // ── HR — kelola cycle ────────────────────────────────────────────────

    public function index()
    {
        $cycles = Feedback360Cycle::with('company')
            ->withCount(['reviews', 'reviews as submitted_count' => fn ($q) => $q->where('status', 'submitted')])
            ->orderByDesc('id')->get();

        return view('appraisal.feedback360.index', compact('cycles'));
    }

    public function create()
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('appraisal.feedback360.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id'   => 'required|exists:companies,id',
            'title'        => 'required|string|max:150',
            'period_start' => 'required|date',
            'period_end'   => 'required|date|after_or_equal:period_start',
        ]);
        $data['created_by_user_id'] = $request->user()->id;
        $data['status'] = 'draft';

        $cycle = Feedback360Cycle::create($data);

        return redirect()->route('appraisal.feedback360.show', $cycle)->with('success', 'Cycle 360° dibuat. Tambahkan subjek & rater.');
    }

    public function show(Feedback360Cycle $cycle)
    {
        $cycle->load(['reviews.subject', 'reviews.rater']);
        $bySubject = $cycle->reviews->groupBy('subject_employee_id');
        $employees = Employee::where('is_active', true)->where('company_id', $cycle->company_id)->orderBy('name')->get(['id', 'name']);

        return view('appraisal.feedback360.show', compact('cycle', 'bySubject', 'employees'));
    }

    /** Tambah 1 subjek + beberapa rater sekaligus (self otomatis ditambahkan). */
    public function addSubject(Request $request, Feedback360Cycle $cycle)
    {
        $data = $request->validate([
            'subject_employee_id'   => 'required|exists:employees,id',
            'rater_employee_ids'    => 'nullable|array',
            'rater_employee_ids.*'  => 'nullable|exists:employees,id',
            'relation_types'        => 'nullable|array',
        ]);

        Feedback360Review::firstOrCreate([
            'cycle_id' => $cycle->id, 'subject_employee_id' => $data['subject_employee_id'],
            'rater_employee_id' => $data['subject_employee_id'],
        ], ['relation_type' => 'self']);

        foreach ($data['rater_employee_ids'] ?? [] as $i => $raterId) {
            if (empty($raterId) || (int) $raterId === (int) $data['subject_employee_id']) {
                continue;
            }
            Feedback360Review::firstOrCreate([
                'cycle_id' => $cycle->id, 'subject_employee_id' => $data['subject_employee_id'],
                'rater_employee_id' => $raterId,
            ], ['relation_type' => $data['relation_types'][$i] ?? 'peer']);
        }

        return back()->with('success', 'Subjek & rater ditambahkan.');
    }

    public function removeReview(Feedback360Cycle $cycle, Feedback360Review $review)
    {
        abort_if($review->cycle_id !== $cycle->id, 404);
        $review->delete();

        return back()->with('success', 'Rater dihapus.');
    }

    public function openCycle(Feedback360Cycle $cycle)
    {
        $cycle->update(['status' => 'open']);

        return back()->with('success', 'Cycle dibuka — rater sudah bisa mengisi penilaian.');
    }

    public function closeCycle(Feedback360Cycle $cycle)
    {
        $cycle->update(['status' => 'closed']);

        return back()->with('success', 'Cycle ditutup.');
    }

    /** Hasil rangkuman 360° untuk 1 subjek dalam cycle (dilihat HR / subjek sendiri setelah closed). */
    public function results(Feedback360Cycle $cycle, Employee $employee)
    {
        $reviews = $cycle->reviews()->where('subject_employee_id', $employee->id)
            ->with(['rater', 'answers'])->get();

        $byRelation = $reviews->where('status', 'submitted')->groupBy('relation_type');
        $questionAverages = [];
        foreach (Feedback360Review::$questions as $key => $label) {
            $ratings = $reviews->where('status', 'submitted')->flatMap->answers
                ->where('question_key', $key)->pluck('rating')->filter();
            $questionAverages[$key] = $ratings->isEmpty() ? null : round($ratings->avg(), 2);
        }

        return view('appraisal.feedback360.results', compact('cycle', 'employee', 'reviews', 'byRelation', 'questionAverages'));
    }

    // ── Rater (ESS) — isi penilaian ─────────────────────────────────────

    public function mine(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 404, 'Akun Anda belum terhubung ke data karyawan.');

        $reviews = Feedback360Review::where('rater_employee_id', $employee->id)
            ->whereHas('cycle', fn ($q) => $q->where('status', 'open'))
            ->with(['subject', 'cycle'])->get();

        return view('appraisal.feedback360.mine', compact('employee', 'reviews'));
    }

    public function fill(Feedback360Review $review)
    {
        $employee = request()->user()->employee;
        abort_unless($employee && $review->rater_employee_id === $employee->id, 403);
        abort_unless($review->cycle->status === 'open', 422, 'Cycle ini belum/tidak lagi dibuka.');

        $review->load('answers');

        return view('appraisal.feedback360.fill', compact('review'));
    }

    public function submit(Request $request, Feedback360Review $review)
    {
        $employee = $request->user()->employee;
        abort_unless($employee && $review->rater_employee_id === $employee->id, 403);
        abort_unless($review->cycle->status === 'open', 422);

        $rules = [];
        foreach (Feedback360Review::$questions as $key => $label) {
            $rules["ratings.$key"] = 'required|integer|min:1|max:5';
            $rules["comments.$key"] = 'nullable|string|max:1000';
        }
        $data = $request->validate($rules);

        foreach (Feedback360Review::$questions as $key => $label) {
            Feedback360Answer::updateOrCreate(
                ['review_id' => $review->id, 'question_key' => $key],
                ['rating' => $data['ratings'][$key], 'comment' => $data['comments'][$key] ?? null]
            );
        }

        $review->update(['status' => 'submitted', 'submitted_at' => now()]);

        return redirect()->route('feedback360.mine')->with('success', 'Penilaian 360° untuk ' . $review->subject->name . ' terkirim. Terima kasih.');
    }
}
