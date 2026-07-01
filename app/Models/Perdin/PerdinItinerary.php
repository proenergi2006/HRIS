<?php

namespace App\Models\Perdin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerdinItinerary extends Model
{
    protected $table = 'perdin_itinerary';

    protected $fillable = [
        'perdin_request_id', 'no', 'travel_date', 'time_start', 'time_end',
        'timezone', 'description',
    ];

    protected $casts = [
        'travel_date' => 'date',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(PerdinRequest::class, 'perdin_request_id');
    }
}
