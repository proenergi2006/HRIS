<?php

namespace App\Traits;

use App\Models\Approval\ApprovalWorkflow;
use App\Models\Approval\ApprovalWorkflowChangeLog;
use App\Models\Approval\ApprovalWorkflowStep;
use Illuminate\Support\Facades\Auth;

/**
 * Dipakai App\Http\Controllers\Approval\ApprovalWorkflowController untuk mencatat
 * jejak perubahan aturan approval ke approval_workflow_change_logs (PRD Bab 7.5).
 * Dipanggil eksplisit di update()/copy() — bukan lewat observer — sama seperti
 * App\Traits\LogsOrgChanges. `saveWorkflow()` hard-delete + recreate semua step
 * tiap simpan, jadi spatie/activitylog tidak cocok; kita snapshot before/after.
 */
trait LogsApprovalWorkflowChanges
{
    /**
     * Ambil daftar step sebuah workflow sebagai array ringkas (buat diff).
     *
     * @return array<int, array<string, mixed>>
     */
    protected function snapshotWorkflowSteps(int $companyId, string $type): array
    {
        $workflow = ApprovalWorkflow::with(['steps' => fn ($q) => $q->orderBy('step_order'), 'steps.position'])
            ->where('company_id', $companyId)
            ->where('transaction_type', $type)
            ->first();

        if (! $workflow) {
            return [];
        }

        return $workflow->steps->map(fn (ApprovalWorkflowStep $s) => [
            'urutan'        => $s->step_order,
            'approver'      => $s->approver_label,
            'eskalasi_hari' => $s->escalate_after_days,
            'kondisi'       => $s->conditions ? collect($s->conditions)->map(
                fn ($c) => "{$c['field']} {$c['operator']} {$c['value']}"
            )->implode(' & ') : null,
        ])->all();
    }

    protected function logWorkflowChange(
        int $companyId,
        string $type,
        string $action,
        ?array $before,
        ?array $after,
        ?string $note = null,
    ): void {
        if ($action === 'updated' && $before === $after) {
            return; // tidak ada perubahan nyata
        }

        ApprovalWorkflowChangeLog::create([
            'company_id'        => $companyId,
            'transaction_type'  => $type,
            'transaction_label' => ApprovalWorkflow::$transactionTypes[$type] ?? $type,
            'action'            => $action,
            'before'            => $before ?: null,
            'after'             => $after ?: null,
            'changed_by'        => Auth::id(),
            'note'              => $note,
        ]);
    }
}
