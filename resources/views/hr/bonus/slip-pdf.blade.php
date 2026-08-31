@php
    $emp = $payment->employee;
    $rp  = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

    $rows = [
        ['l' => $period->type_label . ' Bruto', 'v' => $rp($payment->gross_amount)],
    ];
    if ($payment->tax_amount > 0) {
        $rows[] = ['l' => 'PPh 21 (metode TER)', 'v' => $rp($payment->tax_amount), 'neg' => true];
    }
@endphp
@include('hr.payroll._slip-landscape', [
    'title'       => 'SLIP ' . strtoupper($period->type_label),
    'periodBadge' => $period->name,
    'subMeta'     => 'Dibayar ' . optional($period->payment_date)->translatedFormat('d F Y'),
    'company'     => $emp->company,
    'employee'    => $emp,
    'closedBy'    => $period->closedBy?->name,
    'facts'       => [
        'NIP'           => $emp->nip ?? '—',
        'Grade / Level' => strtoupper($emp->level?->name ?? '—'),
        'Cabang'        => $emp->branch ?? '—',
        'Departemen'    => $emp->department?->name ?? '—',
        'Jenis'         => $period->type_label . ($period->is_taxable ? ' (kena pajak)' : ' (non-pajak)'),
    ],
    'leftNote' => $period->is_taxable
        ? 'PPh 21 dihitung dengan tarif efektif rata-rata (TER) sesuai PP 58/2023.'
        : ($payment->notes ?: null),
    'blocks' => [[
        'head'  => 'Perhitungan ' . $period->type_label,
        'rows'  => $rows,
        'total' => ['l' => $period->type_label . ' Diterima (Netto)', 'v' => $rp($payment->net_amount)],
    ]],
    'net'    => ['l' => $period->type_label . ' Diterima', 'amount' => $payment->net_amount],
    'footNote' => 'Slip ' . $period->type_label . ' ini digenerate otomatis oleh ProPeople dan sah tanpa tanda tangan basah · Informasi bersifat rahasia · Pertanyaan: hubungi HRD',
])
