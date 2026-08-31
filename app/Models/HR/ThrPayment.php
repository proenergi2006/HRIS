<?php

namespace App\Models\HR;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThrPayment extends Model
{
    protected $fillable = [
        'thr_period_id', 'employee_id', 'base_salary',
        'months_worked', 'proration_ratio', 'thr_amount', 'notes',
    ];

    public function period(): BelongsTo   { return $this->belongsTo(ThrPeriod::class, 'thr_period_id'); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
}
