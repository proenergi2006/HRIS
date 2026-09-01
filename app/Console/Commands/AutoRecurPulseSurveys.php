<?php

namespace App\Console\Commands;

use App\Models\Survey\Survey;
use Illuminate\Console\Command;

/**
 * Pulse/eNPS survey berkala otomatis — kalau survey.recurrence bukan 'none' dan
 * survey sudah closed serta belum punya "penerus" (child lewat parent_survey_id),
 * dan sudah lewat interval (monthly/quarterly) sejak dibuka, buat + buka survey
 * baru otomatis (clone judul+pertanyaan, recurrence & company_id ikut turunan).
 */
class AutoRecurPulseSurveys extends Command
{
    protected $signature   = 'survey:auto-recur';
    protected $description = 'Buat & buka otomatis putaran berikutnya utk survey pulse/eNPS berkala yang sudah closed';

    private const INTERVALS = ['monthly' => 1, 'quarterly' => 3];

    public function handle(): void
    {
        $candidates = Survey::where('status', 'closed')
            ->whereIn('recurrence', array_keys(self::INTERVALS))
            ->whereDoesntHave('children')
            ->whereNotNull('opens_at')
            ->with('questions')
            ->get();

        $created = 0;

        foreach ($candidates as $survey) {
            $monthsDue = self::INTERVALS[$survey->recurrence];
            if ($survey->opens_at->copy()->addMonths($monthsDue)->isFuture()) {
                continue;
            }

            $clone = $survey->replicate(['status', 'opens_at', 'closes_at']);
            $clone->title = preg_replace('/\s—\s.+$/', '', $survey->title) . ' — ' . now()->translatedFormat('F Y');
            $clone->status = 'open';
            $clone->opens_at = now();
            $clone->closes_at = $survey->closes_at && $survey->opens_at
                ? now()->addDays($survey->opens_at->diffInDays($survey->closes_at))
                : null;
            $clone->parent_survey_id = $survey->id;
            $clone->save();

            foreach ($survey->questions as $q) {
                $clone->questions()->create([
                    'text' => $q->text, 'type' => $q->type, 'options' => $q->options,
                    'is_required' => $q->is_required, 'sort_order' => $q->sort_order,
                ]);
            }

            $created++;
        }

        $this->info("Survey berkala baru dibuat & dibuka: {$created}.");
    }
}
