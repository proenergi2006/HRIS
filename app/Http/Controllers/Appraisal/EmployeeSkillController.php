<?php

namespace App\Http\Controllers\Appraisal;

use App\Models\EmployeeSkill;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeSkillController extends EmployeeSubResourceController
{
    protected string $relation = 'skills';
    protected string $label    = 'Skill';
    protected string $tab      = 'tab-skill';

    protected function rules(Request $request): array
    {
        return [
            'name'        => 'required|string|max:150',
            'proficiency' => ['nullable', Rule::in(array_keys(EmployeeSkill::$proficiencyLabels))],
            'notes'       => 'nullable|string|max:255',
        ];
    }
}
