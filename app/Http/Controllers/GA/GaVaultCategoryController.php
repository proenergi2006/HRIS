<?php

namespace App\Http\Controllers\GA;

use App\Http\Controllers\Controller;
use App\Models\GA\VaultDocumentCategory;
use Illuminate\Http\Request;

class GaVaultCategoryController extends Controller
{
    public function index()
    {
        $categories = VaultDocumentCategory::withCount('documents')->orderBy('name')->get();
        return view('ga.admin.vault_categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100|unique:vault_document_categories,name',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        VaultDocumentCategory::create($data);

        return back()->with('status', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, VaultDocumentCategory $category)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100|unique:vault_document_categories,name,' . $category->id,
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $category->update($data);

        return back()->with('status', 'Kategori berhasil diperbarui.');
    }

    public function destroy(VaultDocumentCategory $category)
    {
        if ($category->documents()->exists()) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih dipakai oleh dokumen.');
        }

        $category->delete();
        return back()->with('status', 'Kategori berhasil dihapus.');
    }
}
