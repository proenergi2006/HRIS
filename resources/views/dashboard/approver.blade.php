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
            <div class="icon icon-lg bg-soft-warning rounded-circle mr-3">
                <i class="gd-alert icon-text d-inline-block text-warning"></i>
            </div>
            <div>
                <h4 class="lh-1 mb-1">{{ $stats['pending'] }}</h4>
                <h6 class="mb-0 text-muted" style="font-size:0.75rem;">Menunggu Persetujuan</h6>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="card flex-row align-items-center p-3">
            <div class="icon icon-lg bg-soft-success rounded-circle mr-3">
                <i class="gd-check icon-text d-inline-block text-success"></i>
            </div>
            <div>
                <h4 class="lh-1 mb-1">{{ $stats['done'] }}</h4>
                <h6 class="mb-0 text-muted" style="font-size:0.75rem;">Sudah Diproses</h6>
            </div>
        </div>
    </div>
</div>

{{-- Pending list --}}
<div class="card">
    <div class="card-body">
        <h6 class="font-weight-bold mb-3">{{ $approverLabel }}</h6>

        @if($pending->isEmpty())
            <div class="text-center py-4 text-muted">
                <i class="gd-check-circle" style="font-size:2rem;"></i>
                <p class="mt-2 mb-0">{{ $emptyMsg }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th class="border-top-0">Karyawan</th>
                            <th class="border-top-0">Departemen</th>
                            <th class="border-top-0">Periode</th>
                            <th class="border-top-0 text-center">Skor</th>
                            <th class="border-top-0 text-center">Grade</th>
                            <th class="border-top-0">Status</th>
                            <th class="border-top-0"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($pending as $a)
                        @php
                            $bc = match($a->status) {
                                'submitted'      => 'warning',
                                'approved_user2' => 'info',
                                default          => 'secondary',
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="font-weight-bold">{{ $a->employee->name }}</div>
                                <small class="text-muted">{{ $a->employee->position }}</small>
                            </td>
                            <td>{{ $a->employee->department ?? '-' }}</td>
                            <td><small>{{ $a->period->name }}</small></td>
                            <td class="text-center">{{ $a->total_score ?: '-' }}</td>
                            <td class="text-center">
                                @if($a->grade)
                                    <span class="badge badge-info">{{ $a->grade }}</span>
                                @else -
                                @endif
                            </td>
                            <td><span class="badge badge-{{ $bc }}">{{ $a->status_label }}</span></td>
                            <td>
                                <a href="{{ route('appraisal.appraisals.show', $a) }}"
                                   class="btn btn-sm btn-success">
                                    Proses <i class="gd-angle-right icon-text"></i>
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
@endsection
