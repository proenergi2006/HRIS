<?php

return [
    /*
     * Email penerima notifikasi laporan Whistleblower baru.
     * Isi di .env: WHISTLEBLOWER_NOTIFY_EMAIL=hrd@proenergi.co.id
     * Kosongkan untuk nonaktifkan notifikasi email.
     */
    'whistleblower_notify_email' => env('WHISTLEBLOWER_NOTIFY_EMAIL'),

    /*
     * Identitas perusahaan — dipakai di kop & footer PDF serta label rute
     * perjalanan dinas. Override lewat .env bila perlu.
     */
    'company' => [
        'name'      => env('COMPANY_NAME', 'PT. PRO ENERGI'),
        'tagline'   => env('COMPANY_TAGLINE', 'Integrated Energy Solutions'),
        'address'   => env('COMPANY_ADDRESS', 'Jl. Energi Raya No. 1, Jakarta'),
        'website'   => env('COMPANY_WEBSITE', 'www.proenergi.co.id'),
        // Kota asal default perjalanan dinas (titik berangkat & kembali).
        'home_base' => env('COMPANY_HOME_BASE', 'Jakarta'),
    ],
];
