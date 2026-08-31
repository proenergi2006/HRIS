@extends('layouts.grain')
@section('title', 'Detail Pengajuan Cuti')

@section('content')
@include('components.notification')

@php
  $statusBadges = \App\Models\HR\LeaveRequest::$statusBadges;
  $statusLabels = \App\Models\HR\LeaveRequest::$statusLabels;
  $ar = $leave->approvalRequest;
@endphp

<nav class="d-none d-md-block" aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('hr.leave.index') }}">Manajemen Cuti</a></li>
    <li class="breadcrumb-item active">Detail</li>
  </ol>
</nav>

<div class="mb-3 d-flex justify-content-between align-items-center">
  <div>
    <div class="h3 mb-0">Detail Pengajuan Cuti</div>
  </div>
  <span class="badge badge-{{ $statusBadges[$leave->status] ?? 'secondary' }}" style="font-size:.9rem;padding:.4em .8em">
    {{ $statusLabels[$leave->status] ?? ucfirst($leave->status) }}
  </span>
</div>

<div class="row">
  <div class="col-md-7">
    <div class="card mb-3">
      <div class="card-header font-weight-bold">Informasi Cuti</div>
      <div class="card-body">
        <table class="table table-sm mb-0">
          <tr><td class="font-weight-bold" style="width:160px">Karyawan</td><td>{{ $leave->employee->name }} <small class="text-muted">({{ $leave->employee->nip }})</small></td></tr>
          <tr><td class="font-weight-bold">Perusahaan</td><td>{{ $leave->employee->company?->name }}</td></tr>
          <tr><td class="font-weight-bold">Jenis Cuti</td><td>{{ $leave->leaveType->name }}</td></tr>
          <tr><td class="font-weight-bold">Tanggal</td><td>{{ $leave->start_date->format('d M Y') }} — {{ $leave->end_date->format('d M Y') }}</td></tr>
          <tr><td class="font-weight-bold">Total Hari</td><td><strong>{{ $leave->total_days }} hari kerja</strong></td></tr>
          <tr><td class="font-weight-bold">Alasan</td><td>{{ $leave->reason ?? '-' }}</td></tr>
          @if($leave->attachment_path)
          <tr>
            <td class="font-weight-bold">Lampiran</td>
            <td>
              <a href="{{ route('hr.leave.attachment', $leave) }}" target="_blank" class="btn btn-xs btn-outline-primary">
                <i class="gd-clip mr-1"></i> Lihat Lampiran
              </a>
            </td>
          </tr>
          @endif
          @if($balance)
          <tr>
            <td class="font-weight-bold">Saldo Cuti</td>
            <td>
              <small>Alokasi: {{ $balance->allocated }} | Terpakai: {{ $balance->used }} |
              <strong>Sisa: {{ $balance->getRemaining() }}</strong></small>
            </td>
          </tr>
          @endif
        </table>
      </div>
    </div>

    {{-- Alur Persetujuan (Approval Engine) --}}
    <div class="card mb-3">
      <div class="card-header font-weight-bold">Alur Persetujuan</div>
      <div class="card-body py-3">
        @if(! $ar)
          @if(in_array($leave->status, ['approved_hr','approved_manager','submitted','rejected']))
            <p class="text-muted small mb-0">Pengajuan lama (sebelum migrasi ke engine).
              Manager: {{ $leave->managerApprover?->name ?? '-' }} · HR: {{ $leave->hrApprover?->name ?? '-' }}
              @if($leave->hr_notes)<br>Catatan: {{ $leave->hr_notes }}@endif
            </p>
          @else
            <p class="text-muted small mb-0">Belum ada alur.</p>
          @endif
        @else
          <ol class="list-unstyled mb-0">
            @foreach($ar->steps as $s)
              <li class="d-flex mb-3">
                <span class="mr-3" style="width:24px">
                  @if($s->status === 'approved')<i class="gd-check text-success" style="font-size:1.1rem"></i>
                  @elseif($s->status === 'rejected')<i class="gd-close text-danger" style="font-size:1.1rem"></i>
                  @elseif($s->status === 'skipped')<i class="gd-minus text-muted" style="font-size:1.1rem"></i>
                  @else<i class="gd-time text-warning" style="font-size:1.1rem"></i>@endif
                </span>
                <div>
                  <div class="font-weight-bold small">Step {{ $s->step_order }} — {{ $s->approver_label }}</div>
                  <small class="text-muted">
                    {{ $s->approver?->name ?? ($s->approver_type === 'specific_role' ? 'berbasis role' : '—') }}
                    @if($s->acted_at) · {{ ucfirst($s->status) }} oleh {{ $s->actedBy?->name }} {{ $s->acted_at->format('d/m/Y H:i') }}@endif
                  </small>
                  @if($s->notes)<br><small class="font-italic">"{{ $s->notes }}"</small>@endif
                </div>
              </li>
            @endforeach
          </ol>
        @endif
      </div>
    </div>
  </div>

  {{-- Actions --}}
  <div class="col-md-5">
    <div class="card mb-3">
      <div class="card-body">
        @if($leave->isPending())
          <p class="small text-muted">Persetujuan dilakukan oleh approver lewat <a href="{{ route('approval.inbox.index') }}">Kotak Persetujuan</a> masing-masing.</p>
          <form method="POST" action="{{ route('hr.leave.cancel', $leave) }}" onsubmit="return confirm('Batalkan pengajuan cuti ini?')">
            @csrf
            <button class="btn btn-outline-danger btn-sm">Batalkan Pengajuan</button>
          </form>
        @elseif($leave->isApproved())
          <div class="text-success"><i class="gd-check mr-1"></i>Cuti disetujui. Saldo cuti karyawan sudah dipotong.</div>
        @elseif($leave->isRejected())
          <div class="text-danger"><i class="gd-close mr-1"></i>Pengajuan ditolak.
            @if($leave->hr_notes)<div class="small mt-1">{{ $leave->hr_notes }}</div>@endif
          </div>
        @elseif($leave->isCancelled())
          <div class="text-muted">Pengajuan dibatalkan.</div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
