<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class City extends Model
{
    protected $fillable = ['province_id', 'code', 'name', 'type', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function getDisplayNameAttribute(): string
    {
        $prefix = $this->type === 'kota' ? 'Kota' : 'Kab.';

        return "{$prefix} {$this->name}";
    }
}
