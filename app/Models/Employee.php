<?php

namespace App\Models;

use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasHashid;

    protected $fillable = [
        'user_id',
        'level_id',
        'name',
        'nip',
        'lob',
        'department',
        'position',
        'start_date',
        'employment_status',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'is_active'  => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function appraisals(): HasMany
    {
        return $this->hasMany(Appraisal\Appraisal::class);
    }

    public function getEmploymentStatusLabelAttribute(): string
    {
        return match($this->employment_status) {
            'permanent'  => 'Tetap',
            'contract'   => 'Kontrak',
            'probation'  => 'Probation',
            default      => $this->employment_status,
        };
    }
}
