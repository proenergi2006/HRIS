<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Pastikan level Staff (id=5) dan Senior Staff ada
        $staffLevelId = DB::table('levels')->where('name', 'Staff')->value('id');
        if (!$staffLevelId) {
            $staffLevelId = DB::table('levels')->insertGetId(['name' => 'Staff', 'created_at' => now(), 'updated_at' => now()]);
        }

        $seniorLevelId = DB::table('levels')->where('name', 'Senior Staff')->value('id');
        if (!$seniorLevelId) {
            $seniorLevelId = DB::table('levels')->insertGetId(['name' => 'Senior Staff', 'created_at' => now(), 'updated_at' => now()]);
        }

        // Hapus template Staff lama (fixed_points) dan buat ulang sebagai weighted_scale
        $oldTemplate = DB::table('appraisal_templates')->where('level_id', $staffLevelId)->first();
        if ($oldTemplate) {
            $aspects = DB::table('appraisal_aspects')->where('appraisal_template_id', $oldTemplate->id)->pluck('id');
            DB::table('appraisal_aspect_weights')->whereIn('appraisal_aspect_id', $aspects)->delete();
            DB::table('appraisal_grade_bands')->where('appraisal_template_id', $oldTemplate->id)->delete();
            DB::table('appraisal_aspects')->where('appraisal_template_id', $oldTemplate->id)->delete();
            DB::table('appraisal_templates')->where('id', $oldTemplate->id)->delete();
        }

        $factors = [
            ['name' => 'Kemampuan Fungsional',  'weight_pct' => 25, 'order' => 1],
            ['name' => 'Motivasi Kerja',         'weight_pct' => 15, 'order' => 2],
            ['name' => 'Perencanaan Kerja',      'weight_pct' => 10, 'order' => 3],
            ['name' => 'Absensi & Disiplin',     'weight_pct' => 10, 'order' => 4],
            ['name' => 'Kerja Sama',             'weight_pct' => 10, 'order' => 5],
            ['name' => 'Tanggung Jawab',         'weight_pct' => 10, 'order' => 6],
            ['name' => 'Inisiatif',              'weight_pct' => 10, 'order' => 7],
            ['name' => 'Pengembangan Diri',      'weight_pct' => 10, 'order' => 8],
        ];

        // Grade bands untuk weighted_scale (maks 500)
        $gradeBands = [
            ['grade_label' => 'Baik Sekali', 'min_score' => 401],
            ['grade_label' => 'Baik',        'min_score' => 301],
            ['grade_label' => 'Cukup',       'min_score' => 201],
            ['grade_label' => 'Kurang',      'min_score' => 0],
        ];

        foreach ([
            ['level_id' => $staffLevelId,  'name' => 'Template Penilaian Staff'],
            ['level_id' => $seniorLevelId, 'name' => 'Template Penilaian Senior Staff'],
        ] as $tpl) {
            $templateId = DB::table('appraisal_templates')->insertGetId([
                'level_id'     => $tpl['level_id'],
                'name'         => $tpl['name'],
                'scoring_type' => 'weighted_scale',
                'is_default'   => false,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            foreach ($factors as $factor) {
                DB::table('appraisal_aspects')->insert([
                    'appraisal_template_id' => $templateId,
                    'name'                  => $factor['name'],
                    'weight_pct'            => $factor['weight_pct'],
                    'order'                 => $factor['order'],
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);
            }

            foreach ($gradeBands as $band) {
                DB::table('appraisal_grade_bands')->insert([
                    'appraisal_template_id' => $templateId,
                    'grade_label'           => $band['grade_label'],
                    'min_score'             => $band['min_score'],
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('appraisal_templates')
            ->whereIn('name', ['Template Penilaian Staff', 'Template Penilaian Senior Staff'])
            ->each(function ($t) {
                $aspects = DB::table('appraisal_aspects')->where('appraisal_template_id', $t->id)->pluck('id');
                DB::table('appraisal_aspect_weights')->whereIn('appraisal_aspect_id', $aspects)->delete();
                DB::table('appraisal_grade_bands')->where('appraisal_template_id', $t->id)->delete();
                DB::table('appraisal_aspects')->where('appraisal_template_id', $t->id)->delete();
                DB::table('appraisal_templates')->where('id', $t->id)->delete();
            });
    }
};
