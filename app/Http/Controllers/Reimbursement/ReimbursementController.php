<?php

namespace App\Http\Controllers\Reimbursement;

use App\Http\Controllers\Controller;
use App\Mail\ReimbursementSubmittedMail;
use App\Models\Reimbursement\ReimbursementAttachment;
use App\Models\Reimbursement\ReimbursementBalance;
use App\Models\Reimbursement\ReimbursementItem;
use App\Models\Reimbursement\ReimbursementRequest;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ReimbursementController extends Controller
{
    private function itemRules(): array
    {
        $today = \Carbon\Carbon::today()->format('Y-m-d');
        $rules = [
            'items'                  => 'required|array|min:1',
            'items.*.patient_name'   => 'required|string|max:100',
            'items.*.treatment_date' => 'required|date|before_or_equal:' . $today,
            'items.*.institution'    => 'required|string|max:150',
            'items.*.diagnose'       => 'nullable|string|max:200',
        ];
        foreach (array_keys(ReimbursementItem::AMOUNT_FIELDS) as $field) {
            $rules["items.*.{$field}"] = 'nullable|integer|min:0';
        }
        return $rules;
    }

    private function baseRules(): array
    {
        return array_merge([
            'request_date'   => 'required|date|before_or_equal:' . \Carbon\Carbon::today()->format('Y-m-d'),
            'medical_for'    => 'required|in:employee,spouse,child_1,child_2,child_3',
            'marital_status' => 'required|in:single,married',
            'notes'          => 'nullable|string|max:1000',
            'attachments'    => 'nullable|array',
            'attachments.*'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], $this->itemRules());
    }

    private function checkDateRule(array $validated): ?array
    {
        $requestDate = \Carbon\Carbon::parse($validated['request_date']);
        foreach ($validated['items'] as $i => $item) {
            $treatDate = \Carbon\Carbon::parse($item['treatment_date']);
            if ($treatDate->diffInDays($requestDate, false) > 30) {
                return ["items.{$i}.treatment_date" => 'Tanggal berobat melebihi 1 bulan dari tanggal pengajuan.'];
            }
        }
        return null;
    }

    public function index()
    {
        $user     = auth()->user();
        $balance  = ReimbursementBalance::forUser($user->id);
        $requests = ReimbursementRequest::where('user_id', $user->id)
            ->latest('request_date')->paginate(15)->withQueryString();

        return view('reimbursement.index', compact('balance', 'requests'));
    }

    public function create()
    {
        $balance = ReimbursementBalance::forUser(auth()->id());
        return view('reimbursement.create', compact('balance'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->baseRules());

        if ($errors = $this->checkDateRule($validated)) {
            return back()->withInput()->withErrors($errors);
        }

        $reimb = ReimbursementRequest::create([
            'user_id'        => auth()->id(),
            'request_number' => ReimbursementRequest::generateNumber(),
            'request_date'   => $validated['request_date'],
            'medical_for'    => $validated['medical_for'],
            'marital_status' => $validated['marital_status'],
            'notes'          => $validated['notes'] ?? null,
            'status'         => 'draft',
        ]);

        $this->syncItems($reimb, $validated['items']);
        $reimb->recalculateTotal();
        $this->handleAttachments($reimb, $request);

        return redirect()->route('reimbursement.show', $reimb)
            ->with('status', 'Pengajuan berhasil disimpan sebagai draft.');
    }

    public function show(ReimbursementRequest $reimbursement)
    {
        abort_unless($reimbursement->user_id === auth()->id(), 403);
        $reimbursement->load(['items', 'attachments', 'approver']);
        $balance = ReimbursementBalance::forUser($reimbursement->user_id, $reimbursement->request_date->year);
        return view('reimbursement.show', compact('reimbursement', 'balance'));
    }

    public function edit(ReimbursementRequest $reimbursement)
    {
        abort_unless($reimbursement->user_id === auth()->id(), 403);
        abort_unless($reimbursement->isDraft(), 403);
        $reimbursement->load(['items', 'attachments']);
        $balance = ReimbursementBalance::forUser($reimbursement->user_id);
        return view('reimbursement.edit', compact('reimbursement', 'balance'));
    }

    public function update(Request $request, ReimbursementRequest $reimbursement)
    {
        abort_unless($reimbursement->user_id === auth()->id(), 403);
        abort_unless($reimbursement->isDraft(), 403);

        $validated = $request->validate($this->baseRules());

        if ($errors = $this->checkDateRule($validated)) {
            return back()->withInput()->withErrors($errors);
        }

        $reimbursement->update([
            'request_date'   => $validated['request_date'],
            'medical_for'    => $validated['medical_for'],
            'marital_status' => $validated['marital_status'],
            'notes'          => $validated['notes'] ?? null,
        ]);

        $reimbursement->items()->delete();
        $this->syncItems($reimbursement, $validated['items']);
        $reimbursement->recalculateTotal();
        $this->handleAttachments($reimbursement, $request);

        return redirect()->route('reimbursement.show', $reimbursement)
            ->with('status', 'Pengajuan berhasil diperbarui.');
    }

    public function submit(ReimbursementRequest $reimbursement)
    {
        abort_unless($reimbursement->user_id === auth()->id(), 403);
        abort_unless($reimbursement->isDraft(), 403);
        abort_if($reimbursement->items()->count() === 0, 422, 'Tambahkan minimal 1 item sebelum submit.');

        $balance = ReimbursementBalance::forUser($reimbursement->user_id, $reimbursement->request_date->year);
        if ($balance && $reimbursement->total_claim > $balance->remaining_balance) {
            return back()->with('error',
                'Total klaim (Rp ' . number_format($reimbursement->total_claim, 0, ',', '.') .
                ') melebihi sisa saldo (Rp ' . number_format($balance->remaining_balance, 0, ',', '.') . ').');
        }

        $reimbursement->update(['status' => 'submitted']);

        try {
            $admins = User::role('admin')->get();
            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new ReimbursementSubmittedMail($reimbursement));
            }
        } catch (\Throwable) {
            // mail failure must not block submission
        }

        return redirect()->route('reimbursement.show', $reimbursement)
            ->with('status', 'Pengajuan berhasil disubmit dan menunggu persetujuan.');
    }

    public function pdf(ReimbursementRequest $reimbursement)
    {
        abort_unless($reimbursement->user_id === auth()->id() || auth()->user()->hasRole('admin'), 403);
        $reimbursement->load(['items', 'user', 'approver']);
        $pdf = Pdf::loadView('reimbursement.pdf', compact('reimbursement'))->setPaper('a4', 'landscape');
        $filename = 'reimbursement-' . str_replace('/', '-', $reimbursement->request_number) . '.pdf';
        return $pdf->download($filename);
    }

    public function attachment(ReimbursementRequest $reimbursement, ReimbursementAttachment $attachment)
    {
        abort_unless($reimbursement->user_id === auth()->id() || auth()->user()->hasRole('admin'), 403);
        abort_unless($attachment->reimbursement_request_id === $reimbursement->id, 404);
        return Storage::disk('local')->response($attachment->file_path, $attachment->file_name);
    }

    public function destroyAttachment(ReimbursementRequest $reimbursement, ReimbursementAttachment $attachment)
    {
        abort_unless($reimbursement->user_id === auth()->id(), 403);
        abort_unless($reimbursement->isDraft(), 403);
        abort_unless($attachment->reimbursement_request_id === $reimbursement->id, 404);

        Storage::disk('local')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('status', 'Lampiran berhasil dihapus.');
    }

    // ── helpers ─────────────────────────────────────────────────────────────

    private function syncItems(ReimbursementRequest $reimb, array $items): void
    {
        foreach ($items as $itemData) {
            $amountData = [];
            foreach (array_keys(ReimbursementItem::AMOUNT_FIELDS) as $field) {
                $amountData[$field] = (int) ($itemData[$field] ?? 0);
            }

            ReimbursementItem::create(array_merge([
                'reimbursement_request_id' => $reimb->id,
                'patient_name'   => $itemData['patient_name'],
                'treatment_date' => $itemData['treatment_date'],
                'institution'    => $itemData['institution'],
                'diagnose'       => $itemData['diagnose'] ?? null,
                'total_claim'    => array_sum($amountData),
            ], $amountData));
        }
    }

    private function handleAttachments(ReimbursementRequest $reimb, Request $request): void
    {
        if (! $request->hasFile('attachments')) return;

        foreach ($request->file('attachments') as $docType => $file) {
            if (! $file || ! $file->isValid()) continue;

            // Replace existing attachment of same type
            $existing = $reimb->attachments()->where('doc_type', $docType)->first();
            if ($existing) {
                Storage::disk('local')->delete($existing->file_path);
                $existing->delete();
            }

            $path = $file->store("reimbursement/{$reimb->id}", 'local');
            ReimbursementAttachment::create([
                'reimbursement_request_id' => $reimb->id,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'doc_type'  => $docType,
            ]);
        }
    }
}
