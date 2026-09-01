@extends('layouts.grain')
@section('title', 'Detail Cuti / Izin')

@php
  $statusBadges = \App\Models\HR\LeaveRequest::$statusBadges;
  $statusLabels = \App\Models\HR\LeaveRequest::$statusLabels;
  $ar = $leave->approvalRequest;
@endphp

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('leave.mine.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="h3 mb-0">Detail Pengajuan</div>
  <span class="badge badge-{{ $statusBadges[$leave->status] ?? 'secondary' }}" style="font-size:.9rem;padding:.4em .8em">
    {{ $statusLabels[$leave->status] ?? ucfirst($leave->status) }}
  </span>
</div>

<div class="row">
  <div class="col-md-7">
    <div class="card mb-3">
      <div class="card-header font-weight-bold">Informasi</div>
      <div class="card-body">
        <table class="table table-sm mb-0">
          <tr><td class="font-weight-bold" style="width:160px">Jenis</td><td>{{ $leave->leaveType?->name ?? '-' }}</td></tr>
          <tr><td class="font-weight-bold">Tanggal</td><td>{{ $leave->start_date->format('d M Y') }} — {{ $leave->end_date->format('d M Y') }}</td></tr>
          <tr><td class="font-weight-bold">Total Hari</td><td><strong>{{ $leave->total_days }} hari kerja</strong></td></tr>
          <tr><td class="font-weight-bold">Alasan</td><td>{{ $leave->reason ?? '-' }}</td></tr>
          @if($leave->attachment_path)
          <tr>
            <td class="font-weight-bold">Lampiran</td>
            <td><a href="{{ route('leave.mine.attachment', $leave) }}" target="_blank" class="btn btn-xs btn-outline-primary"><i class="gd-clip mr-1"></i>Lihat Lampiran</a></td>
          </tr>
          @endif
          @if($balance)
          <tr>
            <td class="font-weight-bold">Saldo Cuti</td>
            <td><small>Alokasi: {{ $balance->allocated }} | Terpakai: {{ $balance->used }} | <strong>Sisa: {{ $balance->getRemaining() }}</strong></small></td>
          </tr>
          @endif
        </table>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header font-weight-bold">Alur Persetujuan</div>
      <div class="card-body py-3">
        @if(! $ar)
          <p class="text-muted small mb-0">Belum ada alur.</p>
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
                    @if($s->acted_at) · {{ ucfirst($s->status) }} {{ $s->acted_at->format('d/m/Y H:i') }}@endif
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

  <div class="col-md-5">
    <div class="card">
      <div class="card-body">
        @if($leave->isPending())
          <p class="small text-muted">Menunggu persetujuan atasan.</p>
          <form method="POST" action="{{ route('leave.mine.cancel', $leave) }}" onsubmit="return confirm('Batalkan pengajuan ini?')">
            @csrf
            <button class="btn btn-outline-danger btn-sm">Batalkan Pengajuan</button>
          </form>
        @elseif($leave->isApproved())
          <div class="text-success"><i class="gd-check mr-1"></i>Disetujui. Saldo cuti Anda sudah dipotong.</div>
        @elseif($leave->isRejected())
          <div class="text-danger"><i class="gd-close mr-1"></i>Pengajuan ditolak.</div>
        @elseif($leave->isCancelled())
          <div class="text-muted">Pengajuan dibatalkan.</div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
