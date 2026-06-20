<?php

namespace App\Mail;

use App\Models\Appraisal\Appraisal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppraisalNeedsApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appraisal $appraisal,
        public string $approverLabel
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[SIPRO] Penilaian Menunggu Persetujuan — ' . ($this->appraisal->employee?->name ?? '-'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.appraisal-needs-approval');
    }
}
