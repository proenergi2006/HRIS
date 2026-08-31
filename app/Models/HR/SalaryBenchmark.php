<?php

namespace App\Models\HR;

use App\Models\Level;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryBenchmark extends Model
{
    protected $fillable = [
        'level_id', 'market_min', 'market_mid', 'market_max',
        'source', 'notes', 'updated_by_user_id',
    ];

    protected $casts = [
        'market_min' => 'integer',
        'market_mid' => 'integer',
        'market_max' => 'integer',
    ];

    public function level(): BelongsTo     { return $this->belongsTo(Level::class); }
    public function updatedBy(): BelongsTo { return $this->belongsTo(User::class, 'updated_by_user_id'); }

    /** Rasio kompa (posisi gaji aktual thd titik tengah pasar), dalam persen. */
    public function compaRatio(int $currentAmount): ?float
    {
        if ($this->market_mid <= 0) {
            return null;
        }

        return round($currentAmount / $this->market_mid * 100, 1);
    }
}
