<?php

namespace App\Services\Appraisal;

use App\Models\Appraisal\Appraisal;
use App\Models\Appraisal\AppraisalApproval;
use App\Models\Appraisal\AppraisalFlowConfig;
use App\Models\User;

class ApprovalStateMachine
{
    public function stepConfig(Appraisal $appraisal, int $step): ?AppraisalFlowConfig
    {
        return AppraisalFlowConfig::forDepartment(
            $appraisal->employee->department,
            $step
        );
    }

    // --- Permission checks ---

    public function canSubmit(Appraisal $appraisal, User $user): bool
    {
        if ($user->hasRole('karyawan')) return false;
        return $appraisal->isDraft() && $appraisal->isComplete();
    }

    public function canApprove(Appraisal $appraisal, User $user): bool
    {
        // Jika user punya department, hanya bisa approve departemen yg sama
        if ($user->department && $user->department !== ($appraisal->employee->department ?? '')) {
            return false;
        }

        if ($appraisal->status === Appraisal::STATUS_SUBMITTED) {
            $cfg = $this->stepConfig($appraisal, 1);
            return $cfg && $user->hasRole($cfg->role);
        }

        if ($appraisal->status === Appraisal::STATUS_APPROVED_U2) {
            $cfg = $this->stepConfig($appraisal, 2);
            return $cfg && $user->hasRole($cfg->role);
        }

        return false;
    }

    public function canReject(Appraisal $appraisal, User $user): bool
    {
        return $this->canApprove($appraisal, $user);
    }

    // --- Transitions ---

    public function submit(Appraisal $appraisal, User $user): void
    {
        $before = $appraisal->status;
        $dept   = $appraisal->employee->department;

        // Cek apakah dept punya step 1 explicit (bukan fallback default).
        // Jika TIDAK ada → single-step dept, langsung lompat ke approved_user2
        // agar langsung masuk antrian final approver (CEO/CFO).
        $hasExplicitStep1 = $dept && AppraisalFlowConfig::where('department', $dept)
            ->where('step', 1)
            ->exists();

        $newStatus = $hasExplicitStep1
            ? Appraisal::STATUS_SUBMITTED
            : Appraisal::STATUS_APPROVED_U2;

        $appraisal->update([
            'status'       => $newStatus,
            'submitted_at' => now(),
            // Catat siapa yang submit sebagai evaluator (penting untuk weighted_scale
            // yang dibuat oleh karyawan — evaluator_id awalnya null)
            'evaluator_id' => $appraisal->evaluator_id ?? $user->id,
        ]);

        $this->log($appraisal, $user, 'evaluator', 'submit', $before, $newStatus);
    }

    public function approve(Appraisal $appraisal, User $user, ?string $notes = null): void
    {
        $before = $appraisal->status;

        if ($appraisal->status === Appraisal::STATUS_SUBMITTED) {
            $cfg       = $this->stepConfig($appraisal, 1);
            $newStatus = Appraisal::STATUS_APPROVED_U2;
        } elseif ($appraisal->status === Appraisal::STATUS_APPROVED_U2) {
            $cfg       = $this->stepConfig($appraisal, 2);
            $newStatus = Appraisal::STATUS_APPROVED_CFO;
        } else {
            throw new \LogicException("Cannot approve from status: {$appraisal->status}");
        }

        $appraisal->update([
            'status'       => $newStatus,
            'finalized_at' => $newStatus === Appraisal::STATUS_APPROVED_CFO ? now() : null,
        ]);

        $this->log($appraisal, $user, $cfg?->role ?? 'approver', 'approve', $before, $newStatus, $notes);
    }

    public function reject(Appraisal $appraisal, User $user, string $notes): void
    {
        $before = $appraisal->status;

        if ($appraisal->status === Appraisal::STATUS_SUBMITTED) {
            $cfg = $this->stepConfig($appraisal, 1);
        } elseif ($appraisal->status === Appraisal::STATUS_APPROVED_U2) {
            $cfg = $this->stepConfig($appraisal, 2);
        } else {
            throw new \LogicException("Cannot reject from status: {$appraisal->status}");
        }

        $appraisal->update(['status' => Appraisal::STATUS_REJECTED]);

        $this->log($appraisal, $user, $cfg?->role ?? 'approver', 'reject', $before, Appraisal::STATUS_REJECTED, $notes);
    }

    // --- Helpers for views ---

    /** Label of who needs to act next, e.g. "CFO" */
    public function nextApproverLabel(Appraisal $appraisal): ?string
    {
        if ($appraisal->status === Appraisal::STATUS_SUBMITTED) {
            return $this->stepConfig($appraisal, 1)?->label;
        }

        if ($appraisal->status === Appraisal::STATUS_APPROVED_U2) {
            return $this->stepConfig($appraisal, 2)?->label;
        }

        return null;
    }

    // --- Private ---

    private function log(
        Appraisal $appraisal,
        User $user,
        string $role,
        string $action,
        string $before,
        string $after,
        ?string $notes = null
    ): void {
        AppraisalApproval::create([
            'appraisal_id'  => $appraisal->id,
            'user_id'       => $user->id,
            'role'          => $role,
            'action'        => $action,
            'status_before' => $before,
            'status_after'  => $after,
            'notes'         => $notes,
        ]);
    }
}
