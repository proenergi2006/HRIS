<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Section;
use App\Traits\LogsOrgChanges;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    use LogsOrgChanges;

    private array $logFields = ['department_id', 'name', 'code', 'head_employee_id', 'is_active'];

    public function index()
    {
        $sections = Section::with(['department.company', 'head'])
            ->withCount(['positions', 'employees'])
            ->orderBy('department_id')->orderBy('name')
            ->get();

        $departments = Department::with('company')->where('is_active', true)->orderBy('name')->get();
        $employees   = Employee::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('appraisal.section.index', compact('sections', 'departments', 'employees'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $section = Section::create($data);
        $this->logOrgChange('section', $section, 'created', [], $request->date('effective_date'));

        return back()->with('status', 'Section berhasil ditambahkan.');
    }

    public function update(Request $request, Section $section)
    {
        $data     = $this->validated($request, $section->id);
        $original = $section->getOriginal();

        $section->update($data);

        $diff = $this->diffOrgAttributes($section, $original, $this->logFields);
        if ($diff) {
            $action = array_key_exists('department_id', $diff) ? 'moved' : 'updated';
            $this->logOrgChange('section', $section, $action, $diff, $request->date('effective_date'));
        }

        return back()->with('status', 'Section berhasil diperbarui.');
    }

    /**
     * Pindah section ke departemen lain (dipakai drag & drop di bagan organisasi).
     * Payload minimal — cuma department_id — tetap tercatat 'moved' di org_change_logs.
     */
    public function reparent(Request $request, Section $section)
    {
        $data = $request->validate([
            'department_id' => 'required|exists:departments,id',
        ]);

        $original = $section->getOriginal();
        $section->update(['department_id' => $data['department_id']]);

        $diff = $this->diffOrgAttributes($section, $original, $this->logFields);
        if ($diff) {
            $this->logOrgChange('section', $section, 'moved', $diff);
        }

        return response()->json(['ok' => true]);
    }

    public function destroy(Section $section)
    {
        if ($section->positions()->exists() || $section->employees()->exists()) {
            return back()->with('error', 'Section tidak bisa dihapus karena masih dipakai jabatan/karyawan.');
        }

        $this->logOrgChange('section', $section, 'deleted');
        $section->delete();

        return back()->with('status', 'Section berhasil dihapus.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'department_id'    => 'required|exists:departments,id',
            'code'             => 'required|string|max:20|unique:sections,code' . ($ignoreId ? ',' . $ignoreId : ''),
            'name'             => 'required|string|max:150',
            'head_employee_id' => 'nullable|exists:employees,id',
            'is_active'        => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
