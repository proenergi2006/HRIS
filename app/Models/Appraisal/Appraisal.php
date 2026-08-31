<?php

namespace App\Models\Appraisal;

use App\Contracts\Approvable;
use App\Models\Employee;
use App\Models\User;
use App\Traits\HasApprovalWorkflow;
use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Penilaian Kinerja — KPI/objective-based (PRD Bab 3 modul #10), lewat Approval
 * Engine generik (bukan lagi state machine 2-step appraisal_flow_configs).
 */
class Appraisal extends Model implements Approvable
{
    use HasHashid, LogsActivity, HasApprovalWorkflow;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'total_score', 'grade'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('appraisal');
    }

    protected $fillable = [
        'employee_id', 'appraisal_period_id', 'appraisal_template_id', 'evaluator_id',
        'status', 'total_score', 'grade', 'notes', 'strengths', 'development_notes',
        'submitted_at', 'finalized_at',
    ];

    protected $casts = [
        'total_score'  => 'decimal:2',
        'submitted_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];

    public static array $statusLabels = [
        'draft'     => 'Draft',
        'pending'   => 'Menunggu Persetujuan',
        'approved'  => 'Final',
        'rejected'  => 'Dikembalikan',
        'cancelled' => 'Dibatalkan',
    ];

    public static array $statusBadges = [
        'draft'     => 'secondary',
        'pending'   => 'warning',
        'approved'  => 'success',
        'rejected'  => 'danger',
        'cancelled' => 'secondary',
    ];

    public function employee(): BelongsTo   { return $this->belongsTo(Employee::class); }
    public function period(): BelongsTo     { return $this->belongsTo(AppraisalPeriod::class, 'appraisal_period_id'); }
    public function template(): BelongsTo   { return $this->belongsTo(AppraisalTemplate::class, 'appraisal_template_id'); }
    public function evaluator(): BelongsTo  { return $this->belongsTo(User::class, 'evaluator_id'); }
    public function objectives(): HasMany   { return $this->hasMany(AppraisalObjective::class)->orderBy('order'); }

    public function isDraft(): bool    { return in_array($this->status, ['draft', 'rejected']); }
    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }

    public function totalWeight(): int
    {
        return (int) $this->objectives->sum('weight_pct');
    }

    /** Semua KPI wajib punya bobot 100% total & sudah diisi capaian sebelum bisa submit. */
    public function isReadyToSubmit(): bool
    {
        if ($this->objectives->isEmpty() || $this->totalWeight() !== 100) {
            return false;
        }

        return $this->objectives->every(fn ($o) => $o->achievement_pct !== null);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::$statusLabels[$this->status] ?? $this->status;
    }

    // ── Approval Engine ─────────────────────────────────────────────────

    public function approvalTransactionType(): string { return 'appraisal'; }
    public function approvalCompanyId(): ?int { return $this->employee?->company_id; }
    public function approvalRequester(): ?User { return $this->evaluator; }
    public function approvalSubjectEmployee(): ?Employee { return $this->employee; }

    public function approvalAttributes(): array
    {
        return [
            'total_score' => (float) $this->total_score,
            'grade'       => $this->grade,
        ];
    }

    public function approvalSummary(): string
    {
        return 'Penilaian Kinerja — ' . ($this->employee?->name ?? '?')
            . ' · ' . ($this->period?->name ?? '')
            . ' · Skor ' . number_format((float) $this->total_score, 1);
    }

    public function onApprovalApproved(): void
    {
        $this->forceFill(['status' => 'approved', 'finalized_at' => now()])->save();
    }

    public function onApprovalRejected(?string $reason): void
    {
        $this->forceFill([
            'status' => 'rejected',
            'notes'  => trim(($this->notes ? $this->notes . "\n" : '') . 'Ditolak: ' . $reason),
        ])->save();
    }
}
