<?php

namespace App\Http\Controllers\Master;

use App\Models\Master\BloodType;

class BloodTypeController extends SimpleMasterController
{
    protected string $model         = BloodType::class;
    protected string $table         = 'blood_types';
    protected string $routeBase     = 'master.blood-types';
    protected string $titlePlural   = 'Golongan Darah';
    protected string $titleSingular = 'golongan darah';
}
