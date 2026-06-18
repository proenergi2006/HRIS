<?php

namespace App\Services\Appraisal;

use App\Models\Appraisal\Appraisal;

class ScoreEngine
{
    public static function calculate(Appraisal $appraisal): Appraisal
    {
        $appraisal->load(['template.aspects.weights', 'template.gradeBands', 'items.aspect']);

        if ($appraisal->template->isWeightedScale()) {
            return self::calculateWeightedScale($appraisal);
        }

        return self::calculateFixedPoints($appraisal);
    }

    // ── Fixed Points (SPV/Admin/Manager) ────────────────────────────────────

    private static function calculateFixedPoints(Appraisal $appraisal): Appraisal
    {
        $total = 0;

        foreach ($appraisal->items as $item) {
            $score = $item->rating ? $item->aspect->getScoreForRating($item->rating) : 0;
            $item->update(['score' => $score]);
            $total += $score;
        }

        $grade = null;
        foreach ($appraisal->template->gradeBands as $band) {
            if ($total >= $band->min_score) {
                $grade = $band->grade_label;
                break;
            }
        }

        $appraisal->update(['total_score' => $total, 'grade' => $grade]);

        return $appraisal->refresh();
    }

    // ── Weighted Scale 1-5 (Staff/Senior Staff) ──────────────────────────────

    private static function calculateWeightedScale(Appraisal $appraisal): Appraisal
    {
        $evalTypes  = ['self', 'atasan1', 'atasan2', 'ho'];
        $aspectCount = $appraisal->template->aspects()->count();
        $scores      = array_fill_keys($evalTypes, 0.0);
        $counts      = array_fill_keys($evalTypes, 0);

        foreach ($appraisal->items as $item) {
            if (!$item->rating || !in_array($item->evaluator_type, $evalTypes)) continue;

            $weightPct = $item->aspect->weight_pct ?? 0;
            $score     = (float) $item->rating * $weightPct;
            $item->update(['score' => $score]);

            $scores[$item->evaluator_type] += $score;
            $counts[$item->evaluator_type]++;
        }

        // Hitung skor per penilai (null jika belum semua aspek diisi)
        $perEvaluator = [];
        foreach ($evalTypes as $type) {
            $perEvaluator[$type] = ($counts[$type] >= $aspectCount) ? round($scores[$type], 2) : null;
        }

        // Total = atasan1 sebagai primary (atau rata-rata jika semua diisi)
        $filled = array_filter($perEvaluator, fn($v) => $v !== null);
        $total  = count($filled) > 0 ? round(array_sum($filled) / count($filled), 2) : 0;

        // Grade dari grade bands template
        $grade = null;
        foreach ($appraisal->template->gradeBands as $band) {
            if ($total >= $band->min_score) {
                $grade = $band->grade_label;
                break;
            }
        }

        $appraisal->update([
            'score_self'    => $perEvaluator['self'],
            'score_atasan1' => $perEvaluator['atasan1'],
            'score_atasan2' => $perEvaluator['atasan2'],
            'score_ho'      => $perEvaluator['ho'],
            'total_score'   => $total,
            'grade'         => $grade,
        ]);

        return $appraisal->refresh();
    }
}
