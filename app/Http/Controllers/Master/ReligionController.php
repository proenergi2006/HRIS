<?php

namespace App\Http\Controllers\Master;

use App\Models\Master\Religion;

class ReligionController extends SimpleMasterController
{
    protected string $model         = Religion::class;
    protected string $table         = 'religions';
    protected string $routeBase     = 'master.religions';
    protected string $titlePlural   = 'Agama';
    protected string $titleSingular = 'agama';
}
