<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Appraisal\Appraisal;
use App\Services\Appraisal\ApprovalStateMachine;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

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

        $isFinal = $appraisal->fresh()->isFinal();
        $msg = $isFinal
            ? 'Penilaian telah disetujui final dan dikunci.'
            : 'Penilaian disetujui dan diteruskan ke tahap berikutnya.';

        return redirect()->route('appraisal.appraisals.show', $appraisal)->with('status', $msg);
    }

    public function reject(Request $request, Appraisal $appraisal)
    {
        $sm = new ApprovalStateMachine();

        if (! $sm->canReject($appraisal, auth()->user())) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menolak penilaian ini.');
        }

        $request->validate(['notes' => 'required|string|max:1000']);

        $sm->reject($appraisal, auth()->user(), $request->input('notes'));

        return redirect()->route('appraisal.appraisals.show', $appraisal)
            ->with('status', 'Penilaian telah dikembalikan ke evaluator untuk diperbaiki.');
    }
}
