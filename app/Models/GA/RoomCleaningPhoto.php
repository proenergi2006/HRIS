<?php

namespace App\Models\GA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomCleaningPhoto extends Model
{
    protected $fillable = ['detail_id', 'path'];

    public function detail(): BelongsTo
    {
        return $this->belongsTo(RoomCleaningLogDetail::class, 'detail_id');
    }
}
