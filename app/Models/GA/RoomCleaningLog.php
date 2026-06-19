<?php

namespace App\Models\GA;

use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomCleaningLog extends Model
{
    use HasHashid;

    protected $fillable = ['room_id', 'cleaner_name', 'cleaned_at'];
    protected $casts = ['cleaned_at' => 'datetime'];

    public function room(): BelongsTo
    {
        return $this->belongsTo(MeetingRoom::class, 'room_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(RoomCleaningLogDetail::class, 'log_id');
    }
}
