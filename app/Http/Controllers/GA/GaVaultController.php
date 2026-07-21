<?php

namespace App\Http\Controllers\GA;

use App\Http\Controllers\Controller;
use App\Models\GA\Vault;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GaVaultController extends Controller
{
    public function index()
    {
        $vaults = Vault::withCount('documents')->orderBy('name')->get();
        return view('ga.admin.vaults.index', compact('vaults'));
    }

    public function create()
    {
        return view('ga.admin.vaults.create', ['vault' => new Vault]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100|unique:vaults,name',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $vault = Vault::create($data);

        return redirect()->route('ga.admin.vaults.show', $vault)
            ->with('status', 'Berangkas berhasil ditambahkan. Barcode: ' . $vault->barcode);
    }

    public function show(Vault $vault)
    {
        $documents = $vault->documents()->withCount('transactions')->latest()->get();
        return view('ga.admin.vaults.show', compact('vault', 'documents'));
    }

    public function edit(Vault $vault)
    {
        return view('ga.admin.vaults.create', ['vault' => $vault]);
    }

    public function update(Request $request, Vault $vault)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100|unique:vaults,name,' . $vault->id,
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $vault->update($data);

        return redirect()->route('ga.admin.vaults.index')->with('status', 'Berangkas berhasil diperbarui.');
    }

    public function destroy(Vault $vault)
    {
        if ($vault->documents()->exists()) {
            return back()->with('error', 'Berangkas tidak bisa dihapus karena masih berisi dokumen.');
        }

        $vault->delete();
        return redirect()->route('ga.admin.vaults.index')->with('status', 'Berangkas berhasil dihapus.');
    }

    public function qrcode(Vault $vault)
    {
        $url = route('ga.vault.scan', $vault);
        $qr  = QrCode::format('svg')->size(300)->errorCorrection('H')->generate($url);

        return view('ga.admin.vaults.qrcode', compact('vault', 'qr', 'url'));
    }
}
