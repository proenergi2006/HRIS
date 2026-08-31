<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Models\Approval\ApprovalDelegation;
use App\Models\Approval\ApprovalRequest;
use App\Models\Approval\ApprovalRequestStep;
use App\Models\User;
use App\Services\ApprovalEngine;
use Illuminate\Http\Request;

class ApprovalInboxController extends Controller
{
    public function __construct(private ApprovalEngine $engine) {}

    public function index()
    {
        $user = auth()->user();

        $steps = $this->engine->pendingStepsFor($user)
            ->with(['request.requester', 'request.subjectEmployee', 'request.approvable'])
            ->get()
            ->filter(fn ($s) => $this->engine->canActOn($s, $user))
            ->sortBy('request.submitted_at')
            ->values();

        return view('approval.inbox.index', compact('steps'));
    }

    public function show(ApprovalRequestStep $step)
    {
        $user = auth()->user();
        abort_unless($this->engine->canActOn($step, $user) || $user->hasRole('admin'), 403);

        $step->load(['request.steps.approver', 'request.steps.actedBy', 'request.requester', 'request.subjectEmployee', 'request.approvable']);

        return view('approval.inbox.show', compact('step'));
    }

    public function approve(Request $request, ApprovalRequestStep $step)
    {
        $request->validate(['notes' => 'nullable|string|max:500']);
        $this->engine->approve($step, auth()->user(), $request->notes);

        return redirect()->route('approval.inbox.index')->with('success', 'Persetujuan berhasil dicatat.');
    }

    public function reject(Request $request, ApprovalRequestStep $step)
    {
        $request->validate(['notes' => 'required|string|max:500']);
        $this->engine->reject($step, auth()->user(), $request->notes);

        return redirect()->route('approval.inbox.index')->with('success', 'Pengajuan ditolak.');
    }

    public function history()
    {
        $user = auth()->user();

        $submitted = ApprovalRequest::where('requester_user_id', $user->id)
            ->with('approvable')->latest()->paginate(20, ['*'], 'submitted');

        $acted = ApprovalRequestStep::where('acted_by_user_id', $user->id)
            ->with('request.approvable')->latest('acted_at')->paginate(20, ['*'], 'acted');

        return view('approval.inbox.history', compact('submitted', 'acted'));
    }

    // ── Delegasi ────────────────────────────────────────────────────────

    public function delegations()
    {
        $user = auth()->user();
        $delegations = ApprovalDelegation::with('delegate')
            ->where('delegator_user_id', $user->id)->latest()->get();
        $users = User::where('id', '!=', $user->id)->orderBy('name')->get(['id', 'name']);

        return view('approval.inbox.delegations', compact('delegations', 'users'));
    }

    public function storeDelegation(Request $request)
    {
        $data = $request->validate([
            'delegate_user_id' => 'required|exists:users,id|not_in:' . auth()->id(),
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'reason'           => 'nullable|string|max:255',
        ]);
        $data['delegator_user_id'] = auth()->id();
        $data['is_active']         = true;

        ApprovalDelegation::create($data);

        return back()->with('success', 'Delegasi persetujuan dibuat.');
    }

    public function destroyDelegation(ApprovalDelegation $delegation)
    {
        abort_unless($delegation->delegator_user_id === auth()->id(), 403);
        $delegation->delete();

        return back()->with('success', 'Delegasi dihapus.');
    }
}
