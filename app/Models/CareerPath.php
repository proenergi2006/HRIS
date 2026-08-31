<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CareerPath extends Model
{
    protected $fillable = ['company_id', 'title', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function steps(): HasMany     { return $this->hasMany(CareerPathStep::class)->orderBy('step_order'); }
    public function employees(): HasMany { return $this->hasMany(Employee::class); }
}
