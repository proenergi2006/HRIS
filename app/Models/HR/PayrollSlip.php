<?php

namespace App\Models\HR;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollSlip extends Model
{
    protected $fillable = [
        'payroll_period_id', 'employee_id',
        'working_days', 'attendance_days', 'leave_days', 'alpha_days', 'late_minutes',
        'total_allowances', 'total_deductions', 'gross_salary', 'net_salary', 'notes',
    ];

    protected $casts = ['leave_days' => 'decimal:1'];

    public function period(): BelongsTo   { return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id'); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function details(): HasMany    { return $this->hasMany(PayrollSlipDetail::class); }
}
