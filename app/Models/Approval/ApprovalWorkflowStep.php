<?php

namespace App\Models\Approval;

use App\Models\Position;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalWorkflowStep extends Model
{
    protected $fillable = [
        'approval_workflow_id', 'step_order', 'approver_type',
        'approver_position_id', 'approver_role', 'conditions',
        'escalate_after_days', 'is_active',
    ];

    protected $casts = [
        'conditions'          => 'array',
        'is_active'           => 'boolean',
        'step_order'          => 'integer',
        'escalate_after_days' => 'integer',
    ];

    public static array $approverTypes = [
        'direct_manager'    => 'Atasan Langsung',
        'section_head'      => 'Kepala Section',
        'department_head'   => 'Kepala Departemen',
        'division_head'     => 'Kepala Divisi',
        'specific_position' => 'Jabatan Tertentu',
        'specific_role'     => 'Role Tertentu',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'approval_workflow_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'approver_position_id');
    }

    public function getApproverLabelAttribute(): string
    {
        $base = self::$approverTypes[$this->approver_type] ?? $this->approver_type;

        return match ($this->approver_type) {
            'specific_position' => $base . ': ' . ($this->position?->name ?? '?'),
            'specific_role'     => $base . ': ' . ($this->approver_role ?? '?'),
            default             => $base,
        };
    }
}
