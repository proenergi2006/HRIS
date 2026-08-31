<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\TrainingProgram;
use Illuminate\Http\Request;

/** Katalog program training — light-CRUD (PRD Bab 3 modul #11). */
class TrainingProgramController extends Controller
{
    public function index()
    {
        $programs  = TrainingProgram::with('company')->withCount('participants')->orderBy('title')->get();
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('training.program.index', compact('programs', 'companies'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        TrainingProgram::create($data);

        return back()->with('status', 'Program training berhasil ditambahkan.');
    }

    public function update(Request $request, TrainingProgram $program)
    {
        $program->update($this->validated($request));

        return back()->with('status', 'Program training berhasil diperbarui.');
    }

    public function destroy(TrainingProgram $program)
    {
        if ($program->participants()->exists()) {
            return back()->with('error', 'Program tidak bisa dihapus karena masih punya peserta.');
        }

        $program->delete();

        return back()->with('status', 'Program training berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'company_id'     => 'nullable|exists:companies,id',
            'title'          => 'required|string|max:200',
            'category'       => 'nullable|string|max:100',
            'provider'       => 'nullable|string|max:150',
            'duration_hours' => 'nullable|integer|min:0',
            'description'    => 'nullable|string|max:2000',
            'is_active'      => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
