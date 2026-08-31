<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\TrainingParticipant;
use App\Models\TrainingProgram;
use Illuminate\Http\Request;

/** Peserta + training record (PRD Bab 3 modul #11). */
class TrainingParticipantController extends Controller
{
    public function index(Request $request)
    {
        $query = TrainingParticipant::with(['program', 'employee']);

        if ($request->filled('training_program_id')) {
            $query->where('training_program_id', $request->training_program_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $participants = $query->latest('start_date')->get();
        $programs     = TrainingProgram::where('is_active', true)->orderBy('title')->get();
        $employees    = Employee::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('training.participant.index', compact('participants', 'programs', 'employees'));
    }

    public function store(Request $request)
    {
        TrainingParticipant::create($this->validated($request));

        return back()->with('status', 'Peserta training berhasil ditambahkan.');
    }

    public function update(Request $request, TrainingParticipant $participant)
    {
        $participant->update($this->validated($request));

        return back()->with('status', 'Data peserta training berhasil diperbarui.');
    }

    public function destroy(TrainingParticipant $participant)
    {
        $participant->delete();

        return back()->with('status', 'Peserta training berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'training_program_id' => 'required|exists:training_programs,id',
            'employee_id'          => 'required|exists:employees,id',
            'start_date'           => 'required|date',
            'end_date'             => 'nullable|date|after_or_equal:start_date',
            'status'               => 'required|in:planned,ongoing,completed,cancelled,no_show',
            'score'                => 'nullable|string|max:20',
            'certificate_number'   => 'nullable|string|max:100',
            'notes'                => 'nullable|string|max:1000',
        ]);
    }
}
