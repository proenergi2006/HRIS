<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeOrgExperience extends Model
{
    protected $fillable = [
        'employee_id', 'company_id', 'position_id',
        'unit_name', 'position_name', 'change_type',
        'start_date', 'end_date', 'remarks',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public static array $changeTypeLabels = [
        'join'      => 'Bergabung',
        'promotion' => 'Promosi',
        'rotation'  => 'Rotasi',
        'mutation'  => 'Mutasi',
        'demotion'  => 'Demosi',
        'other'     => 'Lainnya',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function getChangeTypeLabelAttribute(): string
    {
        return self::$changeTypeLabels[$this->change_type] ?? $this->change_type;
    }
}
