<?php

namespace App\Models;

use App\Contracts\Approvable;
use App\Traits\HasApprovalWorkflow;
use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class JobRequisition extends Model implements Approvable
{
    use HasHashid, LogsActivity, HasApprovalWorkflow;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'headcount_requested', 'notes_rejection'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('job_requisition');
    }

    protected $fillable = [
        'company_id', 'department_id', 'section_id', 'position_id',
        'title', 'reason', 'headcount_requested', 'employment_type_id', 'target_join_date',
        'status', 'requested_by_user_id', 'notes_rejection',
    ];

    protected $casts = ['target_join_date' => 'date'];

    public static array $statusLabels = [
        'draft'     => 'Draft',
        'pending'   => 'Menunggu Persetujuan',
        'approved'  => 'Disetujui',
        'rejected'  => 'Ditolak',
        'cancelled' => 'Dibatalkan',
        'closed'    => 'Selesai (Terisi)',
    ];

    public static array $statusBadges = [
        'draft'     => 'secondary',
        'pending'   => 'warning',
        'approved'  => 'success',
        'rejected'  => 'danger',
        'cancelled' => 'secondary',
        'closed'    => 'primary',
    ];

    public function company(): BelongsTo     { return $this->belongsTo(Company::class); }
    public function department(): BelongsTo  { return $this->belongsTo(Department::class); }
    public function section(): BelongsTo     { return $this->belongsTo(Section::class); }
    public function position(): BelongsTo    { return $this->belongsTo(Position::class); }
    public function employmentType(): BelongsTo { return $this->belongsTo(\App\Models\Master\EmployeeType::class, 'employment_type_id'); }
    public function requestedBy(): BelongsTo { return $this->belongsTo(User::class, 'requested_by_user_id'); }
    public function candidates(): HasMany    { return $this->hasMany(Candidate::class); }

    public function isDraft(): bool    { return $this->status === 'draft'; }
    public function isEditable(): bool { return in_array($this->status, ['draft', 'rejected']); }
    public function isOpen(): bool     { return $this->status === 'approved'; }

    public function scopeLabel(): string
    {
        return $this->position?->name
            ?? $this->section?->name
            ?? $this->department?->name
            ?? $this->company?->short_name
            ?? $this->company?->name
            ?? '-';
    }

    // ── Approval Engine ─────────────────────────────────────────────────

    public function approvalTransactionType(): string { return 'job_requisition'; }
    public function approvalCompanyId(): ?int { return $this->company_id; }
    public function approvalRequester(): ?User { return $this->requestedBy; }
    public function approvalSubjectEmployee(): ?Employee { return $this->requestedBy?->employee; }

    public function approvalAttributes(): array
    {
        return [
            'headcount_requested' => (int) $this->headcount_requested,
            'department_id'       => $this->department_id,
        ];
    }

    public function approvalSummary(): string
    {
        return 'Requisition — ' . $this->title . ' · ' . $this->scopeLabel()
            . ' · ' . $this->headcount_requested . ' orang';
    }

    public function onApprovalApproved(): void
    {
        $this->forceFill(['status' => 'approved'])->save();
    }

    public function onApprovalRejected(?string $reason): void
    {
        $this->forceFill(['status' => 'rejected', 'notes_rejection' => $reason])->save();
    }
}
