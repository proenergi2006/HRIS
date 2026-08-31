<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingChecklistItem extends Model
{
    protected $fillable = ['company_id', 'label', 'category', 'is_required', 'sort_order', 'is_active'];

    protected $casts = ['is_required' => 'boolean', 'is_active' => 'boolean'];

    public static array $categoryLabels = [
        'dokumen'   => 'Dokumen',
        'akun'      => 'Akun & Akses',
        'aset'      => 'Aset',
        'induction' => 'Induction',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
