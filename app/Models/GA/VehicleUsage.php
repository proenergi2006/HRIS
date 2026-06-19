<?php

namespace App\Models\GA;

use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleUsage extends Model
{
    use HasHashid;

    protected $fillable = [
        'vehicle_id', 'driver_name', 'driver_phone', 'destination',
        'check_in_at', 'check_out_at', 'km_out',
        'photo_dashboard', 'photo_right', 'photo_left', 'photo_front', 'photo_back',
        'keluhan', 'status',
    ];

    protected $casts = [
        'check_in_at'  => 'datetime',
        'check_out_at' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getDurationAttribute(): string
    {
        if (! $this->check_out_at) return '-';
        $mins = $this->check_in_at->diffInMinutes($this->check_out_at);
        if ($mins < 60) return $mins . ' menit';
        $h = intdiv($mins, 60);
        $m = $mins % 60;
        return $h . ' jam' . ($m ? ' ' . $m . ' menit' : '');
    }
}
