<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Mail\AppraisalFinalizedMail;
use App\Mail\AppraisalNeedsApprovalMail;
use App\Mail\AppraisalRejectedMail;
use App\Models\Appraisal\Appraisal;
use App\Models\User;
use App\Services\Appraisal\ApprovalStateMachine;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Mail;

class ApprovalController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function submit(Appraisal $appraisal)
    {
        $sm = new ApprovalStateMachine();

        if (! $sm->canSubmit($appraisal, auth()->user())) {
            if (! $appraisal->isComplete()) {
                return back()->with('error', 'Semua aspek penilaian harus diisi sebelum disubmit.');
            }
            return back()->with('error', 'Penilaian tidak dapat disubmit saat ini.');
        }

        $sm->submit($appraisal, auth()->user());
        $appraisal->refresh()->load('employee', 'period');

        // Notify approver(s) yang perlu bertindak berikutnya
        $nextStep = $appraisal->status === Appraisal::STATUS_SUBMITTED ? 1 : 2;
        $this->notifyNextApprovers($sm, $appraisal, $nextStep);

        return redirect()->route('appraisal.appraisals.show', $appraisal)
            ->with('status', 'Penilaian berhasil disubmit dan menunggu persetujuan.');
    }

    public function approve(Request $request, Appraisal $appraisal)
    {
        $sm   = new ApprovalStateMachine();
        $user = auth()->user();

        if (! $sm->canApprove($appraisal, $user)) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menyetujui penilaian ini.');
        }

        $isFinalStep = $appraisal->status === Appraisal::STATUS_APPROVED_U2;

        $rules = ['notes' => 'nullable|string|max:1000'];
        if ($isFinalStep) {
            $rules['decision'] = 'required|string|max:255';
        }
        $request->validate($rules);

        if ($isFinalStep && $request->filled('decision')) {
            $appraisal->update(['decision' => $request->input('decision')]);
        }

        $sm->approve($appraisal, $user, $request->input('notes'));
        $appraisal->refresh()->load('employee', 'period', 'evaluator');

        if ($isFinalStep) {
            // Penilaian final — notify evaluator
            if ($appraisal->evaluator && $appraisal->evaluator->email) {
                Mail::to($appraisal->evaluator->email)
                    ->send(new AppraisalFinalizedMail($appraisal));
            }
            $msg = 'Penilaian telah disetujui final dan dikunci.';
        } else {
            // Step 1 approve → notify step 2 approvers
            $this->notifyNextApprovers($sm, $appraisal, 2);
            $msg = 'Penilaian disetujui dan diteruskan ke tahap berikutnya.';
        }

        return redirect()->route('appraisal.appraisals.show', $appraisal)->with('status', $msg);
    }

    public function reject(Request $request, Appraisal $appraisal)
    {
        $sm = new ApprovalStateMachine();

        if (! $sm->canReject($appraisal, auth()->user())) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menolak penilaian ini.');
        }

        $request->validate(['notes' => 'required|string|max:1000']);

        $notes = $request->input('notes');
        $sm->reject($appraisal, auth()->user(), $notes);
        $appraisal->refresh()->load('employee', 'period', 'evaluator');

        // Notify evaluator bahwa penilaian dikembalikan
        if ($appraisal->evaluator && $appraisal->evaluator->email) {
            Mail::to($appraisal->evaluator->email)
                ->send(new AppraisalRejectedMail($appraisal, $notes));
        }

        return redirect()->route('appraisal.appraisals.show', $appraisal)
            ->with('status', 'Penilaian telah dikembalikan ke evaluator untuk diperbaiki.');
    }

    private function notifyNextApprovers(ApprovalStateMachine $sm, Appraisal $appraisal, int $step): void
    {
        $cfg = $sm->stepConfig($appraisal, $step);
        if (! $cfg) {
            return;
        }

        $approvers = User::role($cfg->role)->get();
        foreach ($approvers as $approver) {
            if (! $approver->email) {
                continue;
            }
            // Filter by department jika approver punya department
            if ($approver->department && $approver->department !== ($appraisal->employee?->department ?? '')) {
                continue;
            }
            Mail::to($approver->email)
                ->send(new AppraisalNeedsApprovalMail($appraisal, $cfg->label ?? $cfg->role));
        }
    }
}
