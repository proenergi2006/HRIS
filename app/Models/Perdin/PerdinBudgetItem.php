<?php

namespace App\Models\Perdin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerdinBudgetItem extends Model
{
    protected $fillable = [
        'perdin_request_id', 'category', 'item_name', 'handled_by',
        'qty', 'unit_cost', 'total_cost',
    ];

    protected static function booted(): void
    {
        static::saving(function (PerdinBudgetItem $item) {
            $item->total_cost = (int) $item->qty * (int) $item->unit_cost;
        });
    }

    public function isByGa(): bool { return $this->handled_by === 'ga'; }

    public function request(): BelongsTo
    {
        return $this->belongsTo(PerdinRequest::class, 'perdin_request_id');
    }
}
