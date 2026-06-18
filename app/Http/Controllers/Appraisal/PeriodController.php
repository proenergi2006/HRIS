<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Appraisal\AppraisalPeriod;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PeriodController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function index()
    {
        $periods = AppraisalPeriod::withCount('appraisals')->orderByDesc('year')->orderByDesc('id')->get();
        return view('appraisal.period.index', compact('periods'));
    }

    public function create()
    {
        return view('appraisal.period.edit', ['period' => new AppraisalPeriod()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        AppraisalPeriod::create($data);
        return redirect()->route('appraisal.periods.index')->with('status', 'Periode berhasil dibuat.');
    }

    public function edit(AppraisalPeriod $period)
    {
        return view('appraisal.period.edit', compact('period'));
    }

    public function update(Request $request, AppraisalPeriod $period)
    {
        $data = $this->validated($request);
        $period->update($data);
        return redirect()->route('appraisal.periods.index')->with('status', 'Periode berhasil diperbarui.');
    }

    public function destroy(AppraisalPeriod $period)
    {
        if ($period->appraisals()->count() > 0) {
            return back()->with('error', 'Periode tidak bisa dihapus karena sudah memiliki data penilaian.');
        }
        $period->delete();
        return redirect()->route('appraisal.periods.index')->with('status', 'Periode berhasil dihapus.');
    }

    public function toggle(AppraisalPeriod $period)
    {
        $period->update([
            'status' => $period->status === 'open' ? 'closed' : 'open',
        ]);
        $label = $period->status === 'open' ? 'dibuka' : 'ditutup';
        return back()->with('status', "Periode \"{$period->name}\" berhasil {$label}.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'       => 'required|string|max:255',
            'year'       => 'required|integer|min:2000|max:2100',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'status'     => 'required|in:open,closed',
        ]);
    }
}
