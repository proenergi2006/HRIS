<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    protected $fillable = [
        'company_id', 'department_id', 'section_id', 'branch_id', 'level_id', 'reports_to_position_id',
        'code', 'name', 'job_description',
        'tunjangan_jabatan', 'tunjangan_harian', 'tarif_lembur', 'is_active',
        'is_critical_position', 'succession_risk', 'succession_notes',
    ];

    protected $casts = [
        'is_active'             => 'boolean',
        'tunjangan_jabatan'     => 'integer',
        'tunjangan_harian'      => 'integer',
        'tarif_lembur'          => 'integer',
        'is_critical_position'  => 'boolean',
    ];

    public static array $successionRiskLabels = [
        'low'    => 'Rendah',
        'medium' => 'Sedang',
        'high'   => 'Tinggi',
    ];

    public static array $successionRiskBadges = [
        'low'    => 'success',
        'medium' => 'warning',
        'high'   => 'danger',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function branchLocation(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function reportsTo(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'reports_to_position_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Position::class, 'reports_to_position_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function competencyRequirements(): HasMany
    {
        return $this->hasMany(\App\Models\Competency\PositionCompetency::class);
    }

    public function talentPool(): HasMany
    {
        return $this->hasMany(\App\Models\HR\TalentPoolMember::class);
    }
}
