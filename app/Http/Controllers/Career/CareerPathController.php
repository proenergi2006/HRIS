<?php

namespace App\Http\Controllers\Career;

use App\Http\Controllers\Controller;
use App\Models\CareerPath;
use App\Models\CareerPathStep;
use App\Models\Company;
use App\Models\Position;
use Illuminate\Http\Request;

/** Career Path — template jenjang jabatan berurutan (PRD Bab 3 modul #12). */
class CareerPathController extends Controller
{
    public function index()
    {
        $paths     = CareerPath::with('steps.position')->withCount('employees')->orderBy('title')->get();
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $positions = Position::where('is_active', true)->orderBy('name')->get();

        return view('career.path.index', compact('paths', 'companies', 'positions'));
    }

    public function store(Request $request)
    {
        $path = CareerPath::create($this->validated($request));

        return back()->with('status', 'Career path "' . $path->title . '" berhasil dibuat.');
    }

    public function update(Request $request, CareerPath $path)
    {
        $path->update($this->validated($request));

        return back()->with('status', 'Career path berhasil diperbarui.');
    }

    public function destroy(CareerPath $path)
    {
        if ($path->employees()->exists()) {
            return back()->with('error', 'Career path tidak bisa dihapus karena masih dipakai karyawan.');
        }

        $path->delete();

        return back()->with('status', 'Career path berhasil dihapus.');
    }

    public function storeStep(Request $request, CareerPath $path)
    {
        $data = $request->validate([
            'position_id' => 'required|exists:positions,id',
            'step_order'  => 'nullable|integer|min:0',
            'notes'       => 'nullable|string|max:500',
        ]);
        $data['step_order'] = $data['step_order'] ?? ($path->steps()->max('step_order') + 1);

        $path->steps()->create($data);

        return back()->with('status', 'Jenjang jabatan ditambahkan.');
    }

    public function destroyStep(CareerPath $path, CareerPathStep $step)
    {
        abort_if($step->career_path_id !== $path->id, 404);
        $step->delete();

        return back()->with('status', 'Jenjang jabatan dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'company_id'  => 'nullable|exists:companies,id',
            'title'       => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'is_active'   => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
