<?php

namespace App\Models\Appraisal;

use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppraisalPeriod extends Model
{
    use HasHashid;

    protected $fillable = ['name', 'year', 'start_date', 'end_date', 'status'];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function appraisals(): HasMany
    {
        return $this->hasMany(Appraisal::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
