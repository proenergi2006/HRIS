<?php

namespace App\Models\Appraisal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** KPI starter/default yang dicopy ke tiap Appraisal baru dibuat dari template ini. */
class AppraisalTemplateObjective extends Model
{
    protected $fillable = ['appraisal_template_id', 'title', 'description', 'category', 'weight_pct', 'order'];

    public function template(): BelongsTo
    {
        return $this->belongsTo(AppraisalTemplate::class, 'appraisal_template_id');
    }
}
