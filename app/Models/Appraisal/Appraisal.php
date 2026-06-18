<?php

namespace App\Models\Appraisal;

use App\Models\Employee;
use App\Models\User;
use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appraisal extends Model
{
    use HasHashid;

    protected $fillable = [
        'employee_id',
        'appraisal_period_id',
        'appraisal_template_id',
        'evaluator_id',
        'status',
        'total_score',
        'score_self',
        'score_atasan1',
        'score_atasan2',
        'score_ho',
        'grade',
        'avg_late_per_month',
        'avg_leave_per_month',
        'warning_letter',
        'sp_level',
        'notes',
        'strength_points',
        'development_need',
        'individual_development_plan',
        'decision',
        'submitted_at',
        'finalized_at',
    ];

    protected $casts = [
        'total_score'        => 'decimal:2',
        'score_self'         => 'decimal:2',
        'score_atasan1'      => 'decimal:2',
        'score_atasan2'      => 'decimal:2',
        'score_ho'           => 'decimal:2',
        'avg_late_per_month' => 'decimal:2',
        'avg_leave_per_month'=> 'decimal:2',
        'warning_letter'     => 'boolean',
        'submitted_at'       => 'datetime',
        'finalized_at'       => 'datetime',
    ];

    // Status constants
    const STATUS_DRAFT         = 'draft';
    const STATUS_SUBMITTED     = 'submitted';
    const STATUS_APPROVED_U2   = 'approved_user2';
    const STATUS_APPROVED_CFO  = 'approved_cfo';
    const STATUS_REJECTED      = 'rejected';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(AppraisalPeriod::class, 'appraisal_period_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AppraisalTemplate::class, 'appraisal_template_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AppraisalItem::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(AppraisalApproval::class)->orderBy('created_at');
    }

    public function isFinal(): bool
    {
        return $this->status === self::STATUS_APPROVED_CFO;
    }

    public function isDraft(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED]);
    }

    public function isComplete(): bool
    {
        $aspectCount = $this->template->aspects()->count();

        if ($this->template->scoring_type === 'weighted_scale') {
            // Minimal Atasan I harus terisi semua aspek
            $filledAtasan1 = $this->items()
                ->where('evaluator_type', 'atasan1')
                ->whereNotNull('rating')
                ->count();
            return $aspectCount > 0 && $filledAtasan1 >= $aspectCount;
        }

        $ratedCount = $this->items()->whereNotNull('rating')->count();
        return $aspectCount > 0 && $ratedCount >= $aspectCount;
    }

    public function isWeightedScale(): bool
    {
        return $this->template->scoring_type === 'weighted_scale';
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status === self::STATUS_SUBMITTED) {
            $cfg = AppraisalFlowConfig::forDepartment($this->employee->department ?? '', 1);
            return 'Menunggu Persetujuan ' . ($cfg?->label ?? 'Atasan');
        }

        if ($this->status === self::STATUS_APPROVED_U2) {
            $cfg = AppraisalFlowConfig::forDepartment($this->employee->department ?? '', 2);
            return 'Menunggu Persetujuan ' . ($cfg?->label ?? 'Final');
        }

        return match($this->status) {
            self::STATUS_DRAFT        => 'Draft',
            self::STATUS_APPROVED_CFO => 'Final',
            self::STATUS_REJECTED     => 'Dikembalikan',
            default                   => $this->status,
        };
    }

    public function getSpLevelLabelAttribute(): string
    {
        return match($this->sp_level) {
            'none' => 'Tidak Ada',
            'sp1'  => 'SP 1',
            'sp2'  => 'SP 2',
            'sp3'  => 'SP 3',
            default => $this->sp_level,
        };
    }
}
