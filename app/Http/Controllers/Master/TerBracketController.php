<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Payroll\TerBracket;
use App\Models\Payroll\TerCategory;
use Illuminate\Http\Request;

/**
 * Tabel tarif TER PPh21 — admin-editable tanpa developer (PRD NFR "bisa diperbarui saat
 * ada perubahan aturan"). Seed awal ilustratif, lihat catatan di
 * App\Services\Payroll\Pph21Calculator.
 */
class TerBracketController extends Controller
{
    public function index()
    {
        $categories = TerCategory::with('brackets')->orderBy('code')->get();
        return view('master.ter-bracket.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        TerBracket::create($data);

        return back()->with('status', 'Lapisan tarif berhasil ditambahkan.');
    }

    public function update(Request $request, TerBracket $terBracket)
    {
        $data = $this->validated($request);
        $terBracket->update($data);

        return back()->with('status', 'Lapisan tarif berhasil diperbarui.');
    }

    public function destroy(TerBracket $terBracket)
    {
        $terBracket->delete();
        return back()->with('status', 'Lapisan tarif berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        // Nominal ber-format Rupiah ("5.000.000") → buang pemisah non-digit dulu.
        foreach (['income_from', 'income_to'] as $money) {
            if ($request->filled($money)) {
                $request->merge([$money => preg_replace('/\D/', '', (string) $request->input($money))]);
            }
        }

        return $request->validate([
            'ter_category_id' => 'required|exists:ter_categories,id',
            'income_from'     => 'required|integer|min:0',
            'income_to'       => 'nullable|integer|min:0|gt:income_from',
            'rate_percent'    => 'required|numeric|min:0|max:100',
        ]);
    }
}
