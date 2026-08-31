<?php

namespace App\Http\Controllers\Master;

use App\Models\Master\MaritalStatus;

class MaritalStatusController extends SimpleMasterController
{
    protected string $model         = MaritalStatus::class;
    protected string $table         = 'marital_statuses';
    protected string $routeBase     = 'master.marital-statuses';
    protected string $titlePlural   = 'Status Pernikahan';
    protected string $titleSingular = 'status pernikahan';

    protected function extraColumns(): array
    {
        return [
            'ptkp_code' => ['label' => 'Kode PTKP', 'width' => '110px'],
        ];
    }

    protected function extraRules(): array
    {
        return [
            'ptkp_code' => 'nullable|string|max:10',
        ];
    }
}
