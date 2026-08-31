<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeNssf extends Model
{
    protected $table = 'employee_nssf';

    protected $fillable = [
        'employee_id',
        'health_registered', 'health_number', 'health_join_date',
        'employment_registered', 'employment_number', 'employment_join_date',
    ];

    protected $casts = [
        'health_registered'     => 'boolean',
        'employment_registered' => 'boolean',
        'health_join_date'      => 'date',
        'employment_join_date'  => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
