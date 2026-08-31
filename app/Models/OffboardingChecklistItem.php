<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OffboardingChecklistItem extends Model
{
    protected $fillable = ['company_id', 'label', 'category', 'is_required', 'sort_order', 'is_active'];

    protected $casts = ['is_required' => 'boolean', 'is_active' => 'boolean'];

    public static array $categoryLabels = [
        'aset'     => 'Aset',
        'akun'     => 'Akun & Akses',
        'dokumen'  => 'Dokumen',
        'keuangan' => 'Keuangan',
        'exit'     => 'Exit Process',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
