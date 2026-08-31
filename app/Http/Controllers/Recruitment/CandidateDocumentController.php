<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\CandidateDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Dokumen Pre-Employment (KTP, ijazah, hasil MCU, SKCK, dst.) — pola sama EmployeeDocumentController. */
class CandidateDocumentController extends Controller
{
    public function store(Request $request, Candidate $candidate)
    {
        $data = $request->validate([
            'doc_type' => 'required|string|max:60',
            'title'    => 'required|string|max:200',
            'file'     => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes'    => 'nullable|string|max:1000',
        ]);

        $file         = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $path         = $file->store('candidate-documents/' . $candidate->id, 'local');

        $candidate->documents()->create([
            'doc_type'      => $data['doc_type'],
            'title'         => $data['title'],
            'file_path'     => $path,
            'original_name' => $originalName,
            'notes'         => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function download(Candidate $candidate, CandidateDocument $document)
    {
        abort_if($document->candidate_id !== $candidate->id, 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }

    public function destroy(Candidate $candidate, CandidateDocument $document)
    {
        abort_if($document->candidate_id !== $candidate->id, 404);

        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Dokumen dihapus.');
    }
}
