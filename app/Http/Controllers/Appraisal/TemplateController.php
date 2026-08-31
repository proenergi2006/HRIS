<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Appraisal\AppraisalGradeBand;
use App\Models\Appraisal\AppraisalTemplate;
use App\Models\Appraisal\AppraisalTemplateObjective;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class TemplateController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function index()
    {
        $templates = AppraisalTemplate::with('level')->withCount('objectives')->get();
        return view('appraisal.template.index', compact('templates'));
    }

    public function create()
    {
        $levels = Level::orderBy('name')->get();
        return view('appraisal.template.edit', [
            'template' => new AppraisalTemplate(),
            'levels'   => $levels,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'level_id' => 'nullable|exists:levels,id',
        ]);

        DB::transaction(function () use ($request) {
            $template = AppraisalTemplate::create([
                'name'       => $request->name,
                'level_id'   => $request->level_id,
                'is_default' => $request->boolean('is_default'),
            ]);

            $this->syncObjectivesAndBands($template, $request);
        });

        return redirect()->route('appraisal.templates.index')->with('status', 'Template berhasil dibuat.');
    }

    public function edit(AppraisalTemplate $template)
    {
        $template->load(['objectives', 'gradeBands']);
        $levels = Level::orderBy('name')->get();
        return view('appraisal.template.edit', compact('template', 'levels'));
    }

    public function update(Request $request, AppraisalTemplate $template)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'level_id' => 'nullable|exists:levels,id',
        ]);

        DB::transaction(function () use ($request, $template) {
            $template->update([
                'name'       => $request->name,
                'level_id'   => $request->level_id,
                'is_default' => $request->boolean('is_default'),
            ]);

            $this->syncObjectivesAndBands($template, $request);
        });

        return redirect()->route('appraisal.templates.index')->with('status', 'Template berhasil diperbarui.');
    }

    public function destroy(AppraisalTemplate $template)
    {
        if ($template->appraisals()->count() > 0) {
            return back()->with('error', 'Template tidak bisa dihapus karena sudah digunakan oleh data penilaian.');
        }

        $template->delete();
        return redirect()->route('appraisal.templates.index')->with('status', 'Template berhasil dihapus.');
    }

    private function syncObjectivesAndBands(AppraisalTemplate $template, Request $request): void
    {
        // Sync KPI/objective starter
        $submitted = $request->input('objectives', []);
        $keptIds   = [];

        foreach ($submitted as $order => $row) {
            if (empty(trim($row['title'] ?? ''))) continue;

            $objective = AppraisalTemplateObjective::updateOrCreate(
                [
                    'appraisal_template_id' => $template->id,
                    'id'                    => $row['id'] ?? null,
                ],
                [
                    'title'       => $row['title'],
                    'description' => $row['description'] ?? null,
                    'category'    => $row['category'] ?? null,
                    'weight_pct'  => (int) ($row['weight_pct'] ?? 0),
                    'order'       => $order + 1,
                ]
            );

            $keptIds[] = $objective->id;
        }

        $template->objectives()->whereNotIn('id', $keptIds)->delete();

        // Sync grade bands (replace semua)
        $submittedBands = $request->input('grade_bands', []);
        $template->gradeBands()->delete();

        foreach ($submittedBands as $order => $band) {
            if (empty(trim($band['grade_label'] ?? ''))) continue;

            AppraisalGradeBand::create([
                'appraisal_template_id' => $template->id,
                'grade_label'           => $band['grade_label'],
                'min_score'             => (int) ($band['min_score'] ?? 0),
                'order'                 => $order + 1,
            ]);
        }
    }
}
