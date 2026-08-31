<?php

namespace App\Http\Controllers\Appraisal;

use Illuminate\Http\Request;

class EmployeeEducationController extends EmployeeSubResourceController
{
    protected string $relation = 'educations';
    protected string $label    = 'Riwayat pendidikan';
    protected string $tab      = 'tab-education';

    protected function rules(Request $request): array
    {
        return [
            'education_level_id' => 'nullable|exists:education_levels,id',
            'education_major_id' => 'nullable|exists:education_majors,id',
            'institution'        => 'nullable|string|max:200',
            'graduation_year'    => 'nullable|integer|min:1950|max:' . (now()->year + 1),
            'gpa'                => 'nullable|numeric|min:0|max:4',
            'notes'              => 'nullable|string|max:255',
        ];
    }
}
