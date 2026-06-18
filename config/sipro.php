<?php

return [
    /*
     * Email penerima notifikasi laporan Whistleblower baru.
     * Isi di .env: WHISTLEBLOWER_NOTIFY_EMAIL=hrd@proenergi.co.id
     * Kosongkan untuk nonaktifkan notifikasi email.
     */
    'whistleblower_notify_email' => env('WHISTLEBLOWER_NOTIFY_EMAIL'),
];
