<?php

namespace App\Http\Controllers\Master;

use App\Models\Master\Bank;

class BankController extends SimpleMasterController
{
    protected string $model         = Bank::class;
    protected string $table         = 'banks';
    protected string $routeBase     = 'master.banks';
    protected string $titlePlural   = 'Bank';
    protected string $titleSingular = 'bank';

    protected function extraColumns(): array
    {
        return [
            'swift_code' => ['label' => 'Kode SWIFT', 'width' => '140px'],
        ];
    }

    protected function extraRules(): array
    {
        return [
            'swift_code' => 'nullable|string|max:20',
        ];
    }

    protected function blockingRelations(): array
    {
        return ['companyBanks'];
    }
}
