<?php

namespace App\Models\Approval;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalDelegation extends Model
{
    protected $fillable = [
        'delegator_user_id', 'delegate_user_id',
        'start_date', 'end_date', 'reason', 'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    public function scopeActiveOn(Builder $query, $date = null): Builder
    {
        $date = $date ?: now()->toDateString();

        return $query->where('is_active', true)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date);
    }

    public function delegator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegator_user_id');
    }

    public function delegate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegate_user_id');
    }

    /** Cari user pengganti yang aktif untuk seorang approver hari ini. */
    public static function delegateFor(int $userId): ?int
    {
        return static::query()->activeOn()
            ->where('delegator_user_id', $userId)
            ->value('delegate_user_id');
    }
}
