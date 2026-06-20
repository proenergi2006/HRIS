<?php

namespace App\Mail;

use App\Models\Reimbursement\ReimbursementRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReimbursementRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ReimbursementRequest $reimbursement) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[SIPRO] Pengajuan Reimbursement Ditolak — ' . $this->reimbursement->request_number,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.reimbursement-rejected');
    }
}
