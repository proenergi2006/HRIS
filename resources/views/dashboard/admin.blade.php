@extends('layouts.grain')
@section('title', 'Dashboard')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center">
    <div class="h3 mb-0">Dashboard</div>
    <small class="text-muted">Selamat datang, <strong>{{ auth()->user()->name }}</strong></small>
</div>

{{-- ── Tab Navigation ── --}}
<ul class="nav nav-tabs mb-4" id="dashTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="tab-overview" data-toggle="tab" href="#pane-overview" role="tab">
            <i class="gd-home mr-1"></i> Overview
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
                    <i class="gd-check-circle icon-text d-inline-block text-primary"></i>
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
                    <i class="gd-clock icon-text d-inline-block text-warning"></i>
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
                        <i class="gd-check-circle mr-1"></i> Data Penilaian
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
{{-- TAB 2: REIMBURSEMENT                                                --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<div class="tab-pane fade" id="pane-reimb" role="tabpanel">

    <div class="row mb-3">
        <div class="col-6 col-xl-4 mb-3">
            <div class="card flex-row align-items-center p-3" style="border-left:4px solid #f59e0b;">
                <div class="icon icon-lg bg-soft-warning rounded-circle mr-3">
                    <i class="gd-clock icon-text d-inline-block text-warning"></i>
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
                    <i class="gd-clock icon-text d-inline-block text-warning"></i>
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
                    <i class="gd-times-circle icon-text d-inline-block text-secondary"></i>
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
                    <td>{{ $emp->position ?? '—' }}</td>
                    <td>{{ $emp->department ?? '—' }}</td>
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
            <i class="gd-clock mr-2"></i>
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
                    <td>{{ $emp->position ?? '—' }}</td>
                    <td>{{ $emp->department ?? '—' }}</td>
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
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i class="gd-check-circle d-block mb-2" style="font-size:2.5rem;opacity:.3;"></i>
            <p class="mb-0">Tidak ada kontrak karyawan yang perlu perhatian saat ini.</p>
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
    var palette = ['#0f2a4a','#2563eb','#22c55e','#f59e0b','#ef4444','#8b5cf6','#0ea5e9','#ec4899'];

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

    // Preserve active tab on reload
    var hash = window.location.hash;
    if (hash) {
        var tab = document.querySelector('#dashTabs a[href="' + hash + '"]');
        if (tab) $(tab).tab('show');
    }
    $('#dashTabs a').on('shown.bs.tab', function(e) {
        history.replaceState(null, null, e.target.getAttribute('href'));
    });
})();
</script>
@endsection
