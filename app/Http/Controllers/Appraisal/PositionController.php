<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        $positions   = Position::with(['company', 'department'])->withCount('employees')->orderBy('name')->get();
        $companies   = Company::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('appraisal.position.index', compact('positions', 'companies', 'departments'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Position::create($data);

        return back()->with('status', 'Jabatan berhasil ditambahkan.');
    }

    public function update(Request $request, Position $position)
    {
        $data = $this->validated($request, $position->id);
        $position->update($data);

        return back()->with('status', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(Position $position)
    {
        if ($position->employees()->exists()) {
            return back()->with('error', 'Jabatan tidak bisa dihapus karena masih dipakai karyawan.');
        }

        $position->delete();

        return back()->with('status', 'Jabatan berhasil dihapus.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'company_id'        => 'nullable|exists:companies,id',
            'department_id'     => 'nullable|exists:departments,id',
            'code'              => 'required|string|max:20|unique:positions,code,' . ($ignoreId ?? 'NULL'),
            'name'              => 'required|string|max:150',
            'tunjangan_jabatan' => 'nullable|integer|min:0',
            'tunjangan_harian'  => 'nullable|integer|min:0',
            'tarif_lembur'      => 'nullable|integer|min:0',
            'is_active'         => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
