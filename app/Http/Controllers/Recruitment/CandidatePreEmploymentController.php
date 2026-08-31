<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\CandidatePreEmploymentTask;
use App\Models\PreEmploymentChecklistItem;
use Illuminate\Http\Request;

/**
 * Pre-Employment (PRD Bab 3 modul #4) — data terstruktur + checklist wajib
 * sebelum kandidat "accepted" dikonversi jadi Employee.
 */
class CandidatePreEmploymentController extends Controller
{
    /** Simpan data pre-employment terstruktur (KTP/NPWP/rekening/BPJS/personal). */
    public function update(Request $request, Candidate $candidate)
    {
        abort_if($candidate->isConverted(), 422, 'Kandidat sudah jadi karyawan.');

        $data = $request->validate([
            'gender'                     => 'nullable|in:L,P',
            'birth_place'                => 'nullable|string|max:100',
            'birth_date'                 => 'nullable|date',
            'marital_status_id'          => 'nullable|exists:marital_statuses,id',
            'religion_id'                => 'nullable|exists:religions,id',
            'blood_type_id'              => 'nullable|exists:blood_types,id',
            'ktp_number'                 => 'nullable|string|max:30',
            'npwp_number'                => 'nullable|string|max:30',
            'ktp_address'                => 'nullable|string|max:255',
            'ktp_city'                   => 'nullable|string|max:100',
            'domicile_address'           => 'nullable|string|max:255',
            'domicile_city'              => 'nullable|string|max:100',
            'bank_id'                    => 'nullable|exists:banks,id',
            'bank_account_number'        => 'nullable|string|max:40',
            'bank_account_holder'        => 'nullable|string|max:150',
            'bpjs_health_number'         => 'nullable|string|max:50',
            'bpjs_health_date'           => 'nullable|date',
            'bpjs_employment_number'     => 'nullable|string|max:50',
            'bpjs_employment_date'       => 'nullable|date',
            'emergency_contact_name'     => 'nullable|string|max:120',
            'emergency_contact_relation' => 'nullable|string|max:60',
            'emergency_contact_phone'    => 'nullable|string|max:30',
            'notes'                      => 'nullable|string|max:1000',
        ]);

        $candidate->preEmployment()->updateOrCreate([], $data);

        return back()->with('success', 'Data pre-employment disimpan.');
    }

    public function toggleTask(Request $request, Candidate $candidate, CandidatePreEmploymentTask $task)
    {
        abort_if($task->candidate_id !== $candidate->id, 404);
        abort_if($candidate->isConverted(), 422);

        $done = ! $task->is_done;
        $task->update([
            'is_done'         => $done,
            'done_at'         => $done ? now() : null,
            'done_by_user_id' => $done ? auth()->id() : null,
            'notes'           => $request->string('notes')->toString() ?: $task->notes,
        ]);

        return back()->with('success', $done ? 'Item ditandai selesai.' : 'Item dibatalkan.');
    }

    /** Buat task checklist pre-employment dari template aktif (global + company requisition). */
    public static function materialize(Candidate $candidate): void
    {
        $companyId = $candidate->jobRequisition?->company_id;

        $items = PreEmploymentChecklistItem::where('is_active', true)
            ->where(fn ($q) => $q->whereNull('company_id')->when($companyId, fn ($q) => $q->orWhere('company_id', $companyId)))
            ->get();

        foreach ($items as $item) {
            CandidatePreEmploymentTask::firstOrCreate([
                'candidate_id'                    => $candidate->id,
                'preemployment_checklist_item_id' => $item->id,
            ]);
        }
    }
}
