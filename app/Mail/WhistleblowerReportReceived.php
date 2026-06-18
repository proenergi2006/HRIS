<?php

namespace App\Mail;

use App\Models\WhistleblowerReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WhistleblowerReportReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public WhistleblowerReport $report) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[SIPRO] Laporan Pengaduan Baru — ' . $this->report->ticket_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.whistleblower.received',
            with: ['report' => $this->report],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
