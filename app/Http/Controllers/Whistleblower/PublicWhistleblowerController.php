<?php

namespace App\Http\Controllers\Whistleblower;

use App\Http\Controllers\Controller;
use App\Models\WhistleblowerReport;
use App\Mail\WhistleblowerReportReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PublicWhistleblowerController extends Controller
{
    public function show()
    {
        return view('whistleblower.public.form', [
            'categories' => WhistleblowerReport::$categories,
            'branches'   => WhistleblowerReport::$branches,
            'relations'  => WhistleblowerReport::$reporterRelations,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category'               => 'required|string|in:' . implode(',', WhistleblowerReport::$categories),
            'branch_location'        => 'required|string|in:' . implode(',', WhistleblowerReport::$branches),
            'reporter_relation'      => 'required|string|in:' . implode(',', WhistleblowerReport::$reporterRelations),
            'description'            => 'required|string|min:20|max:5000',
            'incident_location_time' => 'required|string|max:500',
            'suspected_parties'      => 'required|string|max:500',
            'witnesses'              => 'nullable|string|max:500',
            'is_anonymous'           => 'nullable',
            'reporter_name'          => 'nullable|string|max:100',
            'reporter_email'         => 'nullable|email|max:100',
            'reporter_phone'         => 'nullable|string|max:30',
            'previously_reported'    => 'required|string|in:sudah,belum',
            'willing_to_be_contacted'=> 'required|string|in:ya,tidak',
            'disclosure'             => 'accepted',
            'attachment'             => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:8192',
        ], [
            'disclosure.accepted' => 'Anda harus menyetujui pernyataan disclosure untuk melanjutkan.',
        ]);

        $isAnonymous = $request->boolean('is_anonymous');

        $attachmentPath = null;
        $attachmentName = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('whistleblower', 'local');
            $attachmentName = $file->getClientOriginalName();
        }

        $report = WhistleblowerReport::create([
            'ticket_number'           => WhistleblowerReport::generateTicket(),
            'category'                => $validated['category'],
            'branch_location'         => $validated['branch_location'],
            'reporter_relation'       => $validated['reporter_relation'],
            'description'             => $validated['description'],
            'incident_location_time'  => $validated['incident_location_time'],
            'suspected_parties'       => $validated['suspected_parties'],
            'witnesses'               => $validated['witnesses'] ?? null,
            'is_anonymous'            => $isAnonymous,
            'reporter_name'           => $isAnonymous ? null : ($validated['reporter_name'] ?? null),
            'reporter_email'          => $isAnonymous ? null : ($validated['reporter_email'] ?? null),
            'reporter_phone'          => $isAnonymous ? null : ($validated['reporter_phone'] ?? null),
            'previously_reported'     => $validated['previously_reported'],
            'willing_to_be_contacted' => $validated['willing_to_be_contacted'] === 'ya',
            'attachment_path'         => $attachmentPath,
            'attachment_original_name'=> $attachmentName,
            'status'                  => 'new',
        ]);

        $notifyEmail = config('sipro.whistleblower_notify_email');
        if ($notifyEmail) {
            Mail::to($notifyEmail)->queue(new WhistleblowerReportReceived($report));
        }

        return redirect()->route('whistleblower.success', $report->ticket_number);
    }

    public function success(string $ticket)
    {
        return view('whistleblower.public.success', compact('ticket'));
    }
}
