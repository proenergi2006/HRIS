<?php

namespace App\Services\Perdin;

use App\Models\Employee;
use App\Models\Perdin\PerdinApproval;
use App\Models\Perdin\PerdinRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PerdinApprovalService
{
    /**
     * Status that follows a successful approval at each step.
     */
    private array $advanceMap = [
        'submitted'        => 'reviewed_manager',
        'reviewed_manager' => 'reviewed_hr',
        'reviewed_hr'      => 'approved',
    ];

    /**
     * Submit a draft/rejected request and route it to the first approver.
     * Perjalanan dinas tidak menggunakan saldo/kuota.
     */
    public function submit(PerdinRequest $perdin): PerdinRequest
    {
        $perdin->recalculateTotals();
        $perdin->refresh();

        // If the employee has a direct manager, route to them first; otherwise
        // skip straight to the HR & GA manager review step.
        $initialStatus = $this->directManagerUser($perdin) ? 'submitted' : 'reviewed_manager';

        $perdin->update([
            'status'          => $initialStatus,
            'notes_rejection' => null,
        ]);

        return $perdin->refresh();
    }

    /**
     * Determine which approval role the given user may act as for the request's
     * current status, or null if they are not the next approver.
     */
    public function actingRoleFor(PerdinRequest $perdin, User $user): ?string
    {
        $role = $perdin->nextApprovalRole();
        if ($role === null) {
            return null;
        }

        $authorized = match ($role) {
            'direct_manager' => $this->directManagerUser($perdin)?->id === $user->id || $user->hasRole('admin'),
            'hr_manager'     => $user->hasRole(['hr_manager', 'admin']),
            'ceo'            => $user->hasRole(['ceo', 'admin']),
            default          => false,
        };

        return $authorized ? $role : null;
    }

    public function canApprove(PerdinRequest $perdin, User $user): bool
    {
        return $this->actingRoleFor($perdin, $user) !== null;
    }

    /**
     * Approve the current step and advance the request to the next status.
     */
    public function approve(PerdinRequest $perdin, User $user, ?string $notes = null): PerdinRequest
    {
        $role = $this->actingRoleFor($perdin, $user);

        if ($role === null) {
            throw ValidationException::withMessages([
                'approval' => 'Anda tidak berwenang menyetujui permohonan ini pada tahap saat ini.',
            ]);
        }

        $nextStatus = $this->advanceMap[$perdin->status];

        DB::transaction(function () use ($perdin, $user, $role, $notes, $nextStatus) {
            PerdinApproval::create([
                'perdin_request_id' => $perdin->id,
                'approver_user_id'  => $user->id,
                'role'              => $role,
                'action'            => 'approve',
                'notes'             => $notes,
                'acted_at'          => now(),
            ]);

            $perdin->update(['status' => $nextStatus]);
        });

        return $perdin->refresh();
    }

    /**
     * Reject at the current step. Sets status to rejected and records the note.
     */
    public function reject(PerdinRequest $perdin, User $user, ?string $notes = null): PerdinRequest
    {
        $role = $this->actingRoleFor($perdin, $user);

        if ($role === null) {
            throw ValidationException::withMessages([
                'approval' => 'Anda tidak berwenang menolak permohonan ini pada tahap saat ini.',
            ]);
        }

        DB::transaction(function () use ($perdin, $user, $role, $notes) {
            PerdinApproval::create([
                'perdin_request_id' => $perdin->id,
                'approver_user_id'  => $user->id,
                'role'              => $role,
                'action'            => 'reject',
                'notes'             => $notes,
                'acted_at'          => now(),
            ]);

            $perdin->update([
                'status'          => 'rejected',
                'notes_rejection' => $notes,
            ]);
        });

        return $perdin->refresh();
    }

    /**
     * Resolve the direct-manager User for a perdin request via the employee's
     * manager_id chain. Returns null if no manager or no linked user account.
     */
    public function directManagerUser(PerdinRequest $perdin): ?User
    {
        $employee = Employee::with('manager.user')
            ->where('user_id', $perdin->user_id)
            ->first();

        return $employee?->manager?->user;
    }
}
