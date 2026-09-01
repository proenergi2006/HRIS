<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Survey\Survey;
use App\Models\Survey\SurveyAnswer;
use App\Models\Survey\SurveyQuestion;
use App\Models\Survey\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Survey kepuasan / engagement karyawan (Fase 2 HRD). */
class SurveyController extends Controller
{
    // ── Self-service — semua karyawan yang login ────────────────────────────

    public function index(Request $request)
    {
        $user = $request->user();
        $answeredIds = SurveyResponse::where('user_id', $user->id)->pluck('survey_id');

        $surveys = Survey::where('status', 'open')
            ->where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $user->employee?->company_id))
            ->whereNotIn('id', $answeredIds)
            ->orderByDesc('opens_at')->get();

        return view('surveys.index', compact('surveys'));
    }

    public function show(Request $request, Survey $survey)
    {
        abort_unless($survey->isOpen(), 404);
        $already = SurveyResponse::where('survey_id', $survey->id)->where('user_id', $request->user()->id)->exists();
        abort_if($already, 422, 'Anda sudah mengisi survey ini.');

        $survey->load('questions');

        return view('surveys.show', compact('survey'));
    }

    public function submit(Request $request, Survey $survey)
    {
        abort_unless($survey->isOpen(), 422);
        $already = SurveyResponse::where('survey_id', $survey->id)->where('user_id', $request->user()->id)->exists();
        abort_if($already, 422, 'Anda sudah mengisi survey ini.');

        $survey->load('questions');
        $rules = [];
        foreach ($survey->questions as $q) {
            $rules['answers.' . $q->id] = ($q->is_required ? 'required' : 'nullable');
        }
        $request->validate($rules);

        $employee = $request->user()->employee;

        DB::transaction(function () use ($request, $survey, $employee) {
            $response = SurveyResponse::create([
                'survey_id'     => $survey->id,
                'user_id'       => $request->user()->id,
                'employee_id'   => $survey->is_anonymous ? null : $employee?->id,
                'company_id'    => $employee?->company_id,
                'department_id' => $employee?->department_id,
                'submitted_at'  => now(),
            ]);

            foreach ($survey->questions as $q) {
                $val = $request->input('answers.' . $q->id);
                if ($val === null) {
                    continue;
                }
                SurveyAnswer::create([
                    'survey_response_id' => $response->id,
                    'survey_question_id' => $q->id,
                    'value'               => is_array($val) ? null : $val,
                    'value_json'          => is_array($val) ? $val : null,
                ]);
            }
        });

        return redirect()->route('surveys.index')->with('status', 'Terima kasih, jawaban Anda tersimpan.');
    }

    // ── HR (survey.edit) ─────────────────────────────────────────────────────

    public function manage()
    {
        $surveys = Survey::withCount('responses')->latest()->get();

        return view('surveys.manage.index', compact('surveys'));
    }

    public function create()
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('surveys.manage.form', ['companies' => $companies, 'survey' => new Survey(), 'questions' => collect()]);
    }

    public function store(Request $request)
    {
        $survey = DB::transaction(function () use ($request) {
            $survey = Survey::create($this->validatedSurvey($request) + ['created_by_user_id' => auth()->id()]);
            $this->syncQuestions($request, $survey);

            return $survey;
        });

        return redirect()->route('surveys.manage.index')->with('status', 'Survey berhasil dibuat.');
    }

    public function edit(Survey $survey)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $questions = $survey->questions;

        return view('surveys.manage.form', compact('companies', 'survey', 'questions'));
    }

    public function update(Request $request, Survey $survey)
    {
        DB::transaction(function () use ($request, $survey) {
            $survey->update($this->validatedSurvey($request));
            $this->syncQuestions($request, $survey);
        });

        return redirect()->route('surveys.manage.index')->with('status', 'Survey berhasil diperbarui.');
    }

    public function destroy(Survey $survey)
    {
        $survey->delete();

        return back()->with('status', 'Survey dihapus.');
    }

    public function open(Survey $survey)
    {
        abort_if($survey->questions()->count() === 0, 422, 'Tambahkan minimal 1 pertanyaan sebelum membuka survey.');
        $survey->update(['status' => 'open']);

        // Notifikasi in-app ke semua user dalam scope (company_id survey, atau semua kalau null).
        $users = \App\Models\User::when($survey->company_id, fn ($q, $v) => $q->whereHas('employee', fn ($e) => $e->where('company_id', $v)))
            ->get();
        \Illuminate\Support\Facades\Notification::send($users, new \App\Notifications\GenericNotification(
            'Survey Baru Dibuka',
            $survey->title,
            route('surveys.show', $survey),
            'gd-clipboard'
        ));

        return back()->with('status', 'Survey dibuka. Notifikasi terkirim ke ' . $users->count() . ' user.');
    }

    public function close(Survey $survey)
    {
        $survey->update(['status' => 'closed']);

        return back()->with('status', 'Survey ditutup.');
    }

    public function duplicate(Survey $survey)
    {
        $clone = $survey->replicate(['status', 'opens_at', 'closes_at']);
        $clone->title = $survey->title . ' — ' . now()->translatedFormat('F Y');
        $clone->status = 'draft';
        $clone->created_by_user_id = auth()->id();
        $clone->save();

        foreach ($survey->questions as $q) {
            $clone->questions()->create([
                'text' => $q->text, 'type' => $q->type, 'options' => $q->options,
                'is_required' => $q->is_required, 'sort_order' => $q->sort_order,
            ]);
        }

        return redirect()->route('surveys.manage.edit', $clone)
            ->with('status', 'Survey diduplikat sebagai draft baru — sesuaikan tanggal lalu buka.');
    }

    /** Tren skor eNPS (%Promoter - %Detraktor) dari seluruh survey bertipe eNPS. */
    public function enpsTrend()
    {
        $surveys = Survey::where('type', 'enps')
            ->whereIn('status', ['open', 'closed'])
            ->with('questions')
            ->orderBy('opens_at')->get();

        $points = $surveys->map(function ($s) {
            $question = $s->questions->firstWhere('type', 'scale');
            if (! $question) {
                return null;
            }
            $enps = $this->computeEnps($question->id);

            return [
                'survey'    => $s,
                'label'     => $s->title,
                'date'      => $s->opens_at,
                'score'     => $enps['score'],
                'promoters' => $enps['promoters'],
                'passives'  => $enps['passives'],
                'detractors' => $enps['detractors'],
                'total'     => $enps['total'],
            ];
        })->filter()->values();

        return view('surveys.manage.enps-trend', compact('points'));
    }

    private function computeEnps(int $questionId): array
    {
        $values = SurveyAnswer::where('survey_question_id', $questionId)
            ->whereNotNull('value')->pluck('value')->map(fn ($v) => (int) $v);

        $total = $values->count();
        if ($total === 0) {
            return ['score' => null, 'promoters' => 0, 'passives' => 0, 'detractors' => 0, 'total' => 0];
        }

        $promoters  = $values->filter(fn ($v) => $v >= 9)->count();
        $detractors = $values->filter(fn ($v) => $v <= 6)->count();
        $passives   = $total - $promoters - $detractors;
        $score      = round(($promoters / $total - $detractors / $total) * 100);

        return compact('score', 'promoters', 'passives', 'detractors', 'total');
    }

    public function results(Survey $survey)
    {
        $survey->load('questions');

        $enps = null;
        if ($survey->type === 'enps') {
            $scaleQuestion = $survey->questions->firstWhere('type', 'scale');
            if ($scaleQuestion) {
                $enps = $this->computeEnps($scaleQuestion->id);
            }
        }

        $data = [];
        foreach ($survey->questions as $q) {
            $answers = SurveyAnswer::where('survey_question_id', $q->id)->get();

            if ($q->type === 'scale') {
                $values = $answers->pluck('value')->filter(fn ($v) => $v !== null)->map(fn ($v) => (float) $v);
                $data[$q->id] = [
                    'question' => $q,
                    'avg'      => $values->isNotEmpty() ? round($values->avg(), 2) : null,
                    'distribution' => $values->countBy()->sortKeys(),
                    'count'    => $values->count(),
                ];
            } elseif (in_array($q->type, ['single', 'multi'])) {
                $counts = collect();
                foreach ($answers as $a) {
                    $vals = $a->value_json ?? ($a->value ? [$a->value] : []);
                    foreach ((array) $vals as $v) {
                        $counts[$v] = ($counts[$v] ?? 0) + 1;
                    }
                }
                $data[$q->id] = ['question' => $q, 'counts' => $counts, 'count' => $answers->count()];
            } else {
                $data[$q->id] = ['question' => $q, 'texts' => $answers->pluck('value')->filter(), 'count' => $answers->count()];
            }
        }

        $respondentCount = SurveyResponse::where('survey_id', $survey->id)->count();

        return view('surveys.manage.results', compact('survey', 'data', 'respondentCount', 'enps'));
    }

    private function validatedSurvey(Request $request): array
    {
        $data = $request->validate([
            'company_id'    => 'nullable|exists:companies,id',
            'title'          => 'required|string|max:200',
            'description'    => 'nullable|string',
            'is_anonymous'   => 'boolean',
            'type'           => 'required|in:standard,pulse,enps',
            'recurrence'     => 'nullable|in:none,monthly,quarterly',
            'opens_at'       => 'nullable|date',
            'closes_at'      => 'nullable|date',
        ]);
        $data['is_anonymous'] = $request->boolean('is_anonymous', true);
        $data['recurrence'] = $data['recurrence'] ?? 'none';

        return $data;
    }

    private function syncQuestions(Request $request, Survey $survey): void
    {
        $request->validate([
            'questions'        => 'nullable|array',
            'questions.*.text' => 'required_with:questions|string|max:500',
            'questions.*.type' => 'required_with:questions|in:scale,text,single,multi',
        ]);

        $survey->questions()->delete();
        foreach ($request->input('questions', []) as $i => $q) {
            if (empty($q['text'])) {
                continue;
            }
            $options = null;
            if (in_array($q['type'], ['single', 'multi']) && ! empty($q['options'])) {
                $options = array_values(array_filter(array_map('trim', explode(',', $q['options']))));
            }
            SurveyQuestion::create([
                'survey_id'   => $survey->id,
                'text'         => $q['text'],
                'type'         => $q['type'],
                'options'      => $options,
                'is_required'  => ! empty($q['is_required']),
                'sort_order'   => $i,
            ]);
        }
    }
}
