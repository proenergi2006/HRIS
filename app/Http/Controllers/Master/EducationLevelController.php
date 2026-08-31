<?php

namespace App\Http\Controllers\Master;

use App\Models\Master\EducationLevel;

class EducationLevelController extends SimpleMasterController
{
    protected string $model         = EducationLevel::class;
    protected string $table         = 'education_levels';
    protected string $routeBase     = 'master.education-levels';
    protected string $titlePlural   = 'Jenjang Pendidikan';
    protected string $titleSingular = 'jenjang pendidikan';

    protected function blockingRelations(): array
    {
        return [];
    }
}
