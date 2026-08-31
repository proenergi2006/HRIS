<?php

namespace App\Traits;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Implementasi standar App\Contracts\Approvable untuk transaksi HR yang
 * berbentuk "pengajuan atas seorang karyawan" (Reward, Punishment,
 * Promosi/Rotasi, Termination). Model tetap harus:
 *   - use HasApprovalWorkflow
 *   - punya kolom: employee_id, company_id, requested_by_user_id, status, notes
 */
trait AsApprovableRequest
{
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function approvalCompanyId(): ?int
    {
        return $this->company_id ?: $this->employee?->company_id;
    }

    public function approvalRequester(): ?User
    {
        return $this->requestedBy;
    }

    public function approvalSubjectEmployee(): ?Employee
    {
        return $this->employee;
    }

    public function approvalAttributes(): array
    {
        return $this->attributesToArray();
    }

    public function onApprovalApproved(): void
    {
        $this->forceFill(['status' => 'approved'])->save();
    }

    public function onApprovalRejected(?string $reason): void
    {
        $this->forceFill([
            'status' => 'rejected',
            'notes'  => trim(($this->notes ? $this->notes . "\n" : '') . 'Ditolak: ' . $reason),
        ])->save();
    }
}
