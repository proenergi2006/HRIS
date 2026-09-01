<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\HR\Kudos;
use App\Notifications\GenericNotification;
use Illuminate\Http\Request;

/** Recognition / Kudos — apresiasi non-finansial antar karyawan, semua user login. */
class KudosController extends Controller
{
    public function index(Request $request)
    {
        $employee = $request->user()->employee;

        $feed = Kudos::with(['fromEmployee', 'toEmployee'])
            ->latest()->take(30)->get();

        $colleagues = $employee
            ? Employee::where('is_active', true)->where('company_id', $employee->company_id)
                ->where('id', '!=', $employee->id)->orderBy('name')->get()
            : collect();

        $leaderboard = Kudos::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->selectRaw('to_employee_id, count(*) as total')
            ->groupBy('to_employee_id')
            ->orderByDesc('total')
            ->with('toEmployee')
            ->take(5)->get();

        return view('kudos.index', compact('feed', 'colleagues', 'leaderboard', 'employee'));
    }

    public function store(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun Anda belum terhubung ke data karyawan.');

        $data = $request->validate([
            'to_employee_id' => 'required|exists:employees,id',
            'category'       => 'required|in:teamwork,innovation,leadership,customer_focus,integrity,excellence',
            'message'        => 'required|string|max:500',
        ]);
        abort_if((int) $data['to_employee_id'] === $employee->id, 422, 'Tidak bisa memberi apresiasi ke diri sendiri.');

        $kudos = Kudos::create([
            'from_employee_id' => $employee->id,
            'to_employee_id'   => $data['to_employee_id'],
            'company_id'       => $employee->company_id,
            'category'         => $data['category'],
            'message'          => $data['message'],
        ]);

        $toUser = $kudos->toEmployee?->user;
        if ($toUser) {
            $toUser->notify(new GenericNotification(
                'Anda Mendapat Apresiasi!',
                $employee->name . ' — ' . Kudos::$categoryLabels[$data['category']] . ': "' . \Illuminate\Support\Str::limit($data['message'], 80) . '"',
                route('kudos.index'),
                Kudos::$categoryIcons[$data['category']]
            ));
        }

        return back()->with('success', 'Apresiasi terkirim ke ' . $kudos->toEmployee?->name . '.');
    }
}
