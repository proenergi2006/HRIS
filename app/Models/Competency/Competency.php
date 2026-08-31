<?php

namespace App\Models\Competency;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Kamus kompetensi (Competency Framework — PRD Bab 3 modul #11). */
class Competency extends Model
{
    protected $fillable = ['company_id', 'code', 'name', 'category', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /** Skala level kompetensi global 1-5. */
    public static array $levelLabels = [
        1 => 'Awareness',
        2 => 'Dasar',
        3 => 'Kompeten',
        4 => 'Mahir',
        5 => 'Ahli',
    ];

    public static array $levelBadges = [
        1 => 'secondary',
        2 => 'info',
        3 => 'primary',
        4 => 'success',
        5 => 'success',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function positionCompetencies(): HasMany
    {
        return $this->hasMany(PositionCompetency::class);
    }

    public function employeeCompetencies(): HasMany
    {
        return $this->hasMany(EmployeeCompetency::class);
    }
}
