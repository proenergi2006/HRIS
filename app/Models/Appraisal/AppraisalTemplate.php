<?php

namespace App\Models\Appraisal;

use App\Models\Level;
use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppraisalTemplate extends Model
{
    use HasHashid;

    protected $fillable = ['level_id', 'name', 'is_default'];

    protected $casts = ['is_default' => 'boolean'];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(AppraisalTemplateObjective::class)->orderBy('order');
    }

    public function gradeBands(): HasMany
    {
        return $this->hasMany(AppraisalGradeBand::class)->orderByDesc('min_score');
    }

    public function appraisals(): HasMany
    {
        return $this->hasMany(Appraisal::class);
    }
}
