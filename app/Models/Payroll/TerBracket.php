<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TerBracket extends Model
{
    protected $fillable = ['ter_category_id', 'income_from', 'income_to', 'rate_percent'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(TerCategory::class, 'ter_category_id');
    }
}
