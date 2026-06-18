<?php

namespace App\Models\Appraisal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppraisalGradeBand extends Model
{
    protected $fillable = ['appraisal_template_id', 'grade_label', 'min_score', 'order'];

    protected $casts = ['min_score' => 'integer'];

    public function template(): BelongsTo
    {
        return $this->belongsTo(AppraisalTemplate::class, 'appraisal_template_id');
    }
}
