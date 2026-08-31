<?php

namespace App\Models;

use App\Contracts\Approvable;
use App\Traits\AsApprovableRequest;
use App\Traits\HasApprovalWorkflow;
use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PunishmentRequest extends Model implements Approvable
{
    use AsApprovableRequest, LogsActivity, HasApprovalWorkflow, HasHashid;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'sanction_level', 'notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('punishment');
    }

    protected $fillable = [
        'employee_id', 'company_id', 'requested_by_user_id',
        'violation_type', 'sanction_level', 'description',
        'incident_date', 'effective_date', 'status', 'notes',
    ];

    protected $casts = [
        'incident_date'  => 'date',
        'effective_date' => 'date',
    ];

    public static array $sanctionLabels = [
        'teguran_lisan' => 'Teguran Lisan',
        'sp1'           => 'SP 1',
        'sp2'           => 'SP 2',
        'sp3'           => 'SP 3',
        'demosi'        => 'Demosi',
        'phk'           => 'PHK',
        'other'         => 'Lainnya',
    ];

    public function approvalTransactionType(): string
    {
        return 'punishment_request';
    }

    public function approvalSummary(): string
    {
        return 'Punishment — ' . ($this->employee?->name ?? '?') . ' · '
            . (self::$sanctionLabels[$this->sanction_level] ?? $this->sanction_level);
    }
}
