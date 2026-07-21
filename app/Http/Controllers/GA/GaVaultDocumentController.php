<?php

namespace App\Http\Controllers\GA;

use App\Http\Controllers\Controller;
use App\Models\GA\Vault;
use App\Models\GA\VaultDocument;
use App\Models\GA\VaultDocumentCategory;
use Illuminate\Http\Request;

class GaVaultDocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = VaultDocument::with(['category', 'vault'])->withCount('transactions')->latest();

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('vault_id')) {
            $query->where('vault_id', $request->vault_id);
        }
        if ($request->filled('q')) {
            $query->where(function ($sub) use ($request) {
                $sub->where('barcode', 'like', "%{$request->q}%")
                    ->orWhere('detail', 'like', "%{$request->q}%");
            });
        }

        $documents  = $query->get();
        $categories = VaultDocumentCategory::orderBy('name')->get();
        $vaults     = Vault::orderBy('name')->get();

        return view('ga.admin.vault_documents.index', compact('documents', 'categories', 'vaults'));
    }

    public function create()
    {
        $categories = VaultDocumentCategory::where('is_active', true)->orderBy('name')->get();
        $vaults     = Vault::where('is_active', true)->orderBy('name')->get();
        return view('ga.admin.vault_documents.create', ['document' => new VaultDocument, 'categories' => $categories, 'vaults' => $vaults]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:vault_document_categories,id',
            'vault_id'    => 'required|exists:vaults,id',
            'detail'      => 'required|string',
            'is_active'   => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $document = VaultDocument::create($data);

        return redirect()->route('ga.admin.vault-documents.show', $document)
            ->with('status', 'Dokumen berhasil ditambahkan.');
    }

    public function show(VaultDocument $document)
    {
        $document->load(['category', 'vault']);
        $transactions = $document->transactions()->with('creator')->latest('transaction_date')->latest('id')->get();

        return view('ga.admin.vault_documents.show', compact('document', 'transactions'));
    }

    public function edit(VaultDocument $document)
    {
        $categories = VaultDocumentCategory::where('is_active', true)->orderBy('name')->get();
        $vaults     = Vault::where('is_active', true)->orderBy('name')->get();
        return view('ga.admin.vault_documents.create', compact('document', 'categories', 'vaults'));
    }

    public function update(Request $request, VaultDocument $document)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:vault_document_categories,id',
            'vault_id'    => 'required|exists:vaults,id',
            'detail'      => 'required|string',
            'is_active'   => 'boolean',
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
