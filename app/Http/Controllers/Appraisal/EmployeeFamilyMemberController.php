<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeFamilyMember;
use Illuminate\Http\Request;

class EmployeeFamilyMemberController extends Controller
{
    public function store(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'relation'   => 'required|in:' . implode(',', array_keys(EmployeeFamilyMember::$relationLabels)),
            'birth_date' => 'nullable|date|before_or_equal:' . now()->format('Y-m-d'),
        ]);

        if ($error = $this->limitError($employee, $data['relation'])) {
            return redirect()->route('appraisal.employees.edit', $employee)->with('error', $error);
        }

        $employee->familyMembers()->create($data);

        return redirect()->route('appraisal.employees.edit', $employee)
            ->with('success', 'Anggota keluarga berhasil ditambahkan.');
    }

    public function update(Request $request, Employee $employee, EmployeeFamilyMember $familyMember)
    {
        abort_if($familyMember->employee_id !== $employee->id, 404);

        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'relation'   => 'required|in:' . implode(',', array_keys(EmployeeFamilyMember::$relationLabels)),
            'birth_date' => 'nullable|date|before_or_equal:' . now()->format('Y-m-d'),
        ]);

        if ($error = $this->limitError($employee, $data['relation'], $familyMember->id)) {
            return redirect()->route('appraisal.employees.edit', $employee)->with('error', $error);
        }

        $familyMember->update($data);

        return redirect()->route('appraisal.employees.edit', $employee)
            ->with('success', 'Anggota keluarga berhasil diperbarui.');
    }

    /**
     * Cek batas jumlah per jenis hubungan (maks 1 istri/suami, 3 anak) —
     * dihitung ulang tiap simpan supaya juga menangkap ganti relasi lewat edit
     * (mis. baris "Anak" diubah jadi "Istri/Suami").
     */
    private function limitError(Employee $employee, string $relation, ?int $ignoreId = null): ?string
    {
        $max = EmployeeFamilyMember::$maxCounts[$relation] ?? null;
        if ($max === null) {
            return null;
        }

        $count = $employee->familyMembers()
            ->where('relation', $relation)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->count();

        if ($count >= $max) {
            $label = EmployeeFamilyMember::$relationLabels[$relation] ?? $relation;

            return "Maksimal {$max} \"{$label}\" per karyawan sudah tercapai.";
        }

        return null;
    }

    public function destroy(Employee $employee, EmployeeFamilyMember $familyMember)
    {
        abort_if($familyMember->employee_id !== $employee->id, 404);

        $familyMember->delete();

        return redirect()->route('appraisal.employees.edit', $employee)
            ->with('success', 'Anggota keluarga berhasil dihapus.');
    }
}
