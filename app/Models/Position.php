<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    protected $fillable = ['company_id', 'department_id', 'code', 'name', 'tunjangan_jabatan', 'tunjangan_harian', 'tarif_lembur', 'is_active'];
    protected $casts    = [
        'is_active'         => 'boolean',
        'tunjangan_jabatan' => 'integer',
        'tunjangan_harian'  => 'integer',
        'tarif_lembur'      => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
