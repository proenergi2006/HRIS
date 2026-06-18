@extends('layouts.grain')
@section('title', 'Dashboard')

@section('content')
@include('components.notification')

<div class="mb-3 mb-md-4 d-flex justify-content-between align-items-center">
    <div class="h3 mb-0">Dashboard</div>
    <small class="text-muted">Selamat datang, <strong>{{ auth()->user()->name }}</strong></small>
</div>

{{-- ── Summary Cards ── --}}
<div class="row">
    <div class="col-6 col-xl-3 mb-3 mb-xl-4">
        <div class="card flex-row align-items-center p-3 p-md-4">
            <div class="icon icon-lg bg-soft-primary rounded-circle mr-3">
                <i class="gd-check-circle icon-text d-inline-block text-primary"></i>
            </div>
            <div>
                <h4 class="lh-1 mb-1">{{ $stats['total'] }}</h4>
                <h6 class="mb-0 text-muted">Total Penilaian</h6>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3 mb-3 mb-xl-4">
        <div class="card flex-row align-items-center p-3 p-md-4">
            <div class="icon icon-lg bg-soft-secondary rounded-circle mr-3">
                <i class="gd-pencil icon-text d-inline-block text-secondary"></i>
            </div>
            <div>
                <h4 class="lh-1 mb-1">{{ $stats['draft'] }}</h4>
                <h6 class="mb-0 text-muted">Draft</h6>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3 mb-3 mb-xl-4">
        <div class="card flex-row align-items-center p-3 p-md-4">
            <div class="icon icon-lg bg-soft-warning rounded-circle mr-3">
                <i class="gd-alert icon-text d-inline-block text-warning"></i>
            </div>
            <div>
                <h4 class="lh-1 mb-1">{{ $stats['pending'] }}</h4>
                <h6 class="mb-0 text-muted">Menunggu Approval</h6>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3 mb-3 mb-xl-4">
        <div class="card flex-row align-items-center p-3 p-md-4">
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

{{-- ── Row 1: Periode + Grade chart + Aksi Cepat ── --}}
<div class="row">
    {{-- Periode Aktif --}}
    <div class="col-12 col-md-4 mb-3 mb-md-4">
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
                <a href="{{ route('appraisal.periods.index') }}" class="btn btn-outline-primary btn-sm mt-2">
                    Kelola Periode
                </a>
            </div>
        </div>
    </div>

    {{-- Chart: Distribusi Grade --}}
    <div class="col-12 col-md-4 mb-3 mb-md-4">
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

    {{-- Aksi Cepat --}}
    <div class="col-12 col-md-4 mb-3 mb-md-4">
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
                    @php $newWb = \App\Models\WhistleblowerReport::where('status','new')->count(); @endphp
                    @if($newWb) <span class="badge badge-danger ml-1">{{ $newWb }}</span> @endif
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ── Row 2: Status chart + Departemen chart ── --}}
<div class="row">
    <div class="col-12 col-md-5 mb-3 mb-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="font-weight-bold mb-3">Status Penilaian</h6>
                @if($statusDistrib->count())
                    <canvas id="chart-status" style="max-height:210px;"></canvas>
                @else
                    <p class="text-muted">Belum ada data.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-12 col-md-7 mb-3 mb-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="font-weight-bold mb-3">Penilaian per Departemen</h6>
                @if($deptDistrib->count())
                    <canvas id="chart-dept" style="max-height:210px;"></canvas>
                @else
                    <p class="text-muted">Belum ada data.</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Penilaian Terbaru ── --}}
@if($recentAppraisals->count())
<div class="card mb-3 mb-md-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="font-weight-bold mb-0">Penilaian Terbaru</h6>
            <a href="{{ route('appraisal.appraisals.index') }}" class="text-muted small">Lihat semua</a>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th class="border-top-0">Karyawan</th>
                        <th class="border-top-0">Periode</th>
                        <th class="border-top-0 text-center">Skor</th>
                        <th class="border-top-0 text-center">Grade</th>
                        <th class="border-top-0">Status</th>
                        <th class="border-top-0"></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($recentAppraisals as $a)
                    @php
                        $bc = match($a->status) {
                            'draft'          => 'secondary',
                            'submitted'      => 'warning',
                            'approved_user2' => 'info',
                            'approved_cfo'   => 'success',
                            'rejected'       => 'danger',
                            default          => 'secondary',
                        };
                    @endphp
                    <tr>
                        <td>
                            <div class="font-weight-bold" style="font-size:0.85rem;">{{ $a->employee->name }}</div>
                            <small class="text-muted">{{ $a->employee->position }}</small>
                        </td>
                        <td><small>{{ $a->period->name }}</small></td>
                        <td class="text-center">{{ $a->total_score ?: '-' }}</td>
                        <td class="text-center">
                            @if($a->grade)
                                <span class="badge badge-info">{{ $a->grade }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td><span class="badge badge-{{ $bc }}">{{ $a->status_label }}</span></td>
                        <td>
                            <a href="{{ route('appraisal.appraisals.show', $a) }}" class="btn btn-xs btn-outline-info">
                                <i class="gd-eye icon-text"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

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
            datasets: [{
                data: {!! $gradeDistrib->values()->toJson() !!},
                backgroundColor: palette,
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            cutout: '65%',
            plugins: { legend: { position: 'bottom', labels: { padding: 14, font: { size: 12 } } } },
            maintainAspectRatio: true
        }
    });
    @endif

    @if($statusDistrib->count())
    var sMap   = { draft:'Draft', submitted:'Menunggu Persetujuan', approved_user2:'Menunggu Final', approved_cfo:'Final', rejected:'Dikembalikan' };
    var scMap  = { draft:'#94a3b8', submitted:'#f59e0b', approved_user2:'#3b82f6', approved_cfo:'#22c55e', rejected:'#ef4444' };
    var sL=[], sD=[], sC=[];
    @foreach($statusDistrib as $s => $c)
    sL.push(sMap['{{ $s }}'] || '{{ $s }}'); sD.push({{ $c }}); sC.push(scMap['{{ $s }}'] || '#94a3b8');
    @endforeach
    new Chart(document.getElementById('chart-status'), {
        type: 'doughnut',
        data: { labels: sL, datasets: [{ data: sD, backgroundColor: sC, borderWidth: 3, borderColor: '#fff' }] },
        options: {
            cutout: '60%',
            plugins: { legend: { position: 'bottom', labels: { padding: 10, font: { size: 11 } } } },
            maintainAspectRatio: true
        }
    });
    @endif

    @if($deptDistrib->count())
    new Chart(document.getElementById('chart-dept'), {
        type: 'bar',
        data: {
            labels: {!! $deptDistrib->keys()->toJson() !!},
            datasets: [{
                label: 'Jumlah Penilaian',
                data: {!! $deptDistrib->values()->toJson() !!},
                backgroundColor: '#1a3f6f',
                hoverBackgroundColor: '#2563eb',
                borderRadius: 5,
                borderSkipped: false
            }]
        },
        options: {
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { stepSize: 1, precision: 0 }, grid: { color: '#f1f5f9' } },
                y: { grid: { display: false } }
            },
            maintainAspectRatio: true
        }
    });
    @endif
})();
</script>
@endsection
