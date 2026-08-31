<?php

namespace App\Mail;

use App\Contracts\Approvable;
use App\Models\Approval\ApprovalWorkflow;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Notifikasi generik ke requester saat approval selesai (approved/rejected)
 * — dipanggil dari App\Services\ApprovalEngine, bukan per modul.
 */
class ApprovalResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Approvable $approvable,
        public bool $approved,
        public ?string $reason = null,
    ) {}

    public function envelope(): Envelope
    {
        $type = ApprovalWorkflow::$transactionTypes[$this->approvable->approvalTransactionType()]
            ?? $this->approvable->approvalTransactionType();
        $result = $this->approved ? 'Disetujui' : 'Ditolak';

        return new Envelope(
            subject: "[ProPeople] {$type} Anda {$result}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.approval-result');
    }
}
