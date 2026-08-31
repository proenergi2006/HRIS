<?php

namespace App\Http\Controllers\Competency;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Competency\Competency;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/** Profil kompetensi per jabatan — kompetensi wajib + level minimal. */
class PositionCompetencyController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function index(Request $request)
    {
        $companyId    = $request->integer('company_id') ?: null;
        $departmentId = $request->integer('department_id') ?: null;

        $positions = Position::with(['company', 'department', 'level'])
            ->withCount('competencyRequirements')
            ->where('is_active', true)
            ->when($companyId, fn ($q, $v) => $q->where('company_id', $v))
            ->when($departmentId, fn ($q, $v) => $q->where('department_id', $v))
            ->orderBy('name')
            ->get();

        $companies   = Company::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('competency.positions.index', compact('positions', 'companies', 'departments', 'companyId', 'departmentId'));
    }

    public function show(Position $position)
    {
        $position->load(['company', 'department', 'level', 'competencyRequirements.competency']);

        $mappedIds    = $position->competencyRequirements->pluck('competency_id');
        $competencies = Competency::where('is_active', true)
            ->whereNotIn('id', $mappedIds)
            ->orderBy('category')->orderBy('name')
            ->get();

        return view('competency.positions.show', compact('position', 'competencies'));
    }

    public function store(Request $request, Position $position)
    {
        $data = $request->validate([
            'competency_id'  => 'required|exists:competencies,id',
            'required_level' => 'required|integer|min:1|max:5',
            'notes'          => 'nullable|string|max:255',
        ]);

        if ($position->competencyRequirements()->where('competency_id', $data['competency_id'])->exists()) {
            return back()->with('error', 'Kompetensi itu sudah ada di profil jabatan ini.');
        }

        $position->competencyRequirements()->create($data);

        return back()->with('status', 'Kompetensi ditambahkan ke profil jabatan.');
    }

    public function update(Request $request, Position $position, int $requirement)
    {
        $row = $position->competencyRequirements()->findOrFail($requirement);

        $row->update($request->validate([
            'required_level' => 'required|integer|min:1|max:5',
            'notes'          => 'nullable|string|max:255',
        ]));

        return back()->with('status', 'Profil kompetensi jabatan diperbarui.');
    }

    public function destroy(Position $position, int $requirement)
    {
        $position->competencyRequirements()->findOrFail($requirement)->delete();

        return back()->with('status', 'Kompetensi dihapus dari profil jabatan.');
    }
}
