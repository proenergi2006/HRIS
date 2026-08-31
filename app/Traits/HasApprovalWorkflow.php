<?php

namespace App\Traits;

use App\Models\Approval\ApprovalRequest;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Dipakai model transaksi yang di-approve lewat App\Services\ApprovalEngine.
 * Model wajib juga implements App\Contracts\Approvable.
 */
trait HasApprovalWorkflow
{
    public function approvalRequest(): MorphOne
    {
        return $this->morphOne(ApprovalRequest::class, 'approvable')->latestOfMany();
    }

    public function isPendingApproval(): bool
    {
        return $this->approvalRequest?->status === 'pending';
    }

    public function isApprovedByWorkflow(): bool
    {
        return $this->approvalRequest?->status === 'approved';
    }

    public function isRejectedByWorkflow(): bool
    {
        return $this->approvalRequest?->status === 'rejected';
    }
}
