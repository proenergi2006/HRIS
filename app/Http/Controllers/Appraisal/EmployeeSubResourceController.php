<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;

/**
 * Basis untuk sub-data karyawan yang 1-ke-banyak (Pendidikan, Pengalaman Kerja,
 * Skill, Riwayat Organisasi, Fasilitas, Rekening Bank, Kontrak).
 *
 * Pola sama dengan EmployeeFamilyMemberController: form inline di halaman
 * Edit Karyawan, redirect balik ke sana dengan flash `success`.
 */
abstract class EmployeeSubResourceController extends Controller
{
    /** Nama relasi hasMany di model Employee. */
    protected string $relation;

    /** Label singular untuk pesan flash, mis. "Riwayat pendidikan". */
    protected string $label;

    /** Anchor tab di halaman edit, mis. "tab-education". */
    protected string $tab = '';

    /** @return array<string, mixed> rules validasi */
    abstract protected function rules(Request $request): array;

    /** Hook untuk normalisasi data sebelum simpan (mis. cast boolean). */
    protected function transform(array $data, Request $request): array
    {
        return $data;
    }

    public function store(Request $request, Employee $employee)
    {
        $data = $this->transform($request->validate($this->rules($request)), $request);
        $employee->{$this->relation}()->create($data);

        return $this->back($employee, "{$this->label} berhasil ditambahkan.");
    }

    public function update(Request $request, Employee $employee, int $child)
    {
        $row = $employee->{$this->relation}()->findOrFail($child);
        $data = $this->transform($request->validate($this->rules($request)), $request);
        $row->update($data);

        return $this->back($employee, "{$this->label} berhasil diperbarui.");
    }

    public function destroy(Employee $employee, int $child)
    {
        $employee->{$this->relation}()->findOrFail($child)->delete();

        return $this->back($employee, "{$this->label} berhasil dihapus.");
    }

    protected function back(Employee $employee, string $message)
    {
        $url = route('appraisal.employees.edit', $employee) . ($this->tab ? '#' . $this->tab : '');

        return redirect($url)->with('success', $message);
    }
}
