<?php

namespace App\Http\Controllers\Competency;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Competency\Competency;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/** Kamus kompetensi — light-CRUD inline (Competency Framework, PRD Bab 3 modul #11). */
class CompetencyController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function index()
    {
        $competencies = Competency::with('company')
            ->withCount(['positionCompetencies', 'employeeCompetencies'])
            ->orderBy('category')
            ->orderBy('name')
            ->get();
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('competency.dictionary.index', compact('competencies', 'companies'));
    }

    public function store(Request $request)
    {
        Competency::create($this->validated($request));

        return back()->with('status', 'Kompetensi berhasil ditambahkan.');
    }

    public function update(Request $request, Competency $competency)
    {
        $competency->update($this->validated($request, $competency->id));

        return back()->with('status', 'Kompetensi berhasil diperbarui.');
    }

    public function destroy(Competency $competency)
    {
        if ($competency->positionCompetencies()->exists() || $competency->employeeCompetencies()->exists()) {
            return back()->with('error', 'Kompetensi tidak bisa dihapus karena masih dipakai profil jabatan / penilaian karyawan.');
        }

        $competency->delete();

        return back()->with('status', 'Kompetensi berhasil dihapus.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'company_id'  => 'nullable|exists:companies,id',
            'code'        => 'required|string|max:30|unique:competencies,code' . ($ignoreId ? ',' . $ignoreId : ''),
            'name'        => 'required|string|max:150',
            'category'    => 'nullable|string|max:50',
            'description' => 'nullable|string|max:2000',
            'is_active'   => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
