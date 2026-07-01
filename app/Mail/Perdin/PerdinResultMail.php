<?php

namespace App\Mail\Perdin;

use App\Models\Perdin\PerdinRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PerdinResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PerdinRequest $perdin, public bool $approved) {}

    public function envelope(): Envelope
    {
        $state = $this->approved ? 'Disetujui' : 'Ditolak';

        return new Envelope(
            subject: '[SIPRO] Permohonan Perjalanan Dinas ' . $state . ' — ' . $this->perdin->no_advance,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.perdin-result');
    }
}
