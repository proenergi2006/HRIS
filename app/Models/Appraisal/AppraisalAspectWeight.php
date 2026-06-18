<?php

namespace App\Models\Appraisal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppraisalAspectWeight extends Model
{
    protected $fillable = ['appraisal_aspect_id', 'rating', 'score'];

    protected $casts = ['score' => 'integer'];

    public function aspect(): BelongsTo
    {
        return $this->belongsTo(AppraisalAspect::class, 'appraisal_aspect_id');
    }
}
