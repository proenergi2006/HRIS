<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Notifikasi in-app generik (bel notifikasi) — dipakai dari titik yang sama dengan
 * email pengingat yang sudah ada (contract:remind, documents:remind, approval
 * pending/overdue/result), TIDAK menggantikan email, cuma tambahan channel database
 * supaya muncul juga di bel notifikasi dalam aplikasi.
 */
class GenericNotification extends Notification
{
    public function __construct(
        private string $title,
        private string $message,
        private ?string $url = null,
        private string $icon = 'gd-bell',
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => $this->title,
            'message' => $this->message,
            'url'     => $this->url,
            'icon'    => $this->icon,
        ];
    }
}
