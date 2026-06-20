<?php

namespace App\Mail;

use App\Models\Appraisal\Appraisal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppraisalFinalizedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appraisal $appraisal) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[SIPRO] Penilaian Disetujui Final — ' . ($this->appraisal->employee?->name ?? '-'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.appraisal-finalized');
    }
}
