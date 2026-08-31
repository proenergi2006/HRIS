<?php

namespace App\Models;

use App\Models\Master\EducationLevel;
use App\Models\Master\EducationMajor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeEducation extends Model
{
    protected $table = 'employee_educations';

    protected $fillable = [
        'employee_id', 'education_level_id', 'education_major_id',
        'institution', 'graduation_year', 'gpa', 'notes',
    ];

    protected $casts = [
        'graduation_year' => 'integer',
        'gpa'             => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(EducationLevel::class, 'education_level_id');
    }

    public function major(): BelongsTo
    {
        return $this->belongsTo(EducationMajor::class, 'education_major_id');
    }
}
