<?php

namespace App\Http\Controllers\Appraisal;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeBankAccountController extends EmployeeSubResourceController
{
    protected string $relation = 'bankAccounts';
    protected string $label    = 'Rekening bank';
    protected string $tab      = 'tab-bank';

    protected function rules(Request $request): array
    {
        return [
            'bank_id'             => 'required|exists:banks,id',
            'account_number'      => 'required|string|max:50',
            'account_holder_name' => 'required|string|max:150',
            'branch_name'         => 'nullable|string|max:150',
            'is_primary'          => 'boolean',
            'is_active'           => 'boolean',
        ];
    }

    protected function transform(array $data, Request $request): array
    {
        $data['is_primary'] = $request->boolean('is_primary');
        $data['is_active']  = $request->boolean('is_active', true);

        return $data;
    }

    public function store(Request $request, Employee $employee)
    {
        $response = parent::store($request, $employee);
        $this->ensureSinglePrimary($employee);

        return $response;
    }

    public function update(Request $request, Employee $employee, int $child)
    {
        $response = parent::update($request, $employee, $child);
        $this->ensureSinglePrimary($employee, $child);

        return $response;
    }

    private function ensureSinglePrimary(Employee $employee, ?int $keepId = null): void
    {
        $primary = $employee->bankAccounts()->where('is_primary', true)
            ->orderByDesc('id')->first();

        if (! $primary) {
            return;
        }

        $employee->bankAccounts()
            ->where('is_primary', true)
            ->whereKeyNot($primary->getKey())
            ->update(['is_primary' => false]);
    }
}
