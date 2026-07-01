<?php

namespace App\Mail\Perdin;

use App\Models\Perdin\PerdinRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PerdinSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PerdinRequest $perdin, public User $approver) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[SIPRO] Permohonan Perjalanan Dinas Menunggu Persetujuan — ' . $this->perdin->no_advance,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.perdin-submitted');
    }
}
