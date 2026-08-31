<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DocumentExpiryReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Collection $expiring,
        public Collection $expired,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[ProPeople] Pengingat Dokumen Karyawan — ' . now()->format('d F Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.document-expiry-reminder',
            with: [
                'expiring' => $this->expiring,
                'expired'  => $this->expired,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
