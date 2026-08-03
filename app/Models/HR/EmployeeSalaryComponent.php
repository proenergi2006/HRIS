<?php

namespace App\Models\HR;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSalaryComponent extends Model
{
    protected $fillable = ['employee_id', 'salary_component_id', 'amount'];

    public function employee(): BelongsTo        { return $this->belongsTo(Employee::class); }
    public function component(): BelongsTo { return $this->belongsTo(SalaryComponent::class, 'salary_component_id'); }
}
