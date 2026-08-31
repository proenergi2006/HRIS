<?php

namespace App\Models\HR;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BonusPeriod extends Model
{
    protected $fillable = [
        'company_id', 'name', 'bonus_type', 'payment_date', 'is_taxable',
        'status', 'closed_by', 'closed_at',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'is_taxable'   => 'boolean',
        'closed_at'    => 'datetime',
    ];

    public static array $typeLabels = [
        'bonus'       => 'Bonus',
        'insentif'    => 'Insentif',
        'thr_susulan' => 'THR Susulan',
        'other'       => 'Lainnya',
    ];

    public function company(): BelongsTo  { return $this->belongsTo(Company::class); }
    public function closedBy(): BelongsTo { return $this->belongsTo(User::class, 'closed_by'); }
    public function payments(): HasMany   { return $this->hasMany(BonusPayment::class); }

    public function isClosed(): bool { return $this->status === 'closed'; }

    public function getTypeLabelAttribute(): string
    {
        return self::$typeLabels[$this->bonus_type] ?? $this->bonus_type;
    }
}
