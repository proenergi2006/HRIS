<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeWorkExperience extends Model
{
    protected $fillable = [
        'employee_id', 'company_name', 'company_city', 'phone',
        'start_date', 'end_date', 'end_job_title', 'end_pay_rate',
        'job_description', 'remarks',
    ];

    protected $casts = [
        'start_date'   => 'date',
        'end_date'     => 'date',
        'end_pay_rate' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
