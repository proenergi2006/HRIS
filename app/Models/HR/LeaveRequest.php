<?php

namespace App\Models\HR;

use App\Contracts\Approvable;
use App\Models\Employee;
use App\Models\User;
use App\Traits\HasApprovalWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LeaveRequest extends Model implements Approvable
{
    use LogsActivity, HasApprovalWorkflow;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'total_days', 'manager_notes', 'hr_notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('leave');
    }

    protected $fillable = [
        'employee_id', 'leave_type_id', 'start_date', 'end_date', 'total_days',
        'reason', 'attachment_path', 'status',
        'manager_approved_by', 'manager_approved_at', 'manager_notes',
        'hr_approved_by', 'hr_approved_at', 'hr_notes',
    ];

    protected $casts = [
        'start_date'          => 'date',
        'end_date'            => 'date',
        'manager_approved_at' => 'datetime',
        'hr_approved_at'      => 'datetime',
        'total_days'          => 'decimal:1',
    ];

    public static array $statusLabels = [
        'draft'            => 'Draft',
        'pending'          => 'Menunggu Persetujuan',
        'approved'         => 'Disetujui',
        'rejected'         => 'Ditolak',
        'cancelled'        => 'Dibatalkan',
        // nilai lama (baris histori sebelum migrasi ke engine)
        'submitted'        => 'Menunggu Persetujuan',
        'approved_manager' => 'Menunggu Persetujuan',
        'approved_hr'      => 'Disetujui',
    ];

    public static array $statusBadges = [
        'draft'            => 'secondary',
        'pending'          => 'warning',
        'approved'         => 'success',
        'rejected'         => 'danger',
        'cancelled'        => 'secondary',
        'submitted'        => 'warning',
        'approved_manager' => 'info',
        'approved_hr'      => 'success',
    ];

    public function isDraft(): bool     { return $this->status === 'draft'; }
    public function isPending(): bool   { return in_array($this->status, ['pending', 'submitted', 'approved_manager']); }
    public function isApproved(): bool  { return in_array($this->status, ['approved', 'approved_hr']); }
    public function isRejected(): bool  { return $this->status === 'rejected'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }

    // Kompatibilitas dgn kode/tampilan lama
    public function isSubmitted(): bool       { return $this->isPending(); }
    public function isApprovedManager(): bool { return false; }

    public function employee(): BelongsTo    { return $this->belongsTo(Employee::class); }
    public function leaveType(): BelongsTo   { return $this->belongsTo(LeaveType::class); }
    public function managerApprover(): BelongsTo { return $this->belongsTo(User::class, 'manager_approved_by'); }
    public function hrApprover(): BelongsTo      { return $this->belongsTo(User::class, 'hr_approved_by'); }

    // ── Approval Engine ─────────────────────────────────────────────────

    public function approvalTransactionType(): string
    {
        return 'leave_request';
    }

    public function approvalCompanyId(): ?int
    {
        return $this->employee?->company_id;
    }

    public function approvalRequester(): ?User
    {
        return $this->employee?->user;
    }

    public function approvalSubjectEmployee(): ?Employee
    {
        return $this->employee;
    }

    public function approvalAttributes(): array
    {
        return [
            'total_days'    => (float) $this->total_days,
            'leave_type_id' => $this->leave_type_id,
            'is_paid'       => (int) ($this->leaveType?->is_paid ?? 1),
        ];
    }

    public function approvalSummary(): string
    {
        $days = rtrim(rtrim(number_format((float) $this->total_days, 1), '0'), '.');

        return 'Cuti ' . ($this->leaveType?->name ?? '')
            . ' — ' . ($this->employee?->name ?? '')
            . ' · ' . $this->start_date?->format('d/m') . '–' . $this->end_date?->format('d/m/Y')
            . ' (' . $days . ' hari)';
    }

    public function onApprovalApproved(): void
    {
        // Potong saldo cuti (idempoten — hanya kalau belum di-approve).
        if ($this->isApproved()) {
            return;
        }

        $balance = LeaveBalance::forEmployee($this->employee_id, $this->leave_type_id, $this->start_date->year);
        $balance->increment('used', $this->total_days);

        $this->forceFill([
            'status'         => 'approved',
            'hr_approved_at' => now(),
        ])->save();
    }

    public function onApprovalRejected(?string $reason): void
    {
        $this->forceFill([
            'status'   => 'rejected',
            'hr_notes' => $reason,
        ])->save();
    }
}
