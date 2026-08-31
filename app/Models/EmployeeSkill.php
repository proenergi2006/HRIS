<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSkill extends Model
{
    protected $fillable = ['employee_id', 'name', 'proficiency', 'notes'];

    public static array $proficiencyLabels = [
        'basic'        => 'Dasar',
        'intermediate' => 'Menengah',
        'advanced'     => 'Mahir',
        'expert'       => 'Ahli',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getProficiencyLabelAttribute(): ?string
    {
        return self::$proficiencyLabels[$this->proficiency] ?? null;
    }
}
