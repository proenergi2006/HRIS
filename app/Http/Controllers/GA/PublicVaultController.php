<?php

namespace App\Http\Controllers\GA;

use App\Http\Controllers\Controller;
use App\Models\GA\Vault;
use App\Models\GA\VaultDocument;
use App\Models\GA\VaultDocumentTransaction;
use Illuminate\Http\Request;

class PublicVaultController extends Controller
{
    public function scan(Vault $vault)
    {
        if (! $vault->is_active) {
            abort(404);
        }

        $documents = $vault->documents()->where('is_active', true)->orderBy('detail')->get();

        return view('ga.public.vault_scan', compact('vault', 'documents'));
    }

    public function document(Vault $vault, VaultDocument $document)
    {
        if (! $vault->is_active || ! $document->is_active || $document->vault_id !== $vault->id) {
            abort(404);
        }

        return view('ga.public.vault_document', compact('vault', 'document'));
    }

    public function submit(Request $request, Vault $vault, VaultDocument $document)
    {
        if (! $vault->is_active || ! $document->is_active || $document->vault_id !== $vault->id) {
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

        return redirect()->route('ga.vault.success', [$vault, $document])
            ->with('success', 'Transaksi dokumen berhasil disimpan. Terima kasih!');
    }

    public function success(Vault $vault, VaultDocument $document)
    {
        if ($document->vault_id !== $vault->id) {
            abort(404);
        }

        return view('ga.public.vault_success', compact('vault', 'document'));
    }
}
