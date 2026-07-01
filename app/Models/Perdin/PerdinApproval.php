<?php

namespace App\Models\Perdin;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerdinApproval extends Model
{
    protected $fillable = [
        'perdin_request_id', 'approver_user_id', 'role', 'action', 'notes', 'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public static array $roleLabels = [
        'direct_manager' => 'Atasan Langsung',
        'hr_manager'     => 'Manager HR & GA',
        'ceo'            => 'Direktur Utama',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(PerdinRequest::class, 'perdin_request_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }
}
