<?php

namespace App\Http\Controllers\Appraisal;

use Illuminate\Http\Request;

class EmployeeFacilityController extends EmployeeSubResourceController
{
    protected string $relation = 'facilities';
    protected string $label    = 'Fasilitas';
    protected string $tab      = 'tab-facility';

    protected function rules(Request $request): array
    {
        return [
            'name'          => 'required|string|max:150',
            'description'   => 'nullable|string|max:255',
            'received_date' => 'nullable|date',
            'returned_date' => 'nullable|date|after_or_equal:received_date',
            'remarks'       => 'nullable|string',
        ];
    }
}
