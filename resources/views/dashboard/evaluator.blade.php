@extends('layouts.grain')
@section('title', 'Dashboard')

@section('content')
@include('components.notification')

<div class="mb-3 mb-md-4 d-flex justify-content-between align-items-center">
    <div class="h3 mb-0">Dashboard</div>
    <small class="text-muted">Selamat datang, <strong>{{ auth()->user()->name }}</strong></small>
</div>

{{-- Summary --}}
<div class="row mb-3">
    <div class="col-6 col-md-3 mb-3">
        <div class="card flex-row align-items-center p-3">
            <div class="icon icon-lg bg-soft-primary rounded-circle mr-3">
                <i class="gd-check-circle icon-text d-inline-block text-primary"></i>
            </div>
            <div>
                <h4 class="lh-1 mb-1">{{ $stats['total'] }}</h4>
                <h6 class="mb-0 text-muted" style="font-size:0.75rem;">Total Penilaian</h6>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card flex-row align-items-center p-3">
            <div class="icon icon-lg bg-soft-secondary rounded-circle mr-3">
                <i class="gd-pencil icon-text d-inline-block text-secondary"></i>
            </div>
            <div>
                <h4 class="lh-1 mb-1">{{ $stats['draft'] }}</h4>
                <h6 class="mb-0 text-muted" style="font-size:0.75rem;">Draft / Perlu Dilengkapi</h6>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card flex-row align-items-center p-3">
            <div class="icon icon-lg bg-soft-warning rounded-circle mr-3">
                <i class="gd-alert icon-text d-inline-block text-warning"></i>
            </div>
            <div>
                <h4 class="lh-1 mb-1">{{ $stats['pending'] }}</h4>
                <h6 class="mb-0 text-muted" style="font-size:0.75rem;">Menunggu Approval</h6>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card flex-row align-items-center p-3">
            <div class="icon icon-lg bg-soft-success rounded-circle mr-3">
                <i class="gd-check icon-text d-inline-block text-success"></i>
            </div>
            <div>
                <h4 class="lh-1 mb-1">{{ $stats['final'] }}</h4>
                <h6 class="mb-0 text-muted" style="font-size:0.75rem;">Final</h6>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Draft yang perlu diselesaikan --}}
    @php $drafts = $myAppraisals->whereIn('status', ['draft', 'rejected']); @endphp
    @if($drafts->count())
    <div class="col-12 mb-3">
        <div class="card border-left-warning" style="border-left:4px solid #f0ad4e;">
            <div class="card-body">
                <h6 class="font-weight-bold mb-3 text-warning">Perlu Dilengkapi ({{ $drafts->count() }})</h6>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th class="border-top-0">Karyawan</th>
                                <th class="border-top-0">Periode</th>
                                <th class="border-top-0">Status</th>
                                <th class="border-top-0"></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($drafts as $a)
                            <tr>
                                <td>
                                    <div class="font-weight-bold">{{ $a->employee->name }}</div>
                                    <small class="text-muted">{{ $a->employee->position }}</small>
                                </td>
                                <td><small>{{ $a->period->name }}</small></td>
                                <td>
                                    <span class="badge badge-{{ $a->status === 'rejected' ? 'danger' : 'secondary' }}">
                                        {{ $a->status_label }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('appraisal.appraisals.edit', $a) }}"
                                       class="btn btn-sm btn-warning">
                                        Isi Sekarang <i class="gd-angle-right icon-text"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Semua penilaian --}}
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="font-weight-bold mb-0">Semua Penilaian Saya</h6>
                    <a href="{{ route('appraisal.appraisals.create') }}" class="btn btn-primary btn-sm">
                        + Buat Penilaian
                    </a>
                </div>
                @if($myAppraisals->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <p class="mb-2">Belum ada penilaian yang dibuat.</p>
                        <a href="{{ route('appraisal.appraisals.create') }}" class="btn btn-primary btn-sm">Buat Penilaian Pertama</a>
                    </div>
                @else
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
                            @foreach($myAppraisals as $a)
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
                                        <div class="font-weight-bold">{{ $a->employee->name }}</div>
                                        <small class="text-muted">{{ $a->employee->position }}</small>
                                    </td>
                                    <td><small>{{ $a->period->name }}</small></td>
                                    <td class="text-center">{{ $a->total_score ?: '-' }}</td>
                                    <td class="text-center">
                                        @if($a->grade)<span class="badge badge-info">{{ $a->grade }}</span>@else -@endif
                                    </td>
                                    <td><span class="badge badge-{{ $bc }}">{{ $a->status_label }}</span></td>
                                    <td>
                                        <a href="{{ route('appraisal.appraisals.show', $a) }}"
                                           class="btn btn-xs btn-outline-info">
                                            <i class="gd-eye icon-text"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
