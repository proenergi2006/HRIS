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
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category'      => 'required|string|in:' . implode(',', WhistleblowerReport::$categories),
            'description'   => 'required|string|min:20|max:5000',
            'is_anonymous'  => 'nullable',
            'reporter_name' => 'nullable|string|max:100',
            'reporter_email'=> 'nullable|email|max:100',
            'reporter_phone'=> 'nullable|string|max:30',
            'attachment'    => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
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
            'description'             => $validated['description'],
            'is_anonymous'            => $isAnonymous,
            'reporter_name'           => $isAnonymous ? null : ($validated['reporter_name'] ?? null),
            'reporter_email'          => $isAnonymous ? null : ($validated['reporter_email'] ?? null),
            'reporter_phone'          => $isAnonymous ? null : ($validated['reporter_phone'] ?? null),
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
