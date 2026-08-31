<?php

namespace App\Http\Controllers\Appraisal;

use Illuminate\Http\Request;

class EmployeeWorkExperienceController extends EmployeeSubResourceController
{
    protected string $relation = 'workExperiences';
    protected string $label    = 'Pengalaman kerja';
    protected string $tab      = 'tab-work-exp';

    protected function rules(Request $request): array
    {
        return [
            'company_name'    => 'required|string|max:200',
            'company_city'    => 'nullable|string|max:100',
            'phone'           => 'nullable|string|max:30',
            'start_date'      => 'nullable|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'end_job_title'   => 'nullable|string|max:150',
            'end_pay_rate'    => 'nullable|integer|min:0',
            'job_description' => 'nullable|string',
            'remarks'         => 'nullable|string',
        ];
    }
}
