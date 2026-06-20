<?php

namespace App\Models\Reimbursement;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReimbursementBalance extends Model
{
    protected $fillable = ['user_id', 'period_year', 'balance_type', 'initial_balance', 'used_balance'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRemainingBalanceAttribute(): int
    {
        return max(0, $this->initial_balance - $this->used_balance);
    }

    public static function forUser(int $userId, int $year = null, string $type = 'medical'): ?self
    {
        return static::where('user_id', $userId)
            ->where('period_year', $year ?? now()->year)
            ->where('balance_type', $type)
            ->first();
    }
}
