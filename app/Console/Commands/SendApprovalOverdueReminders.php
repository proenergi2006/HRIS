<?php

namespace App\Console\Commands;

use App\Contracts\Approvable;
use App\Models\Approval\ApprovalRequestStep;
use App\Services\ApprovalEngine;
use Illuminate\Console\Command;

/**
 * Eskalasi approval (PRD Bab 7.6 — auto-reminder jika pending > N hari).
 * Ingatkan approver step yang masih jadi antrian terdepan (frontline) tapi
 * sudah lewat due_at, lalu tandai lewat ApprovalEngine::markOverdue() supaya
 * tidak diingatkan dobel di run berikutnya.
 */
class SendApprovalOverdueReminders extends Command
{
    protected $signature   = 'approval:remind-overdue';
    protected $description = 'Kirim reminder email ke approver untuk step persetujuan yang sudah lewat tenggat (due_at)';

    public function handle(ApprovalEngine $engine): void
    {
        $overdueRequestIds = ApprovalRequestStep::where('status', 'pending')
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereNull('notes')
            ->pluck('approval_request_id')
            ->unique();

        if ($overdueRequestIds->isEmpty()) {
            $this->info('Tidak ada step yang overdue.');
            return;
        }

        $sent = 0;

        foreach ($overdueRequestIds as $requestId) {
            // Cuma step FRONTLINE (paling depan yang pending) yang benar-benar
            // actionable — step lain di request yang sama bisa saja due_at-nya
            // ikut lewat tapi belum jadi giliran, jadi jangan diingatkan.
            $frontStep = ApprovalRequestStep::where('approval_request_id', $requestId)
                ->where('status', 'pending')
                ->orderBy('step_order')
                ->first();

            if (! $frontStep || ! $frontStep->due_at || ! $frontStep->due_at->isPast() || $frontStep->notes) {
                continue;
            }

            $approvable = $frontStep->request->approvable;
            if ($approvable instanceof Approvable) {
                $engine->notifyStepApprover($frontStep, $approvable);
                $sent++;
            }
        }

        $marked = $engine->markOverdue();

        $this->info("Reminder terkirim untuk {$sent} step. {$marked} step ditandai overdue.");
    }
}
