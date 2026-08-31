<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\HR\TalentPoolMember;
use App\Models\Position;
use Illuminate\Http\Request;

/**
 * Succession Planning: tandai jabatan kritikal (risiko tinggi bila kosong mendadak) +
 * kelola talent pool (kandidat pengganti) per jabatan dengan tingkat kesiapan.
 */
class SuccessionController extends Controller
{
    public function positions(Request $request)
    {
        $companyId = $request->filled('company_id') ? (int) $request->company_id : null;
        $onlyCritical = $request->boolean('critical_only', false);
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        $positions = Position::where('is_active', true)
            ->when($companyId, fn ($q, $v) => $q->where('company_id', $v))
            ->when($onlyCritical, fn ($q) => $q->where('is_critical_position', true))
            ->withCount('talentPool')
            ->with(['company', 'department', 'level'])
            ->orderBy('name')->get();

        return view('hr.succession.positions', compact('positions', 'companies', 'companyId', 'onlyCritical'));
    }

    public function toggleCritical(Request $request, Position $position)
    {
        $data = $request->validate([
            'is_critical_position' => 'nullable|boolean',
            'succession_risk'      => 'nullable|in:low,medium,high',
            'succession_notes'     => 'nullable|string|max:1000',
        ]);
        $data['is_critical_position'] = $request->boolean('is_critical_position');

        $position->update($data);

        return back()->with('success', 'Status jabatan "' . $position->name . '" diperbarui.');
    }

    public function show(Position $position)
    {
        $position->loadMissing(['company', 'department', 'level']);
        $pool = TalentPoolMember::where('position_id', $position->id)
            ->with('employee')->orderBy('readiness')->get();

        $candidateEmployees = Employee::where('is_active', true)
            ->where('company_id', $position->company_id)
            ->whereNotIn('id', $pool->pluck('employee_id'))
            ->orderBy('name')->get();

        return view('hr.succession.show', compact('position', 'pool', 'candidateEmployees'));
    }

    public function storePoolMember(Request $request, Position $position)
    {
        $data = $request->validate([
            'employee_id'        => 'required|exists:employees,id',
            'readiness'          => 'required|in:ready_now,ready_1_2yr,ready_3_5yr,development',
            'development_notes'  => 'nullable|string|max:1000',
        ]);

        TalentPoolMember::updateOrCreate(
            ['position_id' => $position->id, 'employee_id' => $data['employee_id']],
            $data + ['added_by_user_id' => auth()->id()]
        );

        return back()->with('success', 'Kandidat pengganti ditambahkan ke talent pool.');
    }

    public function updatePoolMember(Request $request, TalentPoolMember $member)
    {
        $data = $request->validate([
            'readiness'         => 'required|in:ready_now,ready_1_2yr,ready_3_5yr,development',
            'development_notes' => 'nullable|string|max:1000',
        ]);
        $member->update($data);

        return back()->with('success', 'Talent pool diperbarui.');
    }

    public function destroyPoolMember(TalentPoolMember $member)
    {
        $positionId = $member->position_id;
        $member->delete();

        return redirect()->route('succession.show', ['position' => $positionId])->with('success', 'Kandidat dihapus dari talent pool.');
    }

    public function matrix(Request $request)
    {
        $companyId = $request->filled('company_id') ? (int) $request->company_id : null;
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        $critical = Position::where('is_active', true)
            ->where('is_critical_position', true)
            ->when($companyId, fn ($q, $v) => $q->where('company_id', $v))
            ->with(['company', 'department', 'level', 'talentPool'])
            ->orderBy('name')->get();

        $totalCritical = $critical->count();
        $covered = $critical->filter(fn ($p) => $p->talentPool->isNotEmpty())->count();
        $readyNow = $critical->filter(fn ($p) => $p->talentPool->where('readiness', 'ready_now')->isNotEmpty())->count();
        $gaps = $critical->filter(fn ($p) => $p->talentPool->isEmpty());

        return view('hr.succession.matrix', compact('critical', 'totalCritical', 'covered', 'readyNow', 'gaps', 'companies', 'companyId'));
    }
}
