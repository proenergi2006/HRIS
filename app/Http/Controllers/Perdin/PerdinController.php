<?php

namespace App\Http\Controllers\Perdin;

use App\Http\Controllers\Controller;
use App\Mail\Perdin\PerdinSubmittedMail;
use App\Models\Perdin\PerdinRequest;
use App\Services\Perdin\PerdinApprovalService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class PerdinController extends Controller
{
    public function __construct(private PerdinApprovalService $service) {}

    private function rules(): array
    {
        return [
            'department'      => 'nullable|string|max:150',
            'destination'     => 'required|string|max:200',
            'departure_date'  => 'required|date',
            'departure_time'  => 'nullable',
            'return_date'     => 'required|date|after_or_equal:departure_date',
            'return_time'     => 'nullable',
            'purpose'         => 'nullable|string|max:2000',

            'budget'                  => 'required|array|min:1',
            'budget.*.category'       => 'required|in:transportasi,penginapan,lain_lain,uang_saku',
            'budget.*.item_name'      => 'required|string|max:200',
            'budget.*.handled_by'     => 'required|in:self,ga',
            'budget.*.qty'            => 'required|integer|min:1',
            'budget.*.unit_cost'      => 'required|integer|min:0',

            'itinerary'                  => 'nullable|array',
            'itinerary.*.travel_date'    => 'required_with:itinerary|date',
            'itinerary.*.time_start'     => 'nullable',
            'itinerary.*.time_end'       => 'nullable',
            'itinerary.*.timezone'       => 'required_with:itinerary|in:WIB,WITA,WIT',
            'itinerary.*.description'    => 'required_with:itinerary|string|max:300',
        ];
    }

    public function index()
    {
        $user     = auth()->user();
        $requests = PerdinRequest::where('user_id', $user->id)
            ->latest('departure_date')->paginate(15)->withQueryString();

        return view('perdin.index', compact('requests'));
    }

    public function create()
    {
        $perdin = new PerdinRequest();

        return view('perdin.create', compact('perdin'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $perdin = PerdinRequest::create([
            'no_advance'     => PerdinRequest::generateNumber(),
            'user_id'        => auth()->id(),
            'department'     => $validated['department'] ?? auth()->user()->department,
            'destination'    => $validated['destination'],
            'departure_date' => $validated['departure_date'],
            'departure_time' => $validated['departure_time'] ?? null,
            'return_date'    => $validated['return_date'],
            'return_time'    => $validated['return_time'] ?? null,
            'purpose'        => $validated['purpose'] ?? null,
            'status'         => 'draft',
        ]);

        $this->syncBudget($perdin, $validated['budget']);
        $this->syncItinerary($perdin, $validated['itinerary'] ?? []);
        $perdin->recalculateTotals();

        return redirect()->route('perdin.show', $perdin)
            ->with('status', 'Permohonan perjalanan dinas disimpan sebagai draft.');
    }

    public function show(PerdinRequest $perdin)
    {
        $this->authorizeOwnerOrApprover($perdin);
        $perdin->load(['budgetItems', 'itineraries', 'approvals.approver', 'user']);

        $canApprove    = $this->service->canApprove($perdin, auth()->user());
        $managerUser   = $this->service->directManagerUser($perdin);

        return view('perdin.show', compact('perdin', 'canApprove', 'managerUser'));
    }

    public function edit(PerdinRequest $perdin)
    {
        abort_unless($perdin->user_id === auth()->id(), 403);
        abort_unless($perdin->isEditable(), 403);
        $perdin->load(['budgetItems', 'itineraries']);

        return view('perdin.edit', compact('perdin'));
    }

    public function update(Request $request, PerdinRequest $perdin)
    {
        abort_unless($perdin->user_id === auth()->id(), 403);
        abort_unless($perdin->isEditable(), 403);

        $validated = $request->validate($this->rules());

        $perdin->update([
            'department'     => $validated['department'] ?? $perdin->department,
            'destination'    => $validated['destination'],
            'departure_date' => $validated['departure_date'],
            'departure_time' => $validated['departure_time'] ?? null,
            'return_date'    => $validated['return_date'],
            'return_time'    => $validated['return_time'] ?? null,
            'purpose'        => $validated['purpose'] ?? null,
        ]);

        $perdin->budgetItems()->delete();
        $perdin->itineraries()->delete();
        $this->syncBudget($perdin, $validated['budget']);
        $this->syncItinerary($perdin, $validated['itinerary'] ?? []);
        $perdin->recalculateTotals();

        return redirect()->route('perdin.show', $perdin)
            ->with('status', 'Permohonan berhasil diperbarui.');
    }

    public function submit(PerdinRequest $perdin)
    {
        abort_unless($perdin->user_id === auth()->id(), 403);
        abort_unless($perdin->isEditable(), 403);
        abort_if($perdin->budgetItems()->count() === 0, 422, 'Tambahkan minimal 1 item anggaran sebelum submit.');

        try {
            $this->service->submit($perdin);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        $this->notifyNextApprover($perdin);

        return redirect()->route('perdin.show', $perdin)
            ->with('status', 'Permohonan berhasil disubmit dan menunggu persetujuan.');
    }

    public function pdf(PerdinRequest $perdin)
    {
        $this->authorizeOwnerOrApprover($perdin);
        $perdin->load(['budgetItems', 'itineraries', 'approvals.approver', 'user']);

        $managerUser = $this->service->directManagerUser($perdin);

        $pdf = Pdf::loadView('perdin.pdf', compact('perdin', 'managerUser'))->setPaper('a4', 'portrait');
        $filename = 'perdin-' . str_replace('/', '-', $perdin->no_advance) . '.pdf';

        return $pdf->download($filename);
    }

    public function destroy(PerdinRequest $perdin)
    {
        abort_unless($perdin->user_id === auth()->id(), 403);
        abort_unless($perdin->isDraft(), 403);
        $perdin->delete();

        return redirect()->route('perdin.index')->with('status', 'Draft permohonan dihapus.');
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function notifyNextApprover(PerdinRequest $perdin): void
    {
        try {
            $recipients = collect();
            $role = $perdin->nextApprovalRole();

            if ($role === 'direct_manager') {
                $recipients->push($this->service->directManagerUser($perdin));
            } elseif ($role === 'hr_manager') {
                $recipients = \App\Models\User::role('hr_manager')->get();
            } elseif ($role === 'ceo') {
                $recipients = \App\Models\User::role('ceo')->get();
            }

            foreach ($recipients->filter() as $user) {
                Mail::to($user->email)->send(new PerdinSubmittedMail($perdin, $user));
            }
        } catch (\Throwable) {
            // notification failure must not block the flow
        }
    }

    private function authorizeOwnerOrApprover(PerdinRequest $perdin): void
    {
        $user = auth()->user();
        if ($perdin->user_id === $user->id) {
            return;
        }
        if ($user->hasRole(['admin', 'hr_manager', 'ceo'])) {
            return;
        }
        if ($this->service->directManagerUser($perdin)?->id === $user->id) {
            return;
        }
        abort(403);
    }

    private function syncBudget(PerdinRequest $perdin, array $items): void
    {
        foreach ($items as $row) {
            $perdin->budgetItems()->create([
                'category'   => $row['category'],
                'item_name'  => $row['item_name'],
                'handled_by' => $row['handled_by'],
                'qty'        => (int) $row['qty'],
                'unit_cost'  => (int) $row['unit_cost'],
            ]);
        }
    }

    private function syncItinerary(PerdinRequest $perdin, array $items): void
    {
        $no = 1;
        foreach ($items as $row) {
            if (empty($row['travel_date']) || empty($row['description'])) {
                continue;
            }
            $perdin->itineraries()->create([
                'no'          => $no++,
                'travel_date' => $row['travel_date'],
                'time_start'  => $row['time_start'] ?? null,
                'time_end'    => $row['time_end'] ?? null,
                'timezone'    => $row['timezone'] ?? 'WIB',
                'description' => $row['description'],
            ]);
        }
    }
}
