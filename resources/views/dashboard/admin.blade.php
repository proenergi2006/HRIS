@extends('layouts.grain')
@section('title', 'Dashboard')

@section('content')
@include('components.notification')

<div class="mb-2 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
    <div class="h3 mb-0">Dashboard</div>
    <small class="text-muted">Selamat datang, <strong>{{ auth()->user()->name }}</strong></small>
</div>

{{-- Filter perusahaan (memengaruhi data SDM, penilaian & kontrak; Reimbursement/Perdin/Pengaduan tetap lintas PT) --}}
<form method="GET" class="mb-3 d-flex align-items-center flex-wrap" style="gap:.5rem">
    <span class="small text-muted">Perusahaan:</span>
    <select name="company_id" class="form-control form-control-sm" style="width:auto;min-width:220px" onchange="this.form.submit()">
        <option value="">Semua — Konsolidasi Grup</option>
        @foreach($companies as $c)
            <option value="{{ $c->id }}" @selected($companyId === $c->id)>{{ $c->name }}</option>
        @endforeach
    </select>
    <span class="badge badge-{{ $companyId ? 'primary' : 'secondary' }}">
        {{ $companyId ? ($companies->firstWhere('id', $companyId)->short_name ?? $companies->firstWhere('id', $companyId)->name) : 'Konsolidasi Grup (' . $companies->count() . ' PT)' }}
    </span>
</form>

{{-- ── Tab Navigation ── --}}
<ul class="nav nav-tabs mb-4" id="dashTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="tab-overview" data-toggle="tab" href="#pane-overview" role="tab">
            <i class="gd-home mr-1"></i> Overview
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-sdm" data-toggle="tab" href="#pane-sdm" role="tab">
            <i class="gd-user mr-1"></i> SDM
            @php $sdmBadge = $ops['leave']['pending'] + $ops['approvals_pending']; @endphp
            @if($sdmBadge > 0)<span class="badge badge-primary ml-1">{{ $sdmBadge }}</span>@endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-reimb" data-toggle="tab" href="#pane-reimb" role="tab">
            <i class="gd-wallet mr-1"></i> Reimbursement
            @if($reimb['pending'] > 0)
                <span class="badge badge-danger ml-1">{{ $reimb['pending'] }}</span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-perdin" data-toggle="tab" href="#pane-perdin" role="tab">
            <i class="gd-briefcase mr-1"></i> Perjalanan Dinas
            @if($perdin['pending'] > 0)
                <span class="badge badge-warning ml-1">{{ $perdin['pending'] }}</span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-wb" data-toggle="tab" href="#pane-wb" role="tab">
            <i class="gd-alert mr-1"></i> Pengaduan
            @if($wb['new'] > 0)
                <span class="badge badge-danger ml-1">{{ $wb['new'] }}</span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-karyawan" data-toggle="tab" href="#pane-karyawan" role="tab">
            <i class="gd-user mr-1"></i> Karyawan
            @if($contractExpired->isNotEmpty() || $contractExpiring->isNotEmpty())
                <span class="badge badge-{{ $contractExpired->isNotEmpty() ? 'danger' : 'warning' }} ml-1">
                    {{ $contractExpired->count() + $contractExpiring->count() }}
                </span>
            @endif
        </a>
    </li>
</ul>

<div class="tab-content" id="dashTabsContent">

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- TAB 1: OVERVIEW                                                     --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<div class="tab-pane fade show active" id="pane-overview" role="tabpanel">

    {{-- Ringkasan SDM cepat --}}
    <div class="row">
        <div class="col-6 col-md-3 mb-3">
            <a href="#pane-sdm" data-toggle="tab" class="text-decoration-none">
            <div class="card flex-row align-items-center p-3 h-100" style="border-left:4px solid #0f2a4a;">
                <div class="icon icon-lg bg-soft-primary rounded-circle mr-3"><i class="gd-user icon-text d-inline-block text-primary"></i></div>
                <div><h4 class="lh-1 mb-0">{{ $hc['total'] }}</h4><h6 class="mb-0 text-muted" style="font-size:.78rem;">Karyawan Aktif</h6></div>
            </div></a>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <a href="#pane-sdm" data-toggle="tab" class="text-decoration-none">
            <div class="card flex-row align-items-center p-3 h-100" style="border-left:4px solid #0ea5e9;">
                <div class="icon icon-lg bg-soft-info rounded-circle mr-3"><i class="gd-calendar icon-text d-inline-block text-info"></i></div>
                <div><h4 class="lh-1 mb-0">{{ $ops['leave']['on_leave_today']->count() }}</h4><h6 class="mb-0 text-muted" style="font-size:.78rem;">Cuti Hari Ini</h6></div>
            </div></a>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <a href="#pane-sdm" data-toggle="tab" class="text-decoration-none">
            <div class="card flex-row align-items-center p-3 h-100" style="border-left:4px solid #22c55e;">
                <div class="icon icon-lg bg-soft-success rounded-circle mr-3"><i class="gd-user icon-text d-inline-block text-success"></i></div>
                <div><h4 class="lh-1 mb-0">{{ $hc['new_this_month'] }}</h4><h6 class="mb-0 text-muted" style="font-size:.78rem;">Karyawan Baru Bln Ini</h6></div>
            </div></a>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <a href="{{ route('approval.inbox.index') }}" class="text-decoration-none">
            <div class="card flex-row align-items-center p-3 h-100" style="border-left:4px solid #f59e0b;">
                <div class="icon icon-lg bg-soft-warning rounded-circle mr-3"><i class="gd-time icon-text d-inline-block text-warning"></i></div>
                <div><h4 class="lh-1 mb-0">{{ $ops['approvals_pending'] }}</h4><h6 class="mb-0 text-muted" style="font-size:.78rem;">Approval Menunggu Anda</h6></div>
            </div></a>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="row">
        {{-- Reimbursement --}}
        <div class="col-6 col-xl-3 mb-3">
            <a href="#pane-reimb" data-toggle="tab" class="text-decoration-none">
            <div class="card flex-row align-items-center p-3 h-100" style="border-left:4px solid #f59e0b;">
                <div class="icon icon-lg bg-soft-warning rounded-circle mr-3">
                    <i class="gd-wallet icon-text d-inline-block text-warning"></i>
                </div>
                <div>
                    <h4 class="lh-1 mb-0">{{ $reimb['pending'] }}</h4>
                    <h6 class="mb-0 text-muted" style="font-size:.78rem;">Reimb. Menunggu</h6>
                </div>
            </div>
            </a>
        </div>
        {{-- Perdin --}}
        <div class="col-6 col-xl-3 mb-3">
            <a href="#pane-perdin" data-toggle="tab" class="text-decoration-none">
            <div class="card flex-row align-items-center p-3 h-100" style="border-left:4px solid #3b82f6;">
                <div class="icon icon-lg bg-soft-primary rounded-circle mr-3">
                    <i class="gd-briefcase icon-text d-inline-block text-primary"></i>
                </div>
                <div>
                    <h4 class="lh-1 mb-0">{{ $perdin['pending'] }}</h4>
                    <h6 class="mb-0 text-muted" style="font-size:.78rem;">Perdin Menunggu</h6>
                </div>
            </div>
            </a>
        </div>
        {{-- Pengaduan --}}
        <div class="col-6 col-xl-3 mb-3">
            <a href="#pane-wb" data-toggle="tab" class="text-decoration-none">
            <div class="card flex-row align-items-center p-3 h-100" style="border-left:4px solid #ef4444;">
                <div class="icon icon-lg bg-soft-danger rounded-circle mr-3">
                    <i class="gd-alert icon-text d-inline-block text-danger"></i>
                </div>
                <div>
                    <h4 class="lh-1 mb-0">{{ $wb['new'] }}</h4>
                    <h6 class="mb-0 text-muted" style="font-size:.78rem;">Pengaduan Baru</h6>
                </div>
            </div>
            </a>
        </div>
        {{-- Kontrak Berakhir --}}
        <div class="col-6 col-xl-3 mb-3">
            <a href="#pane-karyawan" data-toggle="tab" class="text-decoration-none">
            <div class="card flex-row align-items-center p-3 h-100" style="border-left:4px solid {{ $contractExpired->isNotEmpty() ? '#ef4444' : '#f59e0b' }};">
                <div class="icon icon-lg bg-soft-{{ $contractExpired->isNotEmpty() ? 'danger' : 'warning' }} rounded-circle mr-3">
                    <i class="gd-calendar icon-text d-inline-block text-{{ $contractExpired->isNotEmpty() ? 'danger' : 'warning' }}"></i>
                </div>
                <div>
                    <h4 class="lh-1 mb-0">{{ $contractExpired->count() + $contractExpiring->count() }}</h4>
                    <h6 class="mb-0 text-muted" style="font-size:.78rem;">Kontrak Perlu Perhatian</h6>
                </div>
            </div>
            </a>
        </div>
    </div>

    {{-- Appraisal Stats --}}
    <div class="row mt-1">
        <div class="col-6 col-xl-3 mb-3">
            <div class="card flex-row align-items-center p-3">
                <div class="icon icon-lg bg-soft-primary rounded-circle mr-3">
                    <i class="gd-check icon-text d-inline-block text-primary"></i>
                </div>
                <div>
                    <h4 class="lh-1 mb-1">{{ $stats['total'] }}</h4>
                    <h6 class="mb-0 text-muted">Total Penilaian</h6>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3 mb-3">
            <div class="card flex-row align-items-center p-3">
                <div class="icon icon-lg bg-soft-secondary rounded-circle mr-3">
                    <i class="gd-pencil icon-text d-inline-block text-secondary"></i>
                </div>
                <div>
                    <h4 class="lh-1 mb-1">{{ $stats['draft'] }}</h4>
                    <h6 class="mb-0 text-muted">Draft</h6>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3 mb-3">
            <div class="card flex-row align-items-center p-3">
                <div class="icon icon-lg bg-soft-warning rounded-circle mr-3">
                    <i class="gd-time icon-text d-inline-block text-warning"></i>
                </div>
                <div>
                    <h4 class="lh-1 mb-1">{{ $stats['pending'] }}</h4>
                    <h6 class="mb-0 text-muted">Menunggu Approval</h6>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3 mb-3">
            <div class="card flex-row align-items-center p-3">
                <div class="icon icon-lg bg-soft-success rounded-circle mr-3">
                    <i class="gd-check icon-text d-inline-block text-success"></i>
                </div>
                <div>
                    <h4 class="lh-1 mb-1">{{ $stats['final'] }}</h4>
                    <h6 class="mb-0 text-muted">Final / Selesai</h6>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart + Aksi Cepat --}}
    <div class="row">
        <div class="col-12 col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="font-weight-bold mb-3">Periode Aktif</h6>
                    @forelse($openPeriods as $period)
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <div>
                                <div class="font-weight-bold" style="font-size:0.85rem;">{{ $period->name }}</div>
                                <small class="text-muted">{{ $period->appraisals_count }} penilaian</small>
                            </div>
                            <span class="badge badge-success">Buka</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Tidak ada periode yang sedang buka.</p>
                    @endforelse
                    <a href="{{ route('appraisal.periods.index') }}" class="btn btn-outline-primary btn-sm mt-2">Kelola Periode</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <h6 class="font-weight-bold mb-3">Distribusi Grade (Final)</h6>
                    @if($gradeDistrib->count())
                        <div class="flex-grow-1 d-flex align-items-center justify-content-center">
                            <canvas id="chart-grade"></canvas>
                        </div>
                    @else
                        <p class="text-muted mb-0">Belum ada penilaian final.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="font-weight-bold mb-3">Aksi Cepat</h6>
                    <a href="{{ route('appraisal.appraisals.create') }}" class="btn btn-primary btn-block mb-2">
                        <i class="gd-plus mr-1"></i> Buat Penilaian
                    </a>
                    <a href="{{ route('appraisal.appraisals.index') }}" class="btn btn-outline-primary btn-block mb-2">
                        <i class="gd-check mr-1"></i> Data Penilaian
                    </a>
                    <a href="{{ route('appraisal.report.index') }}" class="btn btn-outline-secondary btn-block mb-2">
                        <i class="gd-search mr-1"></i> Laporan
                    </a>
                    <a href="{{ route('whistleblower.admin.index') }}" class="btn btn-outline-warning btn-block">
                        <i class="gd-alert mr-1"></i> Pengaduan
                        @if($wb['new']) <span class="badge badge-danger ml-1">{{ $wb['new'] }}</span> @endif
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>{{-- /pane-overview --}}


{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- TAB 2: SDM                                                          --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<div class="tab-pane fade" id="pane-sdm" role="tabpanel">
@php
    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $att = $ops['attendance_month'];
    $rec = $ops['recruitment'];
    $trn = $ops['training'];
@endphp

{{-- ── A. Ringkasan ── --}}
<h6 class="text-uppercase text-muted font-weight-bold mb-2" style="letter-spacing:.5px;font-size:.72rem">Ringkasan SDM</h6>
<div class="row">
    @php
        $kpi = [
            ['Karyawan Aktif',   $hc['total'],  $companyId ? 'di PT ini' : $hc['by_company']->count().' PT · grup '.$hc['group_total'], '#0f2a4a', 'gd-user'],
            ['Karyawan Baru',    $hc['new_this_month'], 'bulan ini · '.$hc['new_this_year'].' tahun ini', '#22c55e', 'gd-user-plus'],
            ['Keluar (YTD)',     $analytics['turnover']['total'], $analytics['turnover']['voluntary'].' sukarela / '.$analytics['turnover']['involuntary'].' non', '#ef4444', 'gd-log-out'],
            ['Cuti Hari Ini',    $ops['leave']['on_leave_today']->count(), $ops['leave']['pending'].' menunggu approval', '#0ea5e9', 'gd-calendar'],
            ['Turnover YTD',     ($analytics['turnover']['rate'] ?? 0).'%', 'vs headcount '.$analytics['turnover']['avg_headcount'], '#8b5cf6', 'gd-repeat'],
            ['Approval Menunggu', $ops['approvals_pending'], 'butuh tindakan Anda', '#f59e0b', 'gd-time'],
        ];
    @endphp
    @foreach($kpi as [$label, $val, $sub, $color, $icon])
    <div class="col-6 col-lg-4 col-xl-2 mb-3">
        <div class="card p-3 h-100" style="border-top:3px solid {{ $color }}">
            <div class="d-flex align-items-center mb-1">
                <i class="{{ $icon }} mr-2" style="color:{{ $color }}"></i>
                <h4 class="mb-0">{{ $val }}</h4>
            </div>
            <div class="text-muted" style="font-size:.74rem;line-height:1.25">{{ $label }}<br><span style="opacity:.7">{{ $sub }}</span></div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── B. Analitik SDM ── --}}
<h6 class="text-uppercase text-muted font-weight-bold mb-2 mt-2" style="letter-spacing:.5px;font-size:.72rem">Analitik SDM</h6>
<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="card h-100"><div class="card-body">
            <h6 class="font-weight-bold mb-3">Headcount per Departemen</h6>
            @if($hc['by_department']->count())<div style="height:{{ max(160, $hc['by_department']->count() * 32) }}px"><canvas id="c-dept"></canvas></div>
            @else<p class="text-muted mb-0">Belum ada data.</p>@endif
        </div></div>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="card h-100"><div class="card-body">
            <h6 class="font-weight-bold mb-3">Headcount per Cabang</h6>
            @if($hcx['by_branch']->count())<div style="height:{{ max(160, $hcx['by_branch']->count() * 32) }}px"><canvas id="c-branch"></canvas></div>
            @else<p class="text-muted mb-0">Belum ada data cabang.</p>@endif
        </div></div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card h-100"><div class="card-body text-center">
            <h6 class="font-weight-bold mb-3">Komposisi Status</h6>
            @if($hc['by_status']->sum())<div style="height:210px"><canvas id="c-status"></canvas></div>
            @else<p class="text-muted mb-0">Belum ada data.</p>@endif
        </div></div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card h-100"><div class="card-body text-center">
            <h6 class="font-weight-bold mb-3">Komposisi Gender</h6>
            @if($hc['by_gender']->sum())<div style="height:210px"><canvas id="c-gender"></canvas></div>
            @else<p class="text-muted mb-0">Belum ada data.</p>@endif
        </div></div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card h-100"><div class="card-body">
            <h6 class="font-weight-bold mb-3">Headcount per Level</h6>
            @if($hc['by_level']->count())<div style="height:210px"><canvas id="c-level"></canvas></div>
            @else<p class="text-muted mb-0">Belum ada data.</p>@endif
        </div></div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card h-100"><div class="card-body">
            <h6 class="font-weight-bold mb-3">Distribusi Masa Kerja</h6>
            @if($hcx['tenure_buckets']->sum())<div style="height:210px"><canvas id="c-tenure"></canvas></div>
            @else<p class="text-muted mb-0">Belum ada data.</p>@endif
        </div></div>
    </div>
    <div class="col-lg-7 mb-3">
        <div class="card h-100"><div class="card-body">
            <h6 class="font-weight-bold mb-3">Tren Karyawan Masuk vs Keluar (12 bulan)</h6>
            <div style="height:240px"><canvas id="c-movement"></canvas></div>
        </div></div>
    </div>
    <div class="col-lg-5 mb-3">
        <div class="card h-100"><div class="card-body">
            <h6 class="font-weight-bold mb-3">Absenteeism per Bulan (%)</h6>
            <div style="height:240px"><canvas id="c-absen"></canvas></div>
        </div></div>
    </div>
</div>

{{-- ── C. Operasional ── --}}
<h6 class="text-uppercase text-muted font-weight-bold mb-2 mt-2" style="letter-spacing:.5px;font-size:.72rem">Operasional</h6>
<div class="row">
    {{-- Absensi --}}
    <div class="col-md-6 col-xl-4 mb-3">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <h6 class="font-weight-bold mb-2">Absensi Bulan Ini</h6>
                <a href="{{ route('hr.attendance.index') }}" class="btn btn-xs btn-outline-secondary">Lihat</a>
            </div>
            <div class="d-flex align-items-baseline mb-2">
                <span class="h3 mb-0 mr-2">{{ $att['rate'] ?? '—' }}%</span>
                <small class="text-muted">tingkat kehadiran ({{ $att['total'] }} record)</small>
            </div>
            <div class="row text-center">
                <div class="col"><div class="font-weight-bold text-warning">{{ $att['late'] }}</div><small class="text-muted">Telat</small></div>
                <div class="col"><div class="font-weight-bold text-danger">{{ $att['alpha'] }}</div><small class="text-muted">Alpha</small></div>
                <div class="col"><div class="font-weight-bold text-primary">{{ $att['overtime_hours'] }}j</div><small class="text-muted">Lembur</small></div>
                <div class="col"><div class="font-weight-bold">{{ intdiv($att['late_minutes'], 60) }}j{{ $att['late_minutes'] % 60 }}m</div><small class="text-muted">Total telat</small></div>
            </div>
        </div></div>
    </div>

    {{-- Cuti --}}
    <div class="col-md-6 col-xl-4 mb-3">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <h6 class="font-weight-bold mb-2">Cuti — {{ $ops['leave']['pending'] }} menunggu</h6>
                <a href="{{ route('hr.leave.index') }}" class="btn btn-xs btn-outline-secondary">Lihat</a>
            </div>
            @forelse($ops['leave']['recent_pending'] as $lr)
            <div class="d-flex justify-content-between small border-bottom py-1">
                <span>{{ $lr->employee?->name ?? '-' }} <span class="text-muted">· {{ $lr->leaveType?->name ?? '-' }}</span></span>
                <span class="text-muted">{{ optional($lr->start_date)->format('d/m') }}–{{ optional($lr->end_date)->format('d/m') }}</span>
            </div>
            @empty
            <p class="text-muted small mb-2">Tidak ada pengajuan cuti menunggu.</p>
            @endforelse
            @if($ops['leave']['on_leave_today']->isNotEmpty())
            <div class="mt-2"><small class="text-muted font-weight-bold">Cuti hari ini:</small>
                <div class="small">{{ $ops['leave']['on_leave_today']->map(fn($l) => $l->employee?->name)->filter()->implode(', ') }}</div>
            </div>
            @endif
        </div></div>
    </div>

    {{-- Rekrutmen --}}
    <div class="col-md-6 col-xl-4 mb-3">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <h6 class="font-weight-bold mb-2">Rekrutmen</h6>
                <a href="{{ route('recruitment.requisitions.index') }}" class="btn btn-xs btn-outline-secondary">Lihat</a>
            </div>
            <div class="d-flex mb-2" style="gap:1rem">
                <div><span class="h5 mb-0">{{ $rec['mpp_open'] }}</span> <small class="text-muted d-block">MPP disetujui</small></div>
                <div><span class="h5 mb-0">{{ $rec['jobreq_open'] }}</span> <small class="text-muted d-block">Lowongan aktif</small></div>
                <div><span class="h5 mb-0">{{ $rec['candidates_active'] }}</span> <small class="text-muted d-block">Kandidat proses</small></div>
            </div>
            <small class="text-muted font-weight-bold">Funnel kandidat</small>
            @foreach($rec['candidate_funnel'] as $stage => $n)
            <div class="d-flex align-items-center small py-1" style="gap:.5rem">
                <span style="width:90px">{{ $stage }}</span>
                <div class="flex-grow-1 bg-light rounded" style="height:14px">
                    <div class="bg-primary rounded" style="height:14px;width:{{ $rec['candidate_funnel']->max() ? ($n / max(1,$rec['candidate_funnel']->max()) * 100) : 0 }}%"></div>
                </div>
                <span class="font-weight-bold" style="width:24px;text-align:right">{{ $n }}</span>
            </div>
            @endforeach
        </div></div>
    </div>

    {{-- Training --}}
    <div class="col-md-6 col-xl-4 mb-3">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <h6 class="font-weight-bold mb-2">Training</h6>
                <a href="{{ route('training.participants.index') }}" class="btn btn-xs btn-outline-secondary">Lihat</a>
            </div>
            <div class="row text-center">
                <div class="col"><div class="h4 mb-0">{{ $trn['programs_active'] }}</div><small class="text-muted">Program aktif</small></div>
                <div class="col"><div class="h4 mb-0">{{ $trn['ongoing'] }}</div><small class="text-muted">Berlangsung</small></div>
                <div class="col"><div class="h4 mb-0">{{ $trn['planned'] }}</div><small class="text-muted">Direncanakan</small></div>
                <div class="col"><div class="h4 mb-0">{{ $trn['completion_ytd'] ?? '—' }}%</div><small class="text-muted">Selesai YTD</small></div>
            </div>
        </div></div>
    </div>

    {{-- Kasbon --}}
    <div class="col-md-6 col-xl-4 mb-3">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <h6 class="font-weight-bold mb-2">Kasbon / Pinjaman</h6>
                <a href="{{ route('hr.loans.index') }}" class="btn btn-xs btn-outline-secondary">Lihat</a>
            </div>
            <div class="h3 mb-0">{{ $ops['loans']['active'] }} <small class="text-muted" style="font-size:.85rem">pinjaman aktif</small></div>
            <div class="text-muted">Sisa outstanding: <strong>{{ $rp($ops['loans']['outstanding_total']) }}</strong></div>
        </div></div>
    </div>

    {{-- Offboarding --}}
    <div class="col-md-6 col-xl-4 mb-3">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <h6 class="font-weight-bold mb-2">Offboarding / Clearance</h6>
                <a href="{{ route('hr.offboarding.index') }}" class="btn btn-xs btn-outline-secondary">Lihat</a>
            </div>
            <div class="d-flex" style="gap:1.5rem">
                <div><span class="h3 mb-0">{{ $ops['offboarding']['in_progress'] }}</span><small class="text-muted d-block">Proses berjalan</small></div>
                <div><span class="h3 mb-0">{{ $ops['offboarding']['tasks_pending'] }}</span><small class="text-muted d-block">Task belum selesai</small></div>
            </div>
        </div></div>
    </div>

    {{-- Payroll --}}
    <div class="col-md-6 col-xl-4 mb-3">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <h6 class="font-weight-bold mb-2">Payroll</h6>
                <a href="{{ route('hr.payroll.index') }}" class="btn btn-xs btn-outline-secondary">Lihat</a>
            </div>
            @if($ops['payroll_last'])
            <div class="mb-1"><strong>{{ $ops['payroll_last']['label'] }}</strong>
                <span class="badge badge-{{ $ops['payroll_last']['status'] === 'closed' ? 'success' : 'warning' }} ml-1">{{ ucfirst($ops['payroll_last']['status']) }}</span>
                <span class="text-muted small">· {{ $ops['payroll_last']['company'] }}</span>
            </div>
            <div class="text-muted small">{{ $ops['payroll_last']['slip_count'] }} slip · Total THP {{ $rp($ops['payroll_last']['total_net']) }}</div>
            @else
            <p class="text-muted mb-0">Belum ada periode payroll.</p>
            @endif
        </div></div>
    </div>
</div>

{{-- ── D. Kalender HR ── --}}
<h6 class="text-uppercase text-muted font-weight-bold mb-2 mt-2" style="letter-spacing:.5px;font-size:.72rem">Kalender HR — {{ now()->translatedFormat('F Y') }}</h6>
<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header font-weight-bold"><i class="gd-gift mr-1"></i> Ulang Tahun Bulan Ini ({{ $cal['birthdays']->count() }})</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                    @forelse($cal['birthdays'] as $b)
                    <tr><td class="pl-3">{{ $b['name'] }}</td><td class="text-muted">{{ $b['department'] }}</td>
                        <td class="text-right pr-3 text-nowrap">{{ optional($b['date'])->translatedFormat('d M') }}</td></tr>
                    @empty
                    <tr><td class="text-muted text-center py-3">Tidak ada.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header font-weight-bold"><i class="gd-award mr-1"></i> Hari Jadi Kerja Bulan Ini ({{ $cal['anniversaries']->count() }})</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                    @forelse($cal['anniversaries'] as $a)
                    <tr><td class="pl-3">{{ $a['name'] }}</td><td class="text-muted">{{ $a['department'] }}</td>
                        <td class="text-right pr-3 text-nowrap"><span class="badge badge-primary">{{ $a['years'] }} th</span> {{ optional($a['date'])->translatedFormat('d M') }}</td></tr>
                    @empty
                    <tr><td class="text-muted text-center py-3">Tidak ada.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</div>{{-- /pane-sdm --}}


{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- TAB 3: REIMBURSEMENT                                                --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<div class="tab-pane fade" id="pane-reimb" role="tabpanel">

    <div class="row mb-3">
        <div class="col-6 col-xl-4 mb-3">
            <div class="card flex-row align-items-center p-3" style="border-left:4px solid #f59e0b;">
                <div class="icon icon-lg bg-soft-warning rounded-circle mr-3">
                    <i class="gd-time icon-text d-inline-block text-warning"></i>
                </div>
                <div>
                    <h4 class="lh-1 mb-1">{{ $reimb['pending'] }}</h4>
                    <h6 class="mb-0 text-muted">Menunggu Persetujuan</h6>
                </div>
                @if($reimb['pending'] > 0)
                <a href="{{ route('reimbursement.admin.index', ['status' => 'submitted']) }}" class="stretched-link"></a>
                @endif
            </div>
        </div>
        <div class="col-6 col-xl-4 mb-3">
            <div class="card flex-row align-items-center p-3" style="border-left:4px solid #22c55e;">
                <div class="icon icon-lg bg-soft-success rounded-circle mr-3">
                    <i class="gd-check icon-text d-inline-block text-success"></i>
                </div>
                <div>
                    <h4 class="lh-1 mb-1">{{ $reimb['approved'] }}</h4>
                    <h6 class="mb-0 text-muted">Disetujui {{ now()->year }}</h6>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4 mb-3">
            <div class="card flex-row align-items-center p-3" style="border-left:4px solid #3b82f6;">
                <div class="icon icon-lg bg-soft-primary rounded-circle mr-3">
                    <i class="gd-receipt icon-text d-inline-block text-primary"></i>
                </div>
                <div>
                    <h4 class="lh-1 mb-0" style="font-size:1.1rem">Rp {{ number_format($reimb['total_claim'], 0, ',', '.') }}</h4>
                    <h6 class="mb-0 text-muted">Total Klaim Disetujui {{ now()->year }}</h6>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="font-weight-bold">Aktivitas Reimbursement Terbaru</span>
            <a href="{{ route('reimbursement.admin.index') }}" class="btn btn-xs btn-outline-secondary">Lihat Semua</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="pl-3">No. Pengajuan</th>
                        <th>Nama</th>
                        <th class="text-right">Total Klaim</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Tanggal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($reimb['recent'] as $r)
                <tr>
                    <td class="pl-3 font-weight-bold">{{ $r->request_number }}</td>
                    <td>{{ $r->user?->name ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($r->total_claim, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ \App\Models\Reimbursement\ReimbursementRequest::$statusBadges[$r->status] }}">
                            {{ \App\Models\Reimbursement\ReimbursementRequest::$statusLabels[$r->status] }}
                        </span>
                    </td>
                    <td class="text-center">{{ $r->updated_at->format('d/m/Y') }}</td>
                    <td><a href="{{ route('reimbursement.admin.show', $r) }}" class="btn btn-xs btn-outline-info"><i class="gd-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data reimbursement.</td></tr>
                @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>{{-- /pane-reimb --}}


{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- TAB 3: PERJALANAN DINAS                                             --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<div class="tab-pane fade" id="pane-perdin" role="tabpanel">

    <div class="row mb-3">
        <div class="col-6 col-xl-4 mb-3">
            <div class="card flex-row align-items-center p-3" style="border-left:4px solid #f59e0b;">
                <div class="icon icon-lg bg-soft-warning rounded-circle mr-3">
                    <i class="gd-briefcase icon-text d-inline-block text-warning"></i>
                </div>
                <div>
                    <h4 class="lh-1 mb-1">{{ $perdin['pending'] }}</h4>
                    <h6 class="mb-0 text-muted">Menunggu Persetujuan</h6>
                </div>
                @if($perdin['pending'] > 0)
                <a href="{{ route('perdin.admin.requests') }}" class="stretched-link"></a>
                @endif
            </div>
        </div>
        <div class="col-6 col-xl-4 mb-3">
            <div class="card flex-row align-items-center p-3" style="border-left:4px solid #22c55e;">
                <div class="icon icon-lg bg-soft-success rounded-circle mr-3">
                    <i class="gd-check icon-text d-inline-block text-success"></i>
                </div>
                <div>
                    <h4 class="lh-1 mb-1">{{ $perdin['approved'] }}</h4>
                    <h6 class="mb-0 text-muted">Disetujui {{ now()->year }}</h6>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4 mb-3">
            <div class="card flex-row align-items-center p-3" style="border-left:4px solid #3b82f6;">
                <div class="icon icon-lg bg-soft-primary rounded-circle mr-3">
                    <i class="gd-money icon-text d-inline-block text-primary"></i>
                </div>
                <div>
                    <h4 class="lh-1 mb-0" style="font-size:1.1rem">Rp {{ number_format($perdin['total_budget'], 0, ',', '.') }}</h4>
                    <h6 class="mb-0 text-muted">Total Anggaran Disetujui {{ now()->year }}</h6>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="font-weight-bold">Aktivitas Perjalanan Dinas Terbaru</span>
            <a href="{{ route('perdin.admin.requests') }}" class="btn btn-xs btn-outline-secondary">Lihat Semua</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="pl-3">No. Advance</th>
                        <th>Nama</th>
                        <th>Tujuan</th>
                        <th class="text-right">Anggaran</th>
                        <th class="text-center">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($perdin['recent'] as $p)
                <tr>
                    <td class="pl-3 font-weight-bold">{{ $p->no_advance }}</td>
                    <td>{{ $p->user?->name ?? '-' }}</td>
                    <td>{{ $p->destination }}</td>
                    <td class="text-right">Rp {{ number_format($p->total_budget, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ \App\Models\Perdin\PerdinRequest::$statusBadges[$p->status] }}">
                            {{ \App\Models\Perdin\PerdinRequest::$statusLabels[$p->status] }}
                        </span>
                    </td>
                    <td><a href="{{ route('perdin.show', $p) }}" class="btn btn-xs btn-outline-info"><i class="gd-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data perjalanan dinas.</td></tr>
                @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>{{-- /pane-perdin --}}


{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- TAB 4: PENGADUAN (WHISTLEBLOWER)                                    --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<div class="tab-pane fade" id="pane-wb" role="tabpanel">

    <div class="row mb-3">
        <div class="col-6 col-xl-3 mb-3">
            <div class="card flex-row align-items-center p-3" style="border-left:4px solid #ef4444;">
                <div class="icon icon-lg bg-soft-danger rounded-circle mr-3">
                    <i class="gd-alert icon-text d-inline-block text-danger"></i>
                </div>
                <div>
                    <h4 class="lh-1 mb-1">{{ $wb['new'] }}</h4>
                    <h6 class="mb-0 text-muted">Laporan Baru</h6>
                </div>
                @if($wb['new'] > 0)
                <a href="{{ route('whistleblower.admin.index', ['status' => 'new']) }}" class="stretched-link"></a>
                @endif
            </div>
        </div>
        <div class="col-6 col-xl-3 mb-3">
            <div class="card flex-row align-items-center p-3" style="border-left:4px solid #f59e0b;">
                <div class="icon icon-lg bg-soft-warning rounded-circle mr-3">
                    <i class="gd-time icon-text d-inline-block text-warning"></i>
                </div>
                <div>
                    <h4 class="lh-1 mb-1">{{ $wb['in_review'] }}</h4>
                    <h6 class="mb-0 text-muted">Sedang Diproses</h6>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3 mb-3">
            <div class="card flex-row align-items-center p-3" style="border-left:4px solid #22c55e;">
                <div class="icon icon-lg bg-soft-success rounded-circle mr-3">
                    <i class="gd-check icon-text d-inline-block text-success"></i>
                </div>
                <div>
                    <h4 class="lh-1 mb-1">{{ $wb['resolved'] }}</h4>
                    <h6 class="mb-0 text-muted">Selesai</h6>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3 mb-3">
            <div class="card flex-row align-items-center p-3" style="border-left:4px solid #94a3b8;">
                <div class="icon icon-lg bg-soft-secondary rounded-circle mr-3">
                    <i class="gd-close icon-text d-inline-block text-secondary"></i>
                </div>
                <div>
                    <h4 class="lh-1 mb-1">{{ $wb['total'] }}</h4>
                    <h6 class="mb-0 text-muted">Total Semua</h6>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header font-weight-bold">Laporan per Kategori</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                        @forelse($wb['by_category'] as $cat)
                        <tr>
                            <td class="pl-3">{{ $cat->category }}</td>
                            <td class="text-right pr-3"><span class="badge badge-secondary">{{ $cat->total }}</span></td>
                        </tr>
                        @empty
                        <tr><td class="text-muted text-center py-3">Belum ada data.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header font-weight-bold">Laporan per Cabang</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                        @forelse($wb['by_branch'] as $branch)
                        <tr>
                            <td class="pl-3">{{ $branch->branch_location }}</td>
                            <td class="text-right pr-3"><span class="badge badge-info">{{ $branch->total }}</span></td>
                        </tr>
                        @empty
                        <tr><td class="text-muted text-center py-3">Belum ada data.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="font-weight-bold">Laporan Pengaduan Terbaru</span>
            <a href="{{ route('whistleblower.admin.index') }}" class="btn btn-xs btn-outline-secondary">Lihat Semua</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="pl-3">No. Tiket</th>
                        <th>Kategori</th>
                        <th>Cabang</th>
                        <th>Pelapor</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Tanggal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($wb['recent'] as $w)
                <tr>
                    <td class="pl-3 font-weight-bold font-monospace">{{ $w->ticket_number }}</td>
                    <td style="font-size:.82rem;">{{ $w->category }}</td>
                    <td>{{ $w->branch_location ?? '—' }}</td>
                    <td>
                        @if($w->is_anonymous)
                            <span class="badge badge-secondary">Anonim</span>
                        @else
                            {{ $w->reporter_name ?? '—' }}
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge badge-{{ $w->status_badge }}">{{ $w->status_label }}</span>
                    </td>
                    <td class="text-center">{{ $w->created_at->format('d/m/Y') }}</td>
                    <td><a href="{{ route('whistleblower.admin.show', $w) }}" class="btn btn-xs btn-outline-info"><i class="gd-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada laporan pengaduan.</td></tr>
                @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>{{-- /pane-wb --}}


{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- TAB 5: KARYAWAN (KONTRAK)                                           --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<div class="tab-pane fade" id="pane-karyawan" role="tabpanel">

    @if($contractExpired->isNotEmpty())
    <div class="card mb-3 border-danger">
        <div class="card-header bg-danger text-white font-weight-bold">
            <i class="gd-alert mr-2"></i>
            Kontrak Sudah Berakhir ({{ $contractExpired->count() }} karyawan)
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="pl-3">Nama</th>
                        <th>NIP</th>
                        <th>Jabatan</th>
                        <th>Departemen</th>
                        <th class="text-center">Tgl. Berakhir</th>
                        <th class="text-center">Lewat</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($contractExpired as $emp)
                <tr class="table-danger">
                    <td class="pl-3 font-weight-bold">{{ $emp->name }}</td>
                    <td>{{ $emp->nip ?? '—' }}</td>
                    <td>{{ $emp->position?->name ?? '—' }}</td>
                    <td>{{ $emp->department?->name ?? '—' }}</td>
                    <td class="text-center text-danger font-weight-bold">{{ $emp->contract_end_date->format('d/m/Y') }}</td>
                    <td class="text-center">
                        <span class="badge badge-danger">{{ now()->diffInDays($emp->contract_end_date) }} hari</span>
                    </td>
                    <td><a href="{{ route('appraisal.employees.edit', $emp) }}" class="btn btn-xs btn-outline-danger"><i class="gd-pencil"></i></a></td>
                </tr>
                @endforeach
                </tbody>
            </table>
            </div>
        </div>
    </div>
    @endif

    @if($contractExpiring->isNotEmpty())
    <div class="card mb-3 border-warning">
        <div class="card-header bg-warning font-weight-bold">
            <i class="gd-time mr-2"></i>
            Kontrak Akan Berakhir dalam 2 Bulan ({{ $contractExpiring->count() }} karyawan)
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="pl-3">Nama</th>
                        <th>NIP</th>
                        <th>Jabatan</th>
                        <th>Departemen</th>
                        <th class="text-center">Tgl. Berakhir</th>
                        <th class="text-center">Sisa Hari</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($contractExpiring as $emp)
                @php $sisa = now()->diffInDays($emp->contract_end_date); @endphp
                <tr class="{{ $sisa <= 14 ? 'table-danger' : 'table-warning' }}">
                    <td class="pl-3 font-weight-bold">{{ $emp->name }}</td>
                    <td>{{ $emp->nip ?? '—' }}</td>
                    <td>{{ $emp->position?->name ?? '—' }}</td>
                    <td>{{ $emp->department?->name ?? '—' }}</td>
                    <td class="text-center font-weight-bold">{{ $emp->contract_end_date->format('d/m/Y') }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $sisa <= 14 ? 'danger' : 'warning' }}">{{ $sisa }} hari</span>
                    </td>
                    <td><a href="{{ route('appraisal.employees.edit', $emp) }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></a></td>
                </tr>
                @endforeach
                </tbody>
            </table>
            </div>
        </div>
    </div>
    @endif

    @if($contractExpired->isEmpty() && $contractExpiring->isEmpty())
    <div class="card mb-3">
        <div class="card-body text-center text-muted py-5">
            <i class="gd-check d-block mb-2" style="font-size:2.5rem;opacity:.3;"></i>
            <p class="mb-0">Tidak ada kontrak karyawan yang perlu perhatian saat ini.</p>
        </div>
    </div>
    @endif

    {{-- Dokumen akan kadaluarsa --}}
    @if($hcx['documents_expiring']->isNotEmpty())
    <div class="card mb-3 border-warning">
        <div class="card-header bg-warning font-weight-bold">
            <i class="gd-file mr-2"></i> Dokumen Akan Kadaluarsa (60 hari) — {{ $hcx['documents_expiring']->count() }} dokumen
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="thead-light"><tr>
                    <th class="pl-3">Karyawan</th><th>Jenis</th><th>Judul</th>
                    <th class="text-center">Kadaluarsa</th><th class="text-center">Sisa</th>
                </tr></thead>
                <tbody>
                @foreach($hcx['documents_expiring'] as $d)
                @php $sisa = (int) now()->diffInDays($d['expires'], false); @endphp
                <tr class="{{ $sisa <= 14 ? 'table-warning' : '' }}">
                    <td class="pl-3 font-weight-bold">{{ $d['employee'] }}</td>
                    <td>{{ $d['doc_type'] }}</td>
                    <td class="text-muted">{{ $d['title'] }}</td>
                    <td class="text-center">{{ $d['expires']->format('d/m/Y') }}</td>
                    <td class="text-center"><span class="badge badge-{{ $sisa <= 14 ? 'danger' : 'secondary' }}">{{ $sisa }} hari</span></td>
                </tr>
                @endforeach
                </tbody>
            </table>
            </div>
        </div>
    </div>
    @endif

    <div class="text-right mt-2">
        <a href="{{ route('appraisal.employees.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="gd-user mr-1"></i> Lihat Semua Data Karyawan
        </a>
    </div>
</div>{{-- /pane-karyawan --}}

</div>{{-- /tab-content --}}

@endsection

@section('scripts')
<script>
(function(){
    var palette = ['#0f2a4a','#1a3f6f','#2563eb','#0ea5e9','#22c55e','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6'];
    var monthNames = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

    function doughnut(id, map) {
        var el = document.getElementById(id); if (!el) return;
        var labels = Object.keys(map), data = Object.values(map);
        if (!data.reduce(function(a,b){return a+(+b);},0)) return;
        new Chart(el, {
            type: 'doughnut',
            data: { labels: labels, datasets: [{ data: data, backgroundColor: palette, borderWidth: 2, borderColor: '#fff' }] },
            options: { plugins: { legend: { position: 'bottom', labels: { padding: 10, font: { size: 11 } } } }, cutout: '58%', maintainAspectRatio: false }
        });
    }
    function hbar(id, map) {
        var el = document.getElementById(id); if (!el) return;
        var labels = Object.keys(map), data = Object.values(map);
        if (!data.length) return;
        new Chart(el, {
            type: 'bar',
            data: { labels: labels, datasets: [{ data: data, backgroundColor: '#1a3f6f', borderRadius: 4 }] },
            options: { indexAxis: 'y', plugins: { legend: { display: false } },
                scales: { x: { grid: { color: '#f1f5f9' }, ticks: { precision: 0 } }, y: { grid: { display: false } } },
                maintainAspectRatio: false }
        });
    }
    function vbar(id, map) {
        var el = document.getElementById(id); if (!el) return;
        var labels = Object.keys(map), data = Object.values(map);
        if (!data.length) return;
        new Chart(el, {
            type: 'bar',
            data: { labels: labels, datasets: [{ data: data, backgroundColor: '#2563eb', borderRadius: 4 }] },
            options: { plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0 } }, x: { grid: { display: false } } },
                maintainAspectRatio: false }
        });
    }
    function lineByMonth(id, map) {
        var el = document.getElementById(id); if (!el) return;
        var labels = [], data = [];
        for (var m = 1; m <= 12; m++) { labels.push(monthNames[m]); data.push(+(map[m] || 0)); }
        new Chart(el, {
            type: 'line',
            data: { labels: labels, datasets: [{ data: data, borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,.15)', fill: true, tension: .3 }] },
            options: { plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } },
                maintainAspectRatio: false }
        });
    }
    function multiLine(id, labels, series) {
        var el = document.getElementById(id); if (!el) return;
        var colors = { 'Masuk': '#22c55e', 'Keluar': '#ef4444' };
        new Chart(el, {
            type: 'line',
            data: { labels: labels.map(function(s){ var p = s.split('-'); return monthNames[+p[1]] + " '" + p[0].slice(2); }),
                datasets: Object.keys(series).map(function(k){
                    return { label: k, data: series[k], borderColor: colors[k] || '#2563eb',
                        backgroundColor: 'transparent', tension: .3, pointRadius: 2 };
                }) },
            options: { plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0 } }, x: { grid: { display: false } } },
                maintainAspectRatio: false }
        });
    }

    @if($gradeDistrib->count())
    new Chart(document.getElementById('chart-grade'), {
        type: 'doughnut',
        data: {
            labels: {!! $gradeDistrib->keys()->toJson() !!},
            datasets: [{ data: {!! $gradeDistrib->values()->toJson() !!}, backgroundColor: palette, borderWidth: 3, borderColor: '#fff' }]
        },
        options: { cutout: '65%', plugins: { legend: { position: 'bottom', labels: { padding: 14, font: { size: 12 } } } }, maintainAspectRatio: true }
    });
    @endif

    // ── Tab SDM — chart-nya di-init saat tab dibuka pertama kali supaya
    //    canvas punya dimensi (Chart.js tidak bisa menggambar di elemen tersembunyi). ──
    var sdmDrawn = false;
    function drawSdm() {
        if (sdmDrawn) return;
        sdmDrawn = true;
        hbar('c-dept',   @json($hc['by_department']));
        hbar('c-branch', @json($hcx['by_branch']));
        hbar('c-level',  @json($hc['by_level']));
        doughnut('c-status', @json($hc['by_status']));
        doughnut('c-gender', @json($hc['by_gender']));
        vbar('c-tenure', @json($hcx['tenure_buckets']));
        lineByMonth('c-absen', @json($analytics['absenteeism']['monthly']));
        multiLine('c-movement', @json($hcx['movement_12m']['labels']), {
            'Masuk': @json($hcx['movement_12m']['hires']),
            'Keluar': @json($hcx['movement_12m']['exits'])
        });
    }

    // Preserve active tab on reload
    var hash = window.location.hash;
    if (hash) {
        var tab = document.querySelector('#dashTabs a[href="' + hash + '"]');
        if (tab) $(tab).tab('show');
    }
    if (hash === '#pane-sdm') drawSdm();
    $('#dashTabs a').on('shown.bs.tab', function(e) {
        var href = e.target.getAttribute('href');
        history.replaceState(null, null, href);
        if (href === '#pane-sdm') drawSdm();
    });
})();
</script>
@endsection
