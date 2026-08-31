<?php

namespace App\Models\Appraisal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Satu baris KPI/objective dalam sebuah penilaian (target, realisasi, skor). */
class AppraisalObjective extends Model
{
    protected $fillable = [
        'appraisal_id', 'company_objective_id', 'title', 'description', 'category', 'weight_pct',
        'target', 'actual', 'achievement_pct', 'score', 'order',
    ];

    protected $casts = [
        'achievement_pct' => 'decimal:2',
        'score'           => 'decimal:2',
    ];

    public function appraisal(): BelongsTo
    {
        return $this->belongsTo(Appraisal::class);
    }

    public function companyObjective(): BelongsTo
    {
        return $this->belongsTo(CompanyObjective::class);
    }

    /** score = bobot% x capaian% / 100 — dipanggil ulang tiap kali objective disimpan. */
    public function recalculateScore(): void
    {
        $this->score = $this->achievement_pct !== null
            ? round($this->weight_pct * (float) $this->achievement_pct / 100, 2)
            : null;
    }
}
