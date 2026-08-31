<?php

namespace App\Models\Appraisal;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** OKR — Sasaran Perusahaan/Departemen. Pohon lewat parent_objective_id, ditautkan ke KPI individu. */
class CompanyObjective extends Model
{
    protected $fillable = [
        'company_id', 'department_id', 'parent_objective_id', 'title', 'description',
        'year', 'quarter', 'owner_employee_id', 'status', 'created_by_user_id',
    ];

    public static array $statusLabels = [
        'active'    => 'Berjalan',
        'completed' => 'Tercapai',
        'cancelled' => 'Dibatalkan',
    ];

    public static array $statusBadges = [
        'active'    => 'primary',
        'completed' => 'success',
        'cancelled' => 'secondary',
    ];

    public function company(): BelongsTo    { return $this->belongsTo(Company::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function owner(): BelongsTo      { return $this->belongsTo(Employee::class, 'owner_employee_id'); }
    public function createdBy(): BelongsTo  { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function parent(): BelongsTo     { return $this->belongsTo(CompanyObjective::class, 'parent_objective_id'); }
    public function children(): HasMany     { return $this->hasMany(CompanyObjective::class, 'parent_objective_id'); }
    /** KPI individu (appraisal_objectives) yang ditautkan ke sasaran ini. */
    public function linkedKpis(): HasMany   { return $this->hasMany(AppraisalObjective::class, 'company_objective_id'); }

    public function periodLabel(): string
    {
        return $this->quarter ? "Q{$this->quarter} {$this->year}" : (string) $this->year;
    }

    /** Rata-rata capaian% dari semua KPI individu yang tertaut (progress turunan bawah ke atas). */
    public function derivedProgress(): ?float
    {
        $kpis = $this->linkedKpis()->whereNotNull('achievement_pct')->get();
        $childProgress = $this->children->map(fn ($c) => $c->derivedProgress())->filter(fn ($v) => $v !== null);

        $all = $kpis->pluck('achievement_pct')->map(fn ($v) => (float) $v)->merge($childProgress);

        return $all->isEmpty() ? null : round($all->avg(), 1);
    }
}
