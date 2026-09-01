<?php

namespace App\Http\Controllers\Recruitment;

use App\Exports\CandidateImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\CandidateImport;
use App\Models\JobRequisition;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Import kandidat massal dari Excel/CSV ke 1 Job Requisition — dipakai supaya
 * tim rekrutmen tidak ketik ulang satu-satu daftar pelamar yang sudah
 * diseleksi/diekspor dari portal luar (mis. Jobstreet).
 */
class CandidateImportController extends Controller
{
    public function form(JobRequisition $requisition)
    {
        abort_unless($requisition->isOpen(), 422, 'Requisition ini belum/tidak terbuka untuk menerima kandidat.');

        return view('recruitment.candidate.import', compact('requisition'));
    }

    public function template()
    {
        return Excel::download(new CandidateImportTemplateExport(), 'template-import-kandidat.xlsx');
    }

    public function import(Request $request, JobRequisition $requisition)
    {
        abort_unless($requisition->isOpen(), 422, 'Requisition ini belum/tidak terbuka untuk menerima kandidat.');

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new CandidateImport($requisition->id);
        Excel::import($import, $request->file('file'));

        return redirect()->route('recruitment.candidates.import.form', $requisition)->with('import_result', [
            'created' => $import->created,
            'skipped' => $import->skipped,
            'errors'  => $import->errors,
        ]);
    }
}
