<?php

namespace App\Http\Controllers\Master;

use App\Models\Master\EducationMajor;

class EducationMajorController extends SimpleMasterController
{
    protected string $model         = EducationMajor::class;
    protected string $table         = 'education_majors';
    protected string $routeBase     = 'master.education-majors';
    protected string $titlePlural   = 'Jurusan Pendidikan';
    protected string $titleSingular = 'jurusan';

    protected function blockingRelations(): array
    {
        return [];
    }
}
