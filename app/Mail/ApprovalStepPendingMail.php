<?php

namespace App\Mail;

use App\Contracts\Approvable;
use App\Models\Approval\ApprovalRequestStep;
use App\Models\Approval\ApprovalWorkflow;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Notifikasi generik ke approver saat sebuah step Approval Engine jadi
 * actionable (dipanggil dari App\Services\ApprovalEngine — bukan per modul).
 */
class ApprovalStepPendingMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Approvable $approvable,
        public ApprovalRequestStep $step,
    ) {}

    public function envelope(): Envelope
    {
        $type = ApprovalWorkflow::$transactionTypes[$this->approvable->approvalTransactionType()]
            ?? $this->approvable->approvalTransactionType();

        return new Envelope(
            subject: "[ProPeople] {$type} Menunggu Persetujuan Anda",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.approval-step-pending');
    }
}
