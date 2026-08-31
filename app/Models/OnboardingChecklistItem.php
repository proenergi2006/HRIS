<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnboardingChecklistItem extends Model
{
    protected $fillable = [
        'company_id', 'label', 'description',
        'material_path', 'material_original_name', 'material_url',
        'category', 'is_required', 'requires_acknowledgement', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_required'              => 'boolean',
        'requires_acknowledgement' => 'boolean',
        'is_active'                => 'boolean',
    ];

    public static array $categoryLabels = [
        'dokumen'   => 'Dokumen',
        'akun'      => 'Akun & Akses',
        'aset'      => 'Aset',
        'induction' => 'Induction',
    ];

    public function hasMaterial(): bool
    {
        return (bool) ($this->material_path || $this->material_url);
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function tasks(): HasMany { return $this->hasMany(EmployeeOnboardingTask::class); }
}
