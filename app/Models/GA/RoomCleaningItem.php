<?php

namespace App\Models\GA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomCleaningItem extends Model
{
    protected $fillable = ['room_id', 'name', 'order'];

    public function room(): BelongsTo
    {
        return $this->belongsTo(MeetingRoom::class, 'room_id');
    }

    public function logDetails(): HasMany
    {
        return $this->hasMany(RoomCleaningLogDetail::class, 'item_id');
    }
}
