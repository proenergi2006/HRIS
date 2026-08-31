<?php

namespace App\Models\Approval;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRequestStep extends Model
{
    protected $fillable = [
        'approval_request_id', 'step_order', 'approver_type', 'approver_label',
        'approver_user_id', 'status', 'acted_by_user_id', 'acted_at', 'notes', 'due_at',
    ];

    protected $casts = [
        'acted_at'   => 'datetime',
        'due_at'     => 'datetime',
        'step_order' => 'integer',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    public function actedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by_user_id');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_at && $this->due_at->isPast();
    }
}
