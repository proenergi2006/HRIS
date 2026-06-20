<?php

namespace App\Mail;

use App\Models\Appraisal\Appraisal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppraisalRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appraisal $appraisal,
        public ?string $notes
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[SIPRO] Penilaian Dikembalikan — ' . ($this->appraisal->employee?->name ?? '-'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.appraisal-rejected');
    }
}
