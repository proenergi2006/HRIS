<?php

namespace App\Models\HR;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = [
        'company_id', 'code', 'name', 'start_time', 'end_time',
        'break_minutes', 'crosses_midnight', 'late_grace_minutes', 'is_active',
    ];

    protected $casts = [
        'crosses_midnight' => 'boolean',
        'is_active'        => 'boolean',
    ];

    public function company(): BelongsTo    { return $this->belongsTo(Company::class); }
    public function rosterEntries(): HasMany { return $this->hasMany(RosterEntry::class); }

    public function startMinutes(): int
    {
        [$h, $m] = explode(':', $this->start_time);
        return (int) $h * 60 + (int) $m;
    }

    public function endMinutes(): int
    {
        [$h, $m] = explode(':', $this->end_time);
        $mins = (int) $h * 60 + (int) $m;
        return $this->crosses_midnight ? $mins + 1440 : $mins;
    }
}
