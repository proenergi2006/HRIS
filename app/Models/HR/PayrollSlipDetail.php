<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollSlipDetail extends Model
{
    protected $fillable = ['payroll_slip_id', 'salary_component_id', 'component_name', 'type', 'amount'];

    public function slip(): BelongsTo      { return $this->belongsTo(PayrollSlip::class, 'payroll_slip_id'); }
    public function component(): BelongsTo { return $this->belongsTo(SalaryComponent::class, 'salary_component_id'); }
}
