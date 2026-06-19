<?php

namespace App\Models\GA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomCleaningLogDetail extends Model
{
    protected $fillable = ['log_id', 'item_id', 'notes'];

    public function log(): BelongsTo
    {
        return $this->belongsTo(RoomCleaningLog::class, 'log_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(RoomCleaningItem::class, 'item_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(RoomCleaningPhoto::class, 'detail_id');
    }
}
