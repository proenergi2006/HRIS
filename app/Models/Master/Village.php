<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Kelurahan / Desa (master wilayah — PRD Bab 5.1). */
class Village extends Model
{
    protected $fillable = ['district_id', 'code', 'name', 'type', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function getDisplayNameAttribute(): string
    {
        $prefix = $this->type === 'desa' ? 'Desa' : 'Kel.';

        return "{$prefix} {$this->name}";
    }
}
