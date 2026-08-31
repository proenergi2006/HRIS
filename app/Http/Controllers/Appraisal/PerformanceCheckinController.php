<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Appraisal\PerformanceCheckin;
use App\Models\Employee;
use Illuminate\Http\Request;

/** Continuous Feedback / 1-on-1 — pelengkap appraisal formal (PRD gap: performance maturity). */
class PerformanceCheckinController extends Controller
{
    /** Daftar karyawan — manager lihat bawahannya, HR (appraisal-config) lihat semua. */
    public function index(Request $request)
    {
        $user = $request->user();
        $isHr = $user->can('appraisal-config.view');

        $employees = $isHr
            ? Employee::where('is_active', true)->orderBy('name')->get()
            : ($user->employee?->subordinates()->where('is_active', true)->orderBy('name')->get() ?? collect());

        $employees = $employees->map(function ($e) {
            $e->setAttribute('last_checkin', $e->performanceCheckins()->latest('checkin_date')->first());
            return $e;
        });

        return view('appraisal.checkin.index', compact('employees', 'isHr'));
    }

    public function show(Employee $employee)
    {
        $checkins = $employee->performanceCheckins()->with('createdBy')->orderByDesc('checkin_date')->get();

        return view('appraisal.checkin.show', compact('employee', 'checkins'));
    }

    public function store(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'checkin_date'       => 'required|date',
            'notes'              => 'required|string|max:3000',
            'action_items'       => 'nullable|string|max:2000',
            'next_checkin_date'  => 'nullable|date|after_or_equal:checkin_date',
        ]);
        $data['employee_id'] = $employee->id;
        $data['created_by_user_id'] = $request->user()->id;

        PerformanceCheckin::create($data);

        return back()->with('success', 'Catatan 1-on-1 ditambahkan.');
    }

    /** Karyawan menambah komentar sendiri ke check-in yang sudah ada. */
    public function comment(Request $request, PerformanceCheckin $checkin)
    {
        $employee = $request->user()->employee;
        abort_unless($employee && $checkin->employee_id === $employee->id, 403);

        $request->validate(['employee_comment' => 'required|string|max:2000']);
        $checkin->update(['employee_comment' => $request->employee_comment]);

        return back()->with('success', 'Komentar tersimpan.');
    }

    public function destroy(PerformanceCheckin $checkin)
    {
        $employee = $checkin->employee; // ambil dulu (bukan cuma id) -> route() perlu model utk hashid, bukan int mentah
        $checkin->delete();

        return redirect()->route('appraisal.checkins.show', $employee)->with('success', 'Catatan dihapus.');
    }

    /** ESS — karyawan lihat riwayat 1-on-1 miliknya sendiri. */
    public function mine(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 404, 'Akun Anda belum terhubung ke data karyawan.');

        $checkins = $employee->performanceCheckins()->with('createdBy')->orderByDesc('checkin_date')->get();

        return view('appraisal.checkin.mine', compact('employee', 'checkins'));
    }
}
