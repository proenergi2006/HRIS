<?php

namespace App\Models\HR;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonusPayment extends Model
{
    protected $fillable = [
        'bonus_period_id', 'employee_id', 'base_amount',
        'gross_amount', 'tax_amount', 'net_amount', 'notes',
    ];

    public function period(): BelongsTo   { return $this->belongsTo(BonusPeriod::class, 'bonus_period_id'); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
}
