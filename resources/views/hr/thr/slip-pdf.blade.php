@php
    $emp = $payment->employee;
    $rp  = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $ratio = (float) $payment->proration_ratio;
@endphp
@include('hr.payroll._slip-landscape', [
    'title'       => 'SLIP THR',
    'periodBadge' => str_contains($period->holiday_name, (string) $period->year) ? $period->holiday_name : $period->holiday_name . ' ' . $period->year,
    'subMeta'     => 'Dibayar ' . optional($period->payment_date)->translatedFormat('d F Y'),
    'company'     => $emp->company,
    'employee'    => $emp,
    'closedBy'    => $period->closedBy?->name,
    'facts'       => [
        'NIP'           => $emp->nip ?? '—',
        'Grade / Level' => strtoupper($emp->level?->name ?? '—'),
        'Cabang'        => $emp->branch ?? '—',
        'Departemen'    => $emp->department?->name ?? '—',
        'Mulai Kerja'   => $emp->start_date?->format('d M Y') ?? '—',
    ],
    'stats' => [
        ['n' => $payment->months_worked, 'l' => 'Bulan Masa Kerja'],
        ['n' => number_format($ratio * 100, $ratio * 100 == (int) ($ratio * 100) ? 0 : 1) . '%', 'l' => 'Rasio Proporsional'],
    ],
    'leftNote' => 'THR proporsional = Dasar Perhitungan × Rasio Masa Kerja (maks. 12 bulan).'
        . ($payment->notes ? '  ·  ' . $payment->notes : ''),
    'blocks' => [[
        'head'  => 'Perhitungan THR',
        'rows'  => [
            ['l' => 'Dasar Perhitungan (Gaji Pokok + Tunjangan Tetap)', 'v' => $rp($payment->base_salary)],
            ['l' => 'Masa Kerja Diperhitungkan', 'v' => $payment->months_worked . ' bulan'],
            ['l' => 'Rasio Proporsional', 'v' => number_format($ratio * 100, 1) . ' %'],
        ],
        'total' => ['l' => 'THR Bruto', 'v' => $rp($payment->thr_amount)],
    ]],
    'net'    => ['l' => 'THR Diterima', 'amount' => $payment->thr_amount],
    'footNote' => 'Slip THR ini digenerate otomatis oleh ProPeople dan sah tanpa tanda tangan basah · Informasi bersifat rahasia · Pertanyaan: hubungi HRD',
])
