<?php

namespace App\Http\Controllers\GA;

use App\Http\Controllers\Controller;
use App\Models\GA\VaultDocument;
use App\Models\GA\VaultDocumentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GaVaultTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = VaultDocumentTransaction::with(['document.category', 'creator'])
            ->latest('transaction_date')->latest('id');

        if ($request->filled('document_id')) {
            $query->where('document_id', $request->document_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('keperluan')) {
            $query->where('keperluan', $request->keperluan);
        }
        if ($request->filled('date')) {
            $query->whereDate('transaction_date', $request->date);
        }

        $transactions = $query->get();
        $documents    = VaultDocument::orderBy('barcode')->get();

        return view('ga.admin.vault_transactions.index', compact('transactions', 'documents'));
    }

    public function store(Request $request, VaultDocument $document)
    {
        $data = $request->validate([
            'status'           => 'required|in:pengambilan,pengembalian',
            'transaction_date' => 'required|date',
            'nama'             => 'required|string|max:100',
            'keperluan'        => 'required|in:pinjam,jual,pengembalian_jaminan,pengambilan_dokumen',
            'photo_handover'   => 'required|image|max:8192',
        ]);

        $data['document_id']    = $document->id;
        $data['created_by']     = auth()->id();
        $data['photo_handover'] = $request->file('photo_handover')->store('vault-documents', 'local');

        VaultDocumentTransaction::create($data);

        return redirect()->route('ga.admin.vault-documents.show', $document)
            ->with('status', 'Transaksi berhasil dicatat.');
    }

    public function photo(VaultDocumentTransaction $transaction)
    {
        abort_unless(Storage::disk('local')->exists($transaction->photo_handover), 404);
        return Storage::disk('local')->response($transaction->photo_handover);
    }
}
