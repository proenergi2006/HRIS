<?php

namespace App\Http\Controllers\Appraisal;

use App\Models\EmployeeOrgExperience;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeOrgExperienceController extends EmployeeSubResourceController
{
    protected string $relation = 'orgExperiences';
    protected string $label    = 'Riwayat organisasi';
    protected string $tab      = 'tab-org-exp';

    protected function rules(Request $request): array
    {
        return [
            'company_id'    => 'nullable|exists:companies,id',
            'position_id'   => 'nullable|exists:positions,id',
            'unit_name'     => 'nullable|string|max:200',
            'position_name' => 'nullable|string|max:150',
            'change_type'   => ['required', Rule::in(array_keys(EmployeeOrgExperience::$changeTypeLabels))],
            'start_date'    => 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'remarks'       => 'nullable|string',
        ];
    }
}
