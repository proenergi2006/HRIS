<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\LetterTemplate;
use Illuminate\Http\Request;

/** Template surat — light-CRUD (PRD Bab 3 modul #7 — "surat"). */
class LetterTemplateController extends Controller
{
    public function index()
    {
        $templates = LetterTemplate::with('company')->orderBy('category')->orderBy('title')->get();
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('appraisal.employee.letter-template.index', compact('templates', 'companies'));
    }

    public function store(Request $request)
    {
        LetterTemplate::create($this->validated($request));

        return back()->with('status', 'Template surat berhasil ditambahkan.');
    }

    public function update(Request $request, LetterTemplate $letterTemplate)
    {
        $letterTemplate->update($this->validated($request));

        return back()->with('status', 'Template surat berhasil diperbarui.');
    }

    public function destroy(LetterTemplate $letterTemplate)
    {
        $letterTemplate->delete();

        return back()->with('status', 'Template surat berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'company_id'   => 'nullable|exists:companies,id',
            'title'        => 'required|string|max:200',
            'category'     => 'required|in:' . implode(',', array_keys(LetterTemplate::$categoryLabels)),
            'body'         => 'required|string',
            'is_active'    => 'boolean',
            'self_service' => 'boolean',
        ]);
        $data['is_active']    = $request->boolean('is_active', true);
        $data['self_service'] = $request->boolean('self_service');

        return $data;
    }
}
