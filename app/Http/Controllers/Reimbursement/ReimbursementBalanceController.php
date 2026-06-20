<?php

namespace App\Http\Controllers\Reimbursement;

use App\Http\Controllers\Controller;
use App\Models\Reimbursement\ReimbursementBalance;
use App\Models\User;
use Illuminate\Http\Request;

class ReimbursementBalanceController extends Controller
{
    public function index(Request $request)
    {
        $year   = $request->year ?? now()->year;
        $users  = User::orderBy('name')->get();
        $balances = ReimbursementBalance::with('user')
            ->where('period_year', $year)
            ->where('balance_type', 'medical')
            ->orderBy('user_id')
            ->get()
            ->keyBy('user_id');

        return view('admin.reimbursement.balances', compact('users', 'balances', 'year'));
    }

    public function upsert(Request $request)
    {
        $request->validate([
            'year'            => 'required|integer|min:2020|max:2100',
            'balances'        => 'required|array',
            'balances.*.user_id'         => 'required|exists:users,id',
            'balances.*.initial_balance' => 'required|integer|min:0',
        ]);

        foreach ($request->balances as $row) {
            ReimbursementBalance::updateOrCreate(
                [
                    'user_id'      => $row['user_id'],
                    'period_year'  => $request->year,
                    'balance_type' => 'medical',
                ],
                [
                    'initial_balance' => $row['initial_balance'],
                ]
            );
        }

        return back()->with('status', 'Saldo berhasil disimpan.');
    }
}
