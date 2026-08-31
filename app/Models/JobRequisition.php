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
        'title', 'request_type', 'manpower_plan_id', 'replaces_employee_id',
        'reason', 'headcount_requested', 'employment_type_id', 'target_join_date',
        'status', 'requested_by_user_id', 'notes_rejection',
    ];

    protected $casts = ['target_join_date' => 'date'];

    public static array $typeLabels = [
        'replacement'  => 'Pengganti (Replacement)',
        'additional'   => 'Tambahan (Additional)',
        'new_position' => 'Posisi Baru (New Position)',
    ];

    /** Tipe yang menambah headcount di luar kondisi sekarang -> wajib ada kuota MPP. */
    public const BUDGETED_TYPES = ['additional', 'new_position'];

    public function needsBudget(): bool
    {
        return in_array($this->request_type, self::BUDGETED_TYPES, true);
    }

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
    public function manpowerPlan(): BelongsTo { return $this->belongsTo(ManpowerPlan::class); }
    public function replacesEmployee(): BelongsTo { return $this->belongsTo(Employee::class, 'replaces_employee_id'); }
    public function candidates(): HasMany    { return $this->hasMany(Candidate::class); }

    public function isDraft(): bool    { return $this->status === 'draft'; }
    public function isEditable(): bool { return in_array($this->status, ['draft', 'rejected']); }
    public function isOpen(): bool     { return $this->status === 'approved'; }

    /**
     * Budget Control — cek apakah permintaan ini masih muat di kuota Manpower Plan.
     * Return null bila lolos (atau tidak perlu budget), string alasan bila ditolak.
     */
    public function budgetViolation(): ?string
    {
        if (! $this->needsBudget()) {
            return null;
        }

        $plan = $this->manpowerPlan;

        if (! $plan) {
            return 'Permintaan tipe "' . (self::$typeLabels[$this->request_type] ?? $this->request_type)
                . '" wajib ditautkan ke Rencana Manpower (MPP) yang sudah disetujui.';
        }

        if (! $plan->isApproved()) {
            return 'Rencana Manpower yang dipilih belum berstatus "Disetujui".';
        }

        $remaining = $plan->remainingBudget($this->id);

        if ($this->headcount_requested > $remaining) {
            return 'Kuota Rencana Manpower tidak cukup. Sisa kuota: ' . $remaining
                . ' orang (rencana ' . $plan->planned_headcount . ' − aktual ' . $plan->actualHeadcount()
                . ' − sedang direkrut ' . $plan->committedHeadcount($this->id)
                . '), permintaan ' . $this->headcount_requested . ' orang.';
        }

        return null;
    }

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
