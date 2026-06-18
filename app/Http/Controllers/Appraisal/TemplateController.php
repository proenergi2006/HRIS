<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Appraisal\AppraisalAspect;
use App\Models\Appraisal\AppraisalAspectWeight;
use App\Models\Appraisal\AppraisalGradeBand;
use App\Models\Appraisal\AppraisalTemplate;
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
        $templates = AppraisalTemplate::with('level')->withCount('aspects')->get();
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

            $this->syncAspectsAndBands($template, $request);
        });

        return redirect()->route('appraisal.templates.index')->with('status', 'Template berhasil dibuat.');
    }

    public function edit(AppraisalTemplate $template)
    {
        $template->load(['aspects.weights', 'gradeBands']);
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

            $this->syncAspectsAndBands($template, $request);
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

    private function syncAspectsAndBands(AppraisalTemplate $template, Request $request): void
    {
        // Sync aspects & weights
        $submittedAspects = $request->input('aspects', []);
        $keptIds = [];

        foreach ($submittedAspects as $order => $aspectData) {
            if (empty(trim($aspectData['name'] ?? ''))) continue;

            $aspect = AppraisalAspect::updateOrCreate(
                [
                    'appraisal_template_id' => $template->id,
                    'id'                    => $aspectData['id'] ?? null,
                ],
                ['name' => $aspectData['name'], 'order' => $order + 1]
            );

            $keptIds[] = $aspect->id;

            foreach (['BS', 'B', 'C', 'K'] as $rating) {
                AppraisalAspectWeight::updateOrCreate(
                    ['appraisal_aspect_id' => $aspect->id, 'rating' => $rating],
                    ['score' => (int) ($aspectData['weights'][$rating] ?? 0)]
                );
            }
        }

        // Remove deleted aspects
        $template->aspects()->whereNotIn('id', $keptIds)->delete();

        // Sync grade bands
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
