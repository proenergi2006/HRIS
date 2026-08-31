<?php

namespace App\Models;

use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    use HasHashid;

    protected $fillable = ['name', 'description', 'rank'];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function appraisalTemplates(): HasMany
    {
        return $this->hasMany(Appraisal\AppraisalTemplate::class);
    }

    public function benchmark(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\HR\SalaryBenchmark::class);
    }
}
