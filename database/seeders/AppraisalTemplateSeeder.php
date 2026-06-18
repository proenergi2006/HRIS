<?php

namespace Database\Seeders;

use App\Models\Appraisal\AppraisalAspect;
use App\Models\Appraisal\AppraisalAspectWeight;
use App\Models\Appraisal\AppraisalGradeBand;
use App\Models\Appraisal\AppraisalTemplate;
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

        // 8 aspek dengan bobot rating BS/B/C/K
        // Bobot ini mengikuti contoh form level Admin — bisa disesuaikan lewat UI
        $aspects = [
            [
                'name'    => 'Kualitas Kerja',
                'order'   => 1,
                'weights' => ['BS' => 700, 'B' => 500, 'C' => 300, 'K' => 100],
            ],
            [
                'name'    => 'Motivasi',
                'order'   => 2,
                'weights' => ['BS' => 150, 'B' => 115, 'C' => 75, 'K' => 40],
            ],
            [
                'name'    => 'Kerjasama',
                'order'   => 3,
                'weights' => ['BS' => 200, 'B' => 150, 'C' => 100, 'K' => 50],
            ],
            [
                'name'    => 'Komunikasi',
                'order'   => 4,
                'weights' => ['BS' => 100, 'B' => 75, 'C' => 50, 'K' => 25],
            ],
            [
                'name'    => 'Interaksi',
                'order'   => 5,
                'weights' => ['BS' => 100, 'B' => 75, 'C' => 50, 'K' => 25],
            ],
            [
                'name'    => 'Penerapan Pengetahuan / Keterampilan Teknis',
                'order'   => 6,
                'weights' => ['BS' => 150, 'B' => 115, 'C' => 75, 'K' => 40],
            ],
            [
                'name'    => 'Penyesuaian Diri',
                'order'   => 7,
                'weights' => ['BS' => 150, 'B' => 115, 'C' => 75, 'K' => 40],
            ],
            [
                'name'    => 'Disiplin',
                'order'   => 8,
                'weights' => ['BS' => 300, 'B' => 225, 'C' => 150, 'K' => 75],
            ],
        ];

        foreach ($aspects as $aspectData) {
            $aspect = AppraisalAspect::firstOrCreate(
                [
                    'appraisal_template_id' => $template->id,
                    'name'                  => $aspectData['name'],
                ],
                ['order' => $aspectData['order']]
            );

            foreach ($aspectData['weights'] as $rating => $score) {
                AppraisalAspectWeight::updateOrCreate(
                    ['appraisal_aspect_id' => $aspect->id, 'rating' => $rating],
                    ['score' => $score]
                );
            }
        }

        // Grade bands — urutan dari tertinggi ke terendah
        $gradeBands = [
            ['grade_label' => 'Baik Sekali', 'min_score' => 1201, 'order' => 1],
            ['grade_label' => 'Baik',        'min_score' => 1000, 'order' => 2],
            ['grade_label' => 'Cukup',       'min_score' => 0,    'order' => 3],
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
