<?php

namespace App\Mail;

use App\Models\Candidate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Auto-reject — dikirim saat status kandidat diubah jadi "Ditolak" (CandidateController::updateStatus). */
class CandidateRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Candidate $candidate) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[ProPeople] Update Lamaran Anda — ' . ($this->candidate->jobRequisition?->title ?? 'PT Pro Energi Group'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.candidate-rejected',
            with: ['candidate' => $this->candidate],
        );
    }
}
