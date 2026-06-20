<?php

namespace App\Http\Controllers\Reimbursement;

use App\Http\Controllers\Controller;
use App\Models\Reimbursement\ReimbursementBalance;
use App\Models\Reimbursement\ReimbursementRequest;
use Illuminate\Http\Request;

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

        $requests = $query->get();
        $users    = \App\Models\User::orderBy('name')->get();

        return view('admin.reimbursement.index', compact('requests', 'users'));
    }

    public function show(ReimbursementRequest $reimbursement)
    {
        $reimbursement->load(['user', 'items', 'attachments', 'approver']);
        $balance = ReimbursementBalance::forUser($reimbursement->user_id, $reimbursement->request_date->year);
        return view('admin.reimbursement.show', compact('reimbursement', 'balance'));
    }

    public function approve(ReimbursementRequest $reimbursement)
    {
        abort_unless($reimbursement->isSubmitted(), 422);

        $balance = ReimbursementBalance::forUser($reimbursement->user_id, $reimbursement->request_date->year);

        if ($balance && $reimbursement->total_claim > $balance->remaining_balance) {
            return back()->with('error', 'Saldo tidak mencukupi untuk menyetujui pengajuan ini.');
        }

        $reimbursement->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        if ($balance) {
            $balance->increment('used_balance', $reimbursement->total_claim);
        }

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

        return back()->with('status', 'Pengajuan ditolak.');
    }

    public function attachment(ReimbursementRequest $reimbursement, \App\Models\Reimbursement\ReimbursementAttachment $attachment)
    {
        abort_unless($attachment->reimbursement_request_id === $reimbursement->id, 404);
        return \Illuminate\Support\Facades\Storage::disk('local')->response($attachment->file_path, $attachment->file_name);
    }

    public function pdf(ReimbursementRequest $reimbursement)
    {
        $reimbursement->load(['items', 'user', 'approver']);
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('reimbursement.pdf', compact('reimbursement'))->setPaper('a4', 'landscape');
        return $pdf->download('reimbursement-' . $reimbursement->request_number . '.pdf');
    }
}
