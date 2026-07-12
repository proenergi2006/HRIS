<?php

namespace App\Http\Controllers\GA;

use App\Http\Controllers\Controller;
use App\Models\GA\VaultDocument;
use App\Models\GA\VaultDocumentTransaction;
use Illuminate\Http\Request;

class PublicVaultController extends Controller
{
    public function scan(VaultDocument $document)
    {
        if (! $document->is_active) {
            abort(404);
        }

        $document->load('category');

        return view('ga.public.vault_scan', compact('document'));
    }

    public function submit(Request $request, VaultDocument $document)
    {
        if (! $document->is_active) {
            abort(404);
        }

        $data = $request->validate([
            'status'           => 'required|in:pengambilan,pengembalian',
            'transaction_date' => 'required|date',
            'nama'             => 'required|string|max:100',
            'keperluan'        => 'required|in:pinjam,jual,pengembalian_jaminan,pengambilan_dokumen',
            'photo_handover'   => 'required|image|max:8192',
        ]);

        $data['document_id']    = $document->id;
        $data['photo_handover'] = $request->file('photo_handover')->store('vault-documents', 'local');

        VaultDocumentTransaction::create($data);

        return redirect()->route('ga.vault.success', $document)
            ->with('success', 'Transaksi dokumen berhasil disimpan. Terima kasih!');
    }

    public function success(VaultDocument $document)
    {
        $document->load('category');

        return view('ga.public.vault_success', compact('document'));
    }
}
