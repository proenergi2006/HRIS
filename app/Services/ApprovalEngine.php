<?php

namespace App\Services;

use App\Contracts\Approvable;
use App\Mail\ApprovalResultMail;
use App\Mail\ApprovalStepPendingMail;
use App\Models\Approval\ApprovalDelegation;
use App\Models\Approval\ApprovalRequest;
use App\Models\Approval\ApprovalRequestStep;
use App\Models\Approval\ApprovalWorkflow;
use App\Models\Approval\ApprovalWorkflowStep;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Mesin approval generik. Semua modul transaksional memanggil engine ini
 * alih-alih menulis logika approval sendiri.
 */
class ApprovalEngine
{
    /**
     * Mulai proses approval untuk sebuah transaksi. Membuat ApprovalRequest,
     * memateri­alisasi step dari workflow (evaluasi kondisi + resolve approver),
     * dan langsung menyelesaikan bila tidak ada step yang berlaku.
     */
    public function start(Approvable $approvable): ApprovalRequest
    {
        return DB::transaction(function () use ($approvable) {
            $companyId = $approvable->approvalCompanyId();
            $type      = $approvable->approvalTransactionType();

            $workflow = ApprovalWorkflow::with('steps')
                ->where('company_id', $companyId)
                ->where('transaction_type', $type)
                ->where('is_active', true)
                ->first();

            $request = $approvable->approvalRequest()->create([
                'approval_workflow_id' => $workflow?->id,
                'company_id'           => $companyId,
                'transaction_type'     => $type,
                'summary'              => $approvable->approvalSummary(),
                'requester_user_id'    => $approvable->approvalRequester()?->id,
                'subject_employee_id'  => $approvable->approvalSubjectEmployee()?->id,
                'status'               => 'pending',
                'current_step_order'   => 0,
                'submitted_at'         => now(),
            ]);

            $attributes = $approvable->approvalAttributes();
            $order      = 0;

            foreach ($workflow?->steps ?? [] as $wfStep) {
                if (! $wfStep->is_active || ! $this->evaluateConditions($wfStep->conditions, $attributes)) {
                    continue;
                }

                $approverId = $this->resolveApproverUserId($wfStep, $approvable);

                // Step yang approver-nya tidak bisa di-resolve (mis. atasan langsung
                // belum diisi) di-skip supaya alur tidak macet.
                if (! $approverId && $wfStep->approver_type !== 'specific_role') {
                    $request->steps()->create([
                        'step_order'     => ++$order,
                        'approver_type'  => $wfStep->approver_type,
                        'approver_label' => $wfStep->approver_label,
                        'status'         => 'skipped',
                        'notes'          => 'Approver tidak ditemukan — step dilewati otomatis.',
                    ]);
                    continue;
                }

                $request->steps()->create([
                    'step_order'       => ++$order,
                    'approver_type'    => $wfStep->approver_type,
                    'approver_label'   => $wfStep->approver_label,
                    'approver_user_id' => $approverId,
                    'status'           => 'pending',
                    'due_at'           => $wfStep->escalate_after_days
                        ? now()->addDays($wfStep->escalate_after_days)
                        : null,
                ]);
            }

            $request->load('steps');

            // Tidak ada step yang berlaku → langsung disetujui.
            $frontStep = $request->steps->where('status', 'pending')->first();
            if (! $frontStep) {
                $this->complete($request);
            } else {
                $request->update(['current_step_order' => $frontStep->step_order]);
                $this->notifyStepApprover($frontStep, $approvable);
            }

            return $request->fresh('steps');
        });
    }

    public function approve(ApprovalRequestStep $step, User $actor, ?string $notes = null): void
    {
        $this->assertActionable($step, $actor);

        $step->update([
            'status'           => 'approved',
            'acted_by_user_id' => $actor->id,
            'acted_at'         => now(),
            'notes'            => $notes,
        ]);

        $this->advance($step->request);
    }

    public function reject(ApprovalRequestStep $step, User $actor, string $notes): void
    {
        $this->assertActionable($step, $actor);

        $step->update([
            'status'           => 'rejected',
            'acted_by_user_id' => $actor->id,
            'acted_at'         => now(),
            'notes'            => $notes,
        ]);

        $request = $step->request;
        $request->update(['status' => 'rejected', 'completed_at' => now()]);

        $approvable = $request->approvable;
        if ($approvable instanceof Approvable) {
            $approvable->onApprovalRejected($notes);
            $this->notifyRequester($approvable, false, $notes);
        }
    }

    public function cancel(ApprovalRequest $request): void
    {
        if ($request->status !== 'pending') {
            return;
        }

        $request->update(['status' => 'cancelled', 'completed_at' => now()]);
        $request->steps()->where('status', 'pending')->update(['status' => 'skipped']);
    }

    protected function advance(ApprovalRequest $request): void
    {
        $next = $request->steps()->where('status', 'pending')->orderBy('step_order')->first();

        if ($next) {
            $request->update(['current_step_order' => $next->step_order]);

            $approvable = $request->approvable;
            if ($approvable instanceof Approvable) {
                $this->notifyStepApprover($next, $approvable);
            }

            return;
        }

        $this->complete($request);
    }

    protected function complete(ApprovalRequest $request): void
    {
        $request->update(['status' => 'approved', 'completed_at' => now()]);

        $approvable = $request->approvable;
        if ($approvable instanceof Approvable) {
            $approvable->onApprovalApproved();
            $this->notifyRequester($approvable, true, null);
        }
    }

    // ── Resolusi approver ────────────────────────────────────────────────

    public function resolveApproverUserId(ApprovalWorkflowStep $step, Approvable $approvable): ?int
    {
        $employee = $approvable->approvalSubjectEmployee();

        $approver = match ($step->approver_type) {
            'direct_manager'  => $employee?->manager,
            'section_head'    => $employee?->section?->head,
            'department_head' => $employee?->department?->head,
            'division_head'   => $employee?->division?->head,
            'specific_position' => $step->approver_position_id
                ? Employee::where('position_id', $step->approver_position_id)
                    ->when($approvable->approvalCompanyId(), fn ($q, $c) => $q->where('company_id', $c))
                    ->where('is_active', true)->first()
                : null,
            'specific_role' => null, // ditangani lewat canActOn
            default         => null,
        };

        return $approver instanceof Employee ? $approver->user_id : null;
    }

    // ── Kondisi ──────────────────────────────────────────────────────────

    /**
     * @param  array<int, array{field:string, operator:string, value:mixed}>|null  $conditions
     */
    public function evaluateConditions(?array $conditions, array $attributes): bool
    {
        foreach ($conditions ?? [] as $c) {
            $left  = $attributes[$c['field'] ?? ''] ?? null;
            $right = $c['value'] ?? null;

            $ok = match ($c['operator'] ?? '=') {
                '='   => $left == $right,
                '!='  => $left != $right,
                '>'   => $left > $right,
                '>='  => $left >= $right,
                '<'   => $left < $right,
                '<='  => $left <= $right,
                'in'  => in_array($left, is_array($right) ? $right : explode(',', (string) $right)),
                default => true,
            };

            if (! $ok) {
                return false;
            }
        }

        return true;
    }

    // ── Inbox / otorisasi ───────────────────────────────────────────────

    /** Query step yang menunggu tindakan user ini (termasuk sebagai delegasi & role). */
    public function pendingStepsFor(User $user): Builder
    {
        $delegatorIds = ApprovalDelegation::query()->activeOn()
            ->where('delegate_user_id', $user->id)
            ->pluck('delegator_user_id');

        $roleNames = $user->getRoleNames();

        return ApprovalRequestStep::query()
            ->where('status', 'pending')
            ->whereHas('request', fn ($q) => $q->where('status', 'pending'))
            ->where(function ($q) use ($user, $delegatorIds, $roleNames) {
                $q->where('approver_user_id', $user->id)
                  ->orWhereIn('approver_user_id', $delegatorIds);

                if ($roleNames->isNotEmpty()) {
                    $q->orWhere(function ($q2) use ($roleNames) {
                        $q2->where('approver_type', 'specific_role')
                           ->whereNull('acted_by_user_id');
                        // role dicek lagi di canActOn; di sini semua step role dimunculkan
                    });
                }
            });
    }

    public function canActOn(ApprovalRequestStep $step, User $user): bool
    {
        if ($step->status !== 'pending' || $step->request->status !== 'pending') {
            return false;
        }

        // Harus step aktif (paling depan yang pending).
        $frontline = $step->request->steps()->where('status', 'pending')->orderBy('step_order')->value('id');
        if ($frontline !== $step->id) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($step->approver_user_id === $user->id) {
            return true;
        }

        if ($step->approver_user_id
            && ApprovalDelegation::delegateFor($step->approver_user_id) === $user->id) {
            return true;
        }

        if ($step->approver_type === 'specific_role') {
            $wfStep = ApprovalWorkflowStep::where('approval_workflow_id', $step->request->approval_workflow_id)
                ->where('step_order', $step->step_order)->first();

            return $wfStep && $wfStep->approver_role && $user->hasRole($wfStep->approver_role);
        }

        return false;
    }

    // ── Notifikasi ──────────────────────────────────────────────────────

    /**
     * Kirim email ke approver step ini (generik, dipakai semua modul —
     * dipanggil dari start()/advance() saat step jadi actionable, dan dari
     * command eskalasi terjadwal). Public supaya bisa dipakai ulang command
     * `approval:remind-overdue`.
     */
    public function notifyStepApprover(ApprovalRequestStep $step, Approvable $approvable): void
    {
        $recipients = $this->resolveStepRecipients($step);

        foreach ($recipients as $user) {
            if (! $user->email) {
                continue;
            }

            try {
                Mail::to($user->email)->send(new ApprovalStepPendingMail($approvable, $step));
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim ApprovalStepPendingMail: ' . $e->getMessage());
            }
        }
    }

    /** Kirim email hasil (approved/rejected) ke requester — generik, dipakai semua modul. */
    protected function notifyRequester(Approvable $approvable, bool $approved, ?string $reason): void
    {
        $requester = $approvable->approvalRequester();
        if (! $requester?->email) {
            return;
        }

        try {
            Mail::to($requester->email)->send(new ApprovalResultMail($approvable, $approved, $reason));
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim ApprovalResultMail: ' . $e->getMessage());
        }
    }

    /** @return \Illuminate\Support\Collection<int, User> */
    protected function resolveStepRecipients(ApprovalRequestStep $step): \Illuminate\Support\Collection
    {
        if ($step->approver_user_id) {
            return $step->approver ? collect([$step->approver]) : collect();
        }

        if ($step->approver_type === 'specific_role') {
            $wfStep = ApprovalWorkflowStep::where('approval_workflow_id', $step->request->approval_workflow_id)
                ->where('step_order', $step->step_order)->first();

            if ($wfStep?->approver_role) {
                return User::role($wfStep->approver_role)->get();
            }
        }

        return collect();
    }

    // ── Eskalasi ────────────────────────────────────────────────────────

    /** Tandai step pending yang lewat tenggat. Dipanggil dari command terjadwal. */
    public function markOverdue(): int
    {
        return ApprovalRequestStep::where('status', 'pending')
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereNull('notes')
            ->update(['notes' => 'Lewat tenggat — perlu tindak lanjut.']);
    }

    protected function assertActionable(ApprovalRequestStep $step, User $actor): void
    {
        if (! $this->canActOn($step, $actor)) {
            abort(403, 'Anda tidak berhak menindak langkah persetujuan ini.');
        }
    }

    /** Buat / update workflow beserta step-nya (dipakai config UI). */
    public function saveWorkflow(int $companyId, string $type, array $steps, ?string $name = null): ApprovalWorkflow
    {
        return DB::transaction(function () use ($companyId, $type, $steps, $name) {
            $workflow = ApprovalWorkflow::updateOrCreate(
                ['company_id' => $companyId, 'transaction_type' => $type],
                ['name' => $name ?: (ApprovalWorkflow::$transactionTypes[$type] ?? $type), 'is_active' => true],
            );

            $workflow->steps()->delete();

            foreach (array_values($steps) as $i => $s) {
                $workflow->steps()->create([
                    'step_order'           => $i + 1,
                    'approver_type'        => $s['approver_type'],
                    'approver_position_id' => $s['approver_position_id'] ?? null,
                    'approver_role'        => $s['approver_role'] ?? null,
                    'conditions'           => $s['conditions'] ?? null,
                    'escalate_after_days'  => $s['escalate_after_days'] ?? null,
                    'is_active'            => true,
                ]);
            }

            return $workflow->fresh('steps');
        });
    }
}
