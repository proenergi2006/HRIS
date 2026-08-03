<?php

namespace App\Models\HR;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryComponent extends Model
{
    protected $fillable = [
        'company_id', 'name', 'type', 'calculation_type', 'rate_percent', 'salary_cap',
        'is_taxable', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_taxable'   => 'boolean',
        'is_active'    => 'boolean',
        'rate_percent' => 'float',
        'salary_cap'   => 'integer',
    ];

    public function isManual(): bool
    {
        return $this->calculation_type === 'manual';
    }

    public function company(): BelongsTo     { return $this->belongsTo(Company::class); }
    public function employeeValues(): HasMany { return $this->hasMany(EmployeeSalaryComponent::class); }
}
