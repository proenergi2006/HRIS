<?php

namespace App\Models\Appraisal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppraisalAspect extends Model
{
    protected $fillable = ['appraisal_template_id', 'name', 'order', 'weight_pct'];

    public function template(): BelongsTo
    {
        return $this->belongsTo(AppraisalTemplate::class, 'appraisal_template_id');
    }

    public function weights(): HasMany
    {
        return $this->hasMany(AppraisalAspectWeight::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(AppraisalItem::class);
    }

    public function getScoreForRating(string $rating): int
    {
        $weight = $this->weights->firstWhere('rating', $rating);
        return $weight ? $weight->score : 0;
    }
}
