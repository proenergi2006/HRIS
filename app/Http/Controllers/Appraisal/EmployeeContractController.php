<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EmployeeContractController extends Controller
{
    public function store(Request $request, Employee $employee)
    {
        $data = $this->validated($request);
        $data['document_path']  = null;
        $data['original_name']  = null;

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $data['document_path'] = $file->store('employee-contracts/' . $employee->id, 'local');
            $data['original_name'] = $file->getClientOriginalName();
        }

        $employee->contracts()->create($data);
        $this->syncSummary($employee);

        return $this->back($employee, 'Kontrak berhasil ditambahkan.');
    }

    public function update(Request $request, Employee $employee, EmployeeContract $contract)
    {
        abort_if($contract->employee_id !== $employee->id, 404);

        $data = $this->validated($request);

        if ($request->hasFile('document')) {
            if ($contract->document_path) {
                Storage::disk('local')->delete($contract->document_path);
            }
            $file = $request->file('document');
            $data['document_path'] = $file->store('employee-contracts/' . $employee->id, 'local');
            $data['original_name'] = $file->getClientOriginalName();
        }

        $contract->update($data);
        $this->syncSummary($employee);

        return $this->back($employee, 'Kontrak berhasil diperbarui.');
    }

    public function destroy(Employee $employee, EmployeeContract $contract)
    {
        abort_if($contract->employee_id !== $employee->id, 404);

        if ($contract->document_path) {
            Storage::disk('local')->delete($contract->document_path);
        }
        $contract->delete();
        $this->syncSummary($employee);

        return $this->back($employee, 'Kontrak berhasil dihapus.');
    }

    public function download(Employee $employee, EmployeeContract $contract)
    {
        abort_if($contract->employee_id !== $employee->id, 404);
        abort_unless($contract->document_path && Storage::disk('local')->exists($contract->document_path), 404);

        return Storage::disk('local')->download($contract->document_path, $contract->original_name ?? 'kontrak.pdf');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'contract_type' => ['required', Rule::in(array_keys(EmployeeContract::$typeLabels))],
            'number'        => 'nullable|string|max:100',
            'start_date'    => 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'status'        => ['required', Rule::in(array_keys(EmployeeContract::$statusLabels))],
            'notes'         => 'nullable|string',
            'document'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);
    }

    /**
     * Selaraskan kolom ringkasan employees.contract_end_date dengan kontrak
     * aktif terakhir (kolom lama masih dipakai modul HR & validasi form).
     */
    private function syncSummary(Employee $employee): void
    {
        $latest = $employee->contracts()
            ->where('status', 'active')
            ->orderByDesc('end_date')
            ->first();

        $employee->forceFill(['contract_end_date' => $latest?->end_date])->save();
    }

    private function back(Employee $employee, string $message)
    {
        return redirect(route('appraisal.employees.edit', $employee) . '#tab-contract')
            ->with('success', $message);
    }
}
