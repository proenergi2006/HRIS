<?php

namespace App\Models;

use App\Contracts\Approvable;
use App\Traits\AsApprovableRequest;
use App\Traits\HasApprovalWorkflow;
use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RewardRequest extends Model implements Approvable
{
    use AsApprovableRequest, LogsActivity, HasApprovalWorkflow, HasHashid;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'amount', 'notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('reward');
    }

    protected $fillable = [
        'employee_id', 'company_id', 'requested_by_user_id',
        'reward_type', 'description', 'effective_date', 'amount', 'status', 'notes',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'amount'         => 'integer',
    ];

    public function approvalTransactionType(): string
    {
        return 'reward_request';
    }

    public function approvalSummary(): string
    {
        return 'Reward — ' . ($this->employee?->name ?? '?') . ' · ' . $this->reward_type;
    }
}
