<?php

namespace App\Models\GA;

use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeetingRoom extends Model
{
    use HasHashid;

    protected $fillable = ['name', 'location', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function cleaningItems(): HasMany
    {
        return $this->hasMany(RoomCleaningItem::class, 'room_id')->orderBy('order');
    }

    public function cleaningLogs(): HasMany
    {
        return $this->hasMany(RoomCleaningLog::class, 'room_id');
    }
}
