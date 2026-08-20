<?php

namespace App\Http\Controllers\Reimbursement;

use App\Http\Controllers\Controller;
use App\Mail\ReimbursementApprovedMail;
use App\Mail\ReimbursementRejectedMail;
use App\Models\Reimbursement\ReimbursementAttachment;
use App\Models\Reimbursement\ReimbursementBalance;
use App\Models\Reimbursement\ReimbursementItem;
use App\Models\Reimbursement\ReimbursementRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ReimbursementAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = ReimbursementRequest::with('user')->latest('request_date');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->year) {
            $query->whereYear('request_date', $request->year);
        }

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $requests = $query->paginate(20)->withQueryString();
        $users    = \App\Models\User::orderBy('name')->get();

        return view('admin.reimbursement.index', compact('requests', 'users'));
    }

    public function show(ReimbursementRequest $reimbursement)
    {
        $reimbursement->load(['user', 'items', 'attachments', 'approver']);
        $balance = ReimbursementBalance::forUser($reimbursement->user_id, $reimbursement->request_date->year);
        return view('admin.reimbursement.show', compact('reimbursement', 'balance'));
    }

    public function updateItem(Request $request, ReimbursementRequest $reimbursement, ReimbursementItem $item)
    {
        abort_unless($reimbursement->isSubmitted(), 422);
        abort_unless($item->reimbursement_request_id === $reimbursement->id, 404);

        $rules = [];
        foreach (array_keys(ReimbursementItem::AMOUNT_FIELDS) as $field) {
            $rules[$field] = 'required|integer|min:0';
        }
        $data = $request->validate($rules);

        $item->fill($data);
        $item->total_claim = $item->calculateTotal();
        $item->save();

        $reimbursement->recalculateTotal();

        return back()->with('status', 'Rincian biaya berhasil dikoreksi.');
    }

    public function destroyItem(ReimbursementRequest $reimbursement, ReimbursementItem $item)
    {
        abort_unless($reimbursement->isSubmitted(), 422);
        abort_unless($item->reimbursement_request_id === $reimbursement->id, 404);

        $item->delete();
        $reimbursement->recalculateTotal();

        return back()->with('status', 'Item klaim berhasil dihapus.');
    }

    public function approve(Request $request, ReimbursementRequest $reimbursement)
    {
        abort_unless($reimbursement->isSubmitted(), 422);

        $data = $request->validate([
            'payment_month' => 'required|integer|between:1,12',
            'payment_year'  => 'required|integer|min:2020|max:2100',
        ]);

        $balance = ReimbursementBalance::forUser($reimbursement->user_id, $reimbursement->request_date->year);

        if ($balance && $reimbursement->total_claim > $balance->remaining_balance) {
            return back()->with('error',
                'Saldo tidak mencukupi untuk menyetujui pengajuan ini. Sisa saldo Rp ' .
                number_format($balance->remaining_balance, 0, ',', '.') .
                ', silakan koreksi rincian biaya terlebih dahulu agar sesuai sisa saldo.');
        }

        $reimbursement->update([
            'status'         => 'approved',
            'approved_by'    => auth()->id(),
            'approved_at'    => now(),
            'payment_month'  => $data['payment_month'],
            'payment_year'   => $data['payment_year'],
        ]);

        if ($balance) {
            $balance->increment('used_balance', $reimbursement->total_claim);
        }

        try {
            Mail::to($reimbursement->user->email)->send(new ReimbursementApprovedMail($reimbursement));
        } catch (\Throwable) {}

        return back()->with('status', 'Pengajuan berhasil disetujui.');
    }

    public function reject(Request $request, ReimbursementRequest $reimbursement)
    {
        abort_unless($reimbursement->isSubmitted(), 422);

        $request->validate(['rejection_reason' => 'nullable|string|max:500']);

        $reimbursement->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by'      => auth()->id(),
            'approved_at'      => now(),
        ]);

        try {
            Mail::to($reimbursement->user->email)->send(new ReimbursementRejectedMail($reimbursement));
        } catch (\Throwable) {}

        return back()->with('status', 'Pengajuan ditolak.');
    }

    public function attachment(ReimbursementRequest $reimbursement, ReimbursementAttachment $attachment)
    {
        abort_unless($attachment->reimbursement_request_id === $reimbursement->id, 404);
        return \Illuminate\Support\Facades\Storage::disk('local')->response($attachment->file_path, $attachment->file_name);
    }

    public function updateAttachmentType(Request $request, ReimbursementRequest $reimbursement, ReimbursementAttachment $attachment)
    {
        abort_unless($attachment->reimbursement_request_id === $reimbursement->id, 404);

        $data = $request->validate([
            'doc_type' => 'required|in:' . implode(',', array_keys(ReimbursementAttachment::$docTypes)),
        ]);

        $taken = $reimbursement->attachments()
            ->where('doc_type', $data['doc_type'])
            ->where('id', '!=', $attachment->id)
            ->exists();

        if ($taken) {
            return back()->with('error', 'Jenis dokumen itu sudah dipakai lampiran lain di pengajuan ini.');
        }

        $attachment->update($data);

        return back()->with('status', 'Jenis dokumen lampiran berhasil disimpan.');
    }

    public function pdf(ReimbursementRequest $reimbursement)
    {
        $reimbursement->load(['items', 'user', 'approver']);
        $pdf = Pdf::loadView('reimbursement.pdf', compact('reimbursement'))->setPaper('a4', 'landscape');
        $filename = 'reimbursement-' . str_replace('/', '-', $reimbursement->request_number) . '.pdf';
        return $pdf->download($filename);
    }
}
