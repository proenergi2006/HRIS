<?php

namespace Database\Seeders;

use App\Models\Appraisal\AppraisalGradeBand;
use App\Models\Appraisal\AppraisalTemplate;
use App\Models\Appraisal\AppraisalTemplateObjective;
use App\Models\Level;
use Illuminate\Database\Seeder;

class AppraisalTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $spvLevel = Level::where('name', 'SPV')->first();

        // Template default untuk level SPV
        $template = AppraisalTemplate::firstOrCreate(
            ['name' => 'Template Penilaian SPV'],
            [
                'level_id'   => $spvLevel?->id,
                'is_default' => true,
            ]
        );

        // 8 KPI starter — bobot% mengikuti bobot relatif dari template lama
        // (BS/B/C/K point-based), dikonversi jadi skala persen (total 100%).
        // Evaluator masih bisa mengubah/menambah/menghapus KPI per penilaian.
        $objectives = [
            ['title' => 'Kualitas Kerja',                                  'category' => 'Hasil Kerja',  'weight_pct' => 35, 'order' => 1],
            ['title' => 'Disiplin',                                        'category' => 'Perilaku',     'weight_pct' => 15, 'order' => 2],
            ['title' => 'Kerjasama',                                       'category' => 'Perilaku',     'weight_pct' => 10, 'order' => 3],
            ['title' => 'Motivasi',                                        'category' => 'Perilaku',     'weight_pct' => 10, 'order' => 4],
            ['title' => 'Penerapan Pengetahuan / Keterampilan Teknis',      'category' => 'Kompetensi',   'weight_pct' => 10, 'order' => 5],
            ['title' => 'Penyesuaian Diri',                                 'category' => 'Perilaku',     'weight_pct' => 10, 'order' => 6],
            ['title' => 'Komunikasi',                                      'category' => 'Kompetensi',   'weight_pct' => 5,  'order' => 7],
            ['title' => 'Interaksi',                                       'category' => 'Perilaku',     'weight_pct' => 5,  'order' => 8],
        ];

        foreach ($objectives as $obj) {
            AppraisalTemplateObjective::updateOrCreate(
                [
                    'appraisal_template_id' => $template->id,
                    'title'                 => $obj['title'],
                ],
                [
                    'category'   => $obj['category'],
                    'weight_pct' => $obj['weight_pct'],
                    'order'      => $obj['order'],
                ]
            );
        }

        // Grade bands — skala total_score baru 0-100 (weight_pct x achievement_pct / 100)
        $gradeBands = [
            ['grade_label' => 'Baik Sekali', 'min_score' => 90, 'order' => 1],
            ['grade_label' => 'Baik',        'min_score' => 75, 'order' => 2],
            ['grade_label' => 'Cukup',       'min_score' => 0,  'order' => 3],
        ];

        foreach ($gradeBands as $band) {
            AppraisalGradeBand::updateOrCreate(
                [
                    'appraisal_template_id' => $template->id,
                    'grade_label'           => $band['grade_label'],
                ],
                ['min_score' => $band['min_score'], 'order' => $band['order']]
            );
        }
    }
}
