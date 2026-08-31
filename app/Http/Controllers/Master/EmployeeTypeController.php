<?php

namespace App\Http\Controllers\Master;

use App\Models\Master\EmployeeType;

class EmployeeTypeController extends SimpleMasterController
{
    protected string $model         = EmployeeType::class;
    protected string $table         = 'employee_types';
    protected string $routeBase     = 'master.employee-types';
    protected string $titlePlural   = 'Tipe Karyawan';
    protected string $titleSingular = 'tipe karyawan';
}
