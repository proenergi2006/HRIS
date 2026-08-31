<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanInstallment extends Model
{
    protected $fillable = [
        'employee_loan_id', 'payroll_slip_id', 'period_month', 'period_year',
        'amount', 'status', 'deducted_at',
    ];

    protected $casts = ['deducted_at' => 'datetime'];

    public function loan(): BelongsTo { return $this->belongsTo(EmployeeLoan::class, 'employee_loan_id'); }
    public function slip(): BelongsTo { return $this->belongsTo(PayrollSlip::class, 'payroll_slip_id'); }
}
