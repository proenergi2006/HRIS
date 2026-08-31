<?php

namespace App\Models\Approval;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApprovalRequest extends Model
{
    protected $fillable = [
        'approvable_type', 'approvable_id', 'approval_workflow_id', 'company_id',
        'transaction_type', 'summary', 'requester_user_id', 'subject_employee_id',
        'status', 'current_step_order', 'submitted_at', 'completed_at',
    ];

    protected $casts = [
        'submitted_at'      => 'datetime',
        'completed_at'      => 'datetime',
        'current_step_order' => 'integer',
    ];

    public static array $statusLabels = [
        'pending'   => 'Menunggu Persetujuan',
        'approved'  => 'Disetujui',
        'rejected'  => 'Ditolak',
        'cancelled' => 'Dibatalkan',
    ];

    public static array $statusBadges = [
        'pending'   => 'warning',
        'approved'  => 'success',
        'rejected'  => 'danger',
        'cancelled' => 'secondary',
    ];

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'approval_workflow_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    public function subjectEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'subject_employee_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalRequestStep::class)->orderBy('step_order');
    }

    public function currentStep(): ?ApprovalRequestStep
    {
        return $this->steps->firstWhere('status', 'pending');
    }

    public function getTypeLabelAttribute(): string
    {
        return ApprovalWorkflow::$transactionTypes[$this->transaction_type] ?? $this->transaction_type;
    }
}
