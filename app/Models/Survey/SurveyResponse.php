<?php

namespace App\Models\Survey;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyResponse extends Model
{
    protected $fillable = ['survey_id', 'user_id', 'employee_id', 'company_id', 'department_id', 'submitted_at'];

    protected $casts = ['submitted_at' => 'datetime'];

    public function survey(): BelongsTo   { return $this->belongsTo(Survey::class); }
    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function company(): BelongsTo  { return $this->belongsTo(Company::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function answers(): HasMany    { return $this->hasMany(SurveyAnswer::class); }
}
