<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFacility extends Model
{
    protected $fillable = [
        'employee_id', 'name', 'description',
        'received_date', 'returned_date', 'remarks',
    ];

    protected $casts = [
        'received_date' => 'date',
        'returned_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
