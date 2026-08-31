<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\Level;
use App\Models\Position;
use App\Models\Section;
use App\Traits\LogsOrgChanges;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PositionController extends Controller
{
    use LogsOrgChanges;

    private array $logFields = [
        'company_id', 'department_id', 'section_id', 'level_id', 'reports_to_position_id',
        'name', 'code', 'job_description', 'tunjangan_jabatan', 'tunjangan_harian', 'tarif_lembur', 'is_active',
    ];

    public function index()
    {
        $positions   = Position::with(['company', 'department', 'section', 'level', 'reportsTo'])
            ->withCount('employees')->orderBy('name')->get();
        $companies   = Company::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $sections    = Section::with('department')->where('is_active', true)->orderBy('name')->get();
        $levels      = Level::orderBy('rank')->orderBy('name')->get();
        $allPositions = Position::orderBy('name')->get(['id', 'name']);

        return view('appraisal.position.index', compact(
            'positions', 'companies', 'departments', 'sections', 'levels', 'allPositions'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $position = Position::create($data);
        $this->logOrgChange('position', $position, 'created', [], $request->date('effective_date'));

        return back()->with('status', 'Jabatan berhasil ditambahkan.');
    }

    public function update(Request $request, Position $position)
    {
        $data     = $this->validated($request, $position->id);
        $original = $position->getOriginal();

        $position->update($data);

        $diff = $this->diffOrgAttributes($position, $original, $this->logFields);
        if ($diff) {
            $moved  = array_intersect(['company_id', 'department_id', 'section_id', 'reports_to_position_id'], array_keys($diff));
            $this->logOrgChange('position', $position, $moved ? 'moved' : 'updated', $diff, $request->date('effective_date'));
        }

        return back()->with('status', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(Position $position)
    {
        if ($position->employees()->exists()) {
            return back()->with('error', 'Jabatan tidak bisa dihapus karena masih dipakai karyawan.');
        }

        if ($position->reports()->exists()) {
            return back()->with('error', 'Jabatan tidak bisa dihapus karena jadi atasan jabatan lain.');
        }

        $this->logOrgChange('position', $position, 'deleted');
        $position->delete();

        return back()->with('status', 'Jabatan berhasil dihapus.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        // Field nominal ber-format Rupiah ("5.000.000") — buang pemisah non-digit
        // dulu supaya lolos aturan integer walau JS pembersih titik tidak jalan.
        foreach (['tunjangan_jabatan', 'tunjangan_harian', 'tarif_lembur'] as $money) {
            if ($request->filled($money)) {
                $request->merge([$money => preg_replace('/\D/', '', (string) $request->input($money))]);
            }
        }

        $data = $request->validate([
            'company_id'             => 'nullable|exists:companies,id',
            'department_id'          => 'nullable|exists:departments,id',
            'section_id'             => 'nullable|exists:sections,id',
            'level_id'               => 'nullable|exists:levels,id',
            'reports_to_position_id' => 'nullable|exists:positions,id',
            'code'                   => 'required|string|max:20|unique:positions,code' . ($ignoreId ? ',' . $ignoreId : ''),
            'name'                   => 'required|string|max:150',
            'job_description'        => 'nullable|string',
            'tunjangan_jabatan'      => 'nullable|integer|min:0',
            'tunjangan_harian'       => 'nullable|integer|min:0',
            'tarif_lembur'           => 'nullable|integer|min:0',
            'is_active'              => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        // Jabatan tidak boleh melapor ke dirinya sendiri.
        if ($ignoreId && (int) ($data['reports_to_position_id'] ?? 0) === $ignoreId) {
            $data['reports_to_position_id'] = null;
        }

        // Section yang dipilih harus benar-benar milik Departemen yang dipilih —
        // cegah kombinasi nyasar (mis. Section IT terpasang ke Jabatan di Finance).
        if (! empty($data['section_id'])) {
            $sectionDeptId = Section::where('id', $data['section_id'])->value('department_id');
            if ((int) $sectionDeptId !== (int) ($data['department_id'] ?? 0)) {
                throw ValidationException::withMessages([
                    'section_id' => 'Section yang dipilih bukan bagian dari Departemen ini.',
                ]);
            }
        }

        return $data;
    }
}
