<?php

namespace App\Http\Controllers\Perdin;

use App\Http\Controllers\Controller;
use App\Mail\Perdin\PerdinResultMail;
use App\Mail\Perdin\PerdinSubmittedMail;
use App\Models\Employee;
use App\Models\Perdin\PerdinRequest;
use App\Models\User;
use App\Services\Perdin\PerdinApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class PerdinApprovalController extends Controller
{
    public function __construct(private PerdinApprovalService $service) {}

    /**
     * Inbox of requests awaiting the current user's action.
     */
    public function index()
    {
        $user = auth()->user();

        $query = PerdinRequest::with('user')->whereIn('status', $this->statusesForUser($user));

        // Direct managers should only see their own subordinates' requests.
        if ($this->onlyManager($user)) {
            $subordinateUserIds = Employee::where('manager_id', function ($q) use ($user) {
                $q->select('id')->from('employees')->where('user_id', $user->id)->limit(1);
            })->pluck('user_id')->filter();

            $query->where(function ($q) use ($subordinateUserIds, $user) {
                $q->whereIn('user_id', $subordinateUserIds)
                  ->orWhereIn('status', ['reviewed_manager', 'reviewed_hr']);
            });
        }

        $pending = $query->latest('updated_at')->paginate(15)->withQueryString();

        // Filter to only the ones this user can actually act on right now.
        $pending->setCollection(
            $pending->getCollection()->filter(fn ($p) => $this->service->canApprove($p, $user))->values()
        );

        return view('perdin.approvals.index', compact('pending'));
    }

    public function approve(Request $request, PerdinRequest $perdin)
    {
        $data = $request->validate(['notes' => 'nullable|string|max:1000']);

        try {
            $this->service->approve($perdin, auth()->user(), $data['notes'] ?? null);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        if ($perdin->isApproved()) {
            $this->notifyEmployee($perdin, true);
        } else {
            $this->notifyNextApprover($perdin);
        }

        return redirect()->route('perdin.show', $perdin)
            ->with('status', 'Permohonan berhasil disetujui.');
    }

    public function reject(Request $request, PerdinRequest $perdin)
    {
        $data = $request->validate(['notes' => 'required|string|max:1000']);

        try {
            $this->service->reject($perdin, auth()->user(), $data['notes']);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        $this->notifyEmployee($perdin, false);

        return redirect()->route('perdin.show', $perdin)
            ->with('status', 'Permohonan telah ditolak.');
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function statusesForUser(User $user): array
    {
        $statuses = [];
        if ($user->hasRole('ceo') || $user->hasRole('admin')) {
            $statuses[] = 'reviewed_hr';
        }
        if ($user->hasRole('hr_manager') || $user->hasRole('admin')) {
            $statuses[] = 'reviewed_manager';
        }
        // Anyone may be a direct manager.
        $statuses[] = 'submitted';

        return array_values(array_unique($statuses));
    }

    private function onlyManager(User $user): bool
    {
        return ! $user->hasAnyRole(['admin', 'hr_manager', 'ceo']);
    }

    private function notifyNextApprover(PerdinRequest $perdin): void
    {
        try {
            $role = $perdin->nextApprovalRole();
            $recipients = match ($role) {
                'direct_manager' => collect([$this->service->directManagerUser($perdin)]),
                'hr_manager'     => User::role('hr_manager')->get(),
                'ceo'            => User::role('ceo')->get(),
                default          => collect(),
            };

            foreach ($recipients->filter() as $user) {
                Mail::to($user->email)->send(new PerdinSubmittedMail($perdin, $user));
            }
        } catch (\Throwable) {
        }
    }

    private function notifyEmployee(PerdinRequest $perdin, bool $approved): void
    {
        try {
            Mail::to($perdin->user->email)->send(new PerdinResultMail($perdin, $approved));
        } catch (\Throwable) {
        }
    }
}
