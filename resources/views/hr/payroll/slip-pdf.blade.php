@php
    $emp = $slip->employee;
    $rp  = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $day = fn ($n) => rtrim(rtrim(number_format((float) $n, 1), '0'), '.');

    $allowances = $slip->details->where('type', 'allowance')->values();
    $deductions = $slip->details->where('type', 'deduction')->values();

    $blocks = [[
        'head'  => 'Pendapatan',
        'rows'  => $allowances->isNotEmpty()
            ? $allowances->map(fn ($d) => ['l' => $d->component_name, 'v' => $rp($d->amount)])->all()
            : [['l' => 'Tidak ada komponen pendapatan', 'v' => $rp(0)]],
        'total' => ['l' => 'Total Pendapatan (Bruto)', 'v' => $rp($slip->total_allowances)],
    ], [
        'head'  => 'Potongan',
        'rows'  => $deductions->isNotEmpty()
            ? $deductions->map(fn ($d) => ['l' => $d->component_name, 'v' => $rp($d->amount)])->all()
            : [['l' => 'Tidak ada potongan', 'v' => $rp(0)]],
        'total' => ['l' => 'Total Potongan', 'v' => $rp($slip->total_deductions)],
    ]];
@endphp
@include('hr.payroll._slip-landscape', [
    'title'       => 'SLIP GAJI',
    'periodBadge' => 'Periode ' . $period->period_label,
    'company'     => $period->company ?? $emp->company,
    'employee'    => $emp,
    'closedBy'    => $period->closedBy?->name,
    'facts'       => [
        'NIP'           => $emp->nip ?? '—',
        'Grade / Level' => strtoupper($emp->level?->name ?? '—'),
        'Cabang'        => $emp->branch ?? '—',
        'Kelompok'      => strtoupper(($period->company ?? $emp->company)?->short_name ?? ($period->company ?? $emp->company)?->name ?? '—'),
        'Mulai Kerja'   => $emp->start_date?->format('d M Y') ?? '—',
        'Status'        => $emp->employment_status_label ?? '—',
    ],
    'stats' => [
        ['n' => (int) $slip->working_days,       'l' => 'Hari Kerja'],
        ['n' => $day($slip->attendance_days),    'l' => 'Hadir'],
        ['n' => $day($slip->leave_days),         'l' => 'Cuti / Izin'],
        ['n' => $day($slip->alpha_days),         'l' => 'Alpha'],
    ],
    'leftNote' => 'Periode kerja: ' . $period->cutoffStart()->translatedFormat('d M') . ' – ' . $period->cutoffEnd()->translatedFormat('d M Y')
        . ($slip->late_minutes > 0 ? '  ·  Terlambat ' . $slip->late_minutes . ' menit' : '')
        . ($slip->notes ? '  ·  ' . $slip->notes : ''),
    'blocks' => $blocks,
    'net'    => ['l' => 'Gaji Bersih — Take Home Pay', 'amount' => $slip->net_salary],
    'footNote' => 'Slip gaji ini digenerate otomatis oleh ProPeople dan sah tanpa tanda tangan basah · Informasi bersifat rahasia · Pertanyaan: hubungi HRD',
])
