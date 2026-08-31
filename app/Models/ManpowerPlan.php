<?php

namespace App\Models;

use App\Contracts\Approvable;
use App\Traits\HasApprovalWorkflow;
use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ManpowerPlan extends Model implements Approvable
{
    use HasHashid, LogsActivity, HasApprovalWorkflow;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'planned_headcount', 'notes_rejection'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('manpower_plan');
    }

    protected $fillable = [
        'company_id', 'department_id', 'section_id', 'position_id',
        'year', 'month', 'planned_headcount', 'notes',
        'status', 'requested_by_user_id', 'notes_rejection',
    ];

    public static array $statusLabels = [
        'draft'     => 'Draft',
        'pending'   => 'Menunggu Persetujuan',
        'approved'  => 'Disetujui',
        'rejected'  => 'Ditolak',
        'cancelled' => 'Dibatalkan',
    ];

    public static array $statusBadges = [
        'draft'     => 'secondary',
        'pending'   => 'warning',
        'approved'  => 'success',
        'rejected'  => 'danger',
        'cancelled' => 'secondary',
    ];

    public function company(): BelongsTo      { return $this->belongsTo(Company::class); }
    public function department(): BelongsTo   { return $this->belongsTo(Department::class); }
    public function section(): BelongsTo      { return $this->belongsTo(Section::class); }
    public function position(): BelongsTo     { return $this->belongsTo(Position::class); }
    public function requestedBy(): BelongsTo  { return $this->belongsTo(User::class, 'requested_by_user_id'); }

    public function isDraft(): bool     { return $this->status === 'draft'; }
    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isEditable(): bool  { return in_array($this->status, ['draft', 'rejected']); }

    /** Nama unit target rencana ini (paling spesifik yang terisi). */
    public function scopeLabel(): string
    {
        return $this->position?->name
            ?? $this->section?->name
            ?? $this->department?->name
            ?? $this->company?->short_name
            ?? $this->company?->name
            ?? '-';
    }

    /** Periode dalam format "Agustus 2026" atau "2026" (rencana tahunan). */
    public function periodLabel(): string
    {
        return $this->month
            ? \Carbon\Carbon::create($this->year, $this->month, 1)->translatedFormat('F Y')
            : (string) $this->year;
    }

    /**
     * Headcount aktual — dihitung LIVE dari Employee aktif pada scope yang sama
     * (bukan kolom tersimpan, konsisten dengan pola vacant-position di Org Chart).
     */
    public function actualHeadcount(): int
    {
        $query = Employee::where('company_id', $this->company_id)->where('is_active', true);

        if ($this->position_id) {
            $query->where('position_id', $this->position_id);
        } elseif ($this->section_id) {
            $query->where('section_id', $this->section_id);
        } elseif ($this->department_id) {
            $query->where('department_id', $this->department_id);
        }

        return $query->count();
    }

    // ── Approval Engine ─────────────────────────────────────────────────

    public function approvalTransactionType(): string
    {
        return 'manpower_plan_request';
    }

    public function approvalCompanyId(): ?int
    {
        return $this->company_id;
    }

    public function approvalRequester(): ?User
    {
        return $this->requestedBy;
    }

    /** Subjek approval = karyawan dari user yang mengajukan (utk resolve atasan langsung). */
    public function approvalSubjectEmployee(): ?Employee
    {
        return $this->requestedBy?->employee;
    }

    public function approvalAttributes(): array
    {
        return [
            'planned_headcount' => (int) $this->planned_headcount,
            'department_id'     => $this->department_id,
        ];
    }

    public function approvalSummary(): string
    {
        return 'Manpower Plan — ' . $this->scopeLabel() . ' · ' . $this->periodLabel()
            . ' · ' . $this->planned_headcount . ' orang';
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
