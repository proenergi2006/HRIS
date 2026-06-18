<?php

namespace App\Models\Appraisal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppraisalItem extends Model
{
    protected $fillable = ['appraisal_id', 'appraisal_aspect_id', 'rating', 'score', 'evaluator_type'];

    protected $casts = ['score' => 'decimal:2'];

    public function appraisal(): BelongsTo
    {
        return $this->belongsTo(Appraisal::class);
    }

    public function aspect(): BelongsTo
    {
        return $this->belongsTo(AppraisalAspect::class, 'appraisal_aspect_id');
    }
}
