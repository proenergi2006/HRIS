<?php

namespace App\Http\Controllers\GA;

use App\Http\Controllers\Controller;
use App\Models\GA\Vault;
use App\Models\GA\VaultDocument;
use Illuminate\Http\Request;

class GaVaultDocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = VaultDocument::with('vault')->withCount('transactions')->latest();

        if ($request->filled('vault_id')) {
            $query->where('vault_id', $request->vault_id);
        }
        if ($request->filled('q')) {
            $query->where(function ($sub) use ($request) {
                $sub->where('barcode', 'like', "%{$request->q}%")
                    ->orWhere('detail', 'like', "%{$request->q}%");
            });
        }

        $documents = $query->get();
        $vaults    = Vault::orderBy('name')->get();

        return view('ga.admin.vault_documents.index', compact('documents', 'vaults'));
    }

    public function create()
    {
        $vaults = Vault::where('is_active', true)->orderBy('name')->get();
        return view('ga.admin.vault_documents.create', ['document' => new VaultDocument, 'vaults' => $vaults]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vault_id'  => 'required|exists:vaults,id',
            'detail'    => 'required|string',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $document = VaultDocument::create($data);

        return redirect()->route('ga.admin.vault-documents.show', $document)
            ->with('status', 'Dokumen berhasil ditambahkan.');
    }

    public function show(VaultDocument $document)
    {
        $document->load('vault');
        $transactions = $document->transactions()->with('creator')->latest('transaction_date')->latest('id')->get();

        return view('ga.admin.vault_documents.show', compact('document', 'transactions'));
    }

    public function edit(VaultDocument $document)
    {
        $vaults = Vault::where('is_active', true)->orderBy('name')->get();
        return view('ga.admin.vault_documents.create', compact('document', 'vaults'));
    }

    public function update(Request $request, VaultDocument $document)
    {
        $data = $request->validate([
            'vault_id'  => 'required|exists:vaults,id',
            'detail'    => 'required|string',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $document->update($data);

        return redirect()->route('ga.admin.vault-documents.index')->with('status', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(VaultDocument $document)
    {
        $document->delete();
        return redirect()->route('ga.admin.vault-documents.index')->with('status', 'Dokumen berhasil dihapus.');
    }
}
