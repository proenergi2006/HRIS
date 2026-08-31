<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\CandidateOffer;
use Illuminate\Http\Request;

class CandidateOfferController extends Controller
{
    public function store(Request $request, Candidate $candidate)
    {
        $data = $this->validated($request);
        $candidate->offers()->create($data);

        return back()->with('success', 'Penawaran ditambahkan.');
    }

    public function update(Request $request, Candidate $candidate, CandidateOffer $offer)
    {
        abort_if($offer->candidate_id !== $candidate->id, 404);
        $offer->update($this->validated($request));

        return back()->with('success', 'Penawaran diperbarui.');
    }

    public function destroy(Candidate $candidate, CandidateOffer $offer)
    {
        abort_if($offer->candidate_id !== $candidate->id, 404);
        $offer->delete();

        return back()->with('success', 'Penawaran dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'position_id'        => 'nullable|exists:positions,id',
            'offered_salary'     => 'nullable|integer|min:0',
            'start_date_offered' => 'nullable|date',
            'status'             => 'required|in:draft,sent,accepted,declined,expired',
            'notes'              => 'nullable|string|max:1000',
        ]);
    }
}
