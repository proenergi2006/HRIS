<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TerCategory extends Model
{
    protected $fillable = ['code', 'name', 'description'];

    public function brackets(): HasMany
    {
        return $this->hasMany(TerBracket::class)->orderBy('income_from');
    }
}
