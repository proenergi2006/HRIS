<?php

namespace App\Http\Controllers\GA;

use App\Http\Controllers\Controller;
use App\Models\GA\VaultDocument;
use App\Models\GA\VaultDocumentCategory;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GaVaultDocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = VaultDocument::with('category')->withCount('transactions')->latest();

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('q')) {
            $query->where(function ($sub) use ($request) {
                $sub->where('barcode', 'like', "%{$request->q}%")
                    ->orWhere('detail', 'like', "%{$request->q}%");
            });
        }

        $documents  = $query->get();
        $categories = VaultDocumentCategory::orderBy('name')->get();

        return view('ga.admin.vault_documents.index', compact('documents', 'categories'));
    }

    public function create()
    {
        $categories = VaultDocumentCategory::where('is_active', true)->orderBy('name')->get();
        return view('ga.admin.vault_documents.create', ['document' => new VaultDocument, 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:vault_document_categories,id',
            'detail'      => 'required|string',
            'is_active'   => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $document = VaultDocument::create($data);

        return redirect()->route('ga.admin.vault-documents.show', $document)
            ->with('status', 'Dokumen berhasil ditambahkan. Barcode: ' . $document->barcode);
    }

    public function show(VaultDocument $document)
    {
        $document->load('category');
        $transactions = $document->transactions()->with('creator')->latest('transaction_date')->latest('id')->get();

        return view('ga.admin.vault_documents.show', compact('document', 'transactions'));
    }

    public function edit(VaultDocument $document)
    {
        $categories = VaultDocumentCategory::where('is_active', true)->orderBy('name')->get();
        return view('ga.admin.vault_documents.create', compact('document', 'categories'));
    }

    public function update(Request $request, VaultDocument $document)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:vault_document_categories,id',
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

    public function qrcode(VaultDocument $document)
    {
        $url = route('ga.vault.scan', $document);
        $qr  = QrCode::format('svg')->size(300)->errorCorrection('H')->generate($url);

        return view('ga.admin.vault_documents.qrcode', compact('document', 'qr', 'url'));
    }
}
