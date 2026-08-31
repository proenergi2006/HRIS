<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Master\Bank;
use App\Models\Master\CompanyBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyBankController extends Controller
{
    public function index()
    {
        $companyBanks = CompanyBank::with(['company', 'bank'])
            ->orderBy('company_id')
            ->orderByDesc('is_primary')
            ->get();

        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $banks     = Bank::active()->ordered()->get();

        return view('master.company_bank.index', compact('companyBanks', 'companies', 'banks'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->save(new CompanyBank(), $data, $request);

        return back()->with('status', 'Rekening perusahaan berhasil ditambahkan.');
    }

    public function update(Request $request, CompanyBank $companyBank)
    {
        $data = $this->validated($request, $companyBank->id);
        $this->save($companyBank, $data, $request);

        return back()->with('status', 'Rekening perusahaan berhasil diperbarui.');
    }

    public function destroy(CompanyBank $companyBank)
    {
        $companyBank->delete();

        return back()->with('status', 'Rekening perusahaan berhasil dihapus.');
    }

    private function save(CompanyBank $model, array $data, Request $request): void
    {
        $data['is_primary'] = $request->boolean('is_primary');
        $data['is_active']  = $request->boolean('is_active', true);

        DB::transaction(function () use ($model, $data) {
            $model->fill($data)->save();

            // Hanya boleh ada satu rekening utama per perusahaan.
            if ($model->is_primary) {
                CompanyBank::where('company_id', $model->company_id)
                    ->whereKeyNot($model->getKey())
                    ->update(['is_primary' => false]);
            }
        });
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'company_id'     => 'required|exists:companies,id',
            'bank_id'        => 'required|exists:banks,id',
            'account_number' => 'required|string|max:50',
            'account_name'   => 'required|string|max:150',
            'branch_name'    => 'nullable|string|max:150',
        ]);
    }
}
