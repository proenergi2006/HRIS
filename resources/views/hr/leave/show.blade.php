@extends('layouts.grain')
@section('title', 'Detail Pengajuan Cuti')

@section('content')
@include('components.notification')

@php
  $statusBadges = \App\Models\HR\LeaveRequest::$statusBadges;
  $statusLabels = \App\Models\HR\LeaveRequest::$statusLabels;
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
  <span class="badge badge-{{ $statusBadges[$leave->status] }}" style="font-size:.9rem;padding:.4em .8em">
    {{ $statusLabels[$leave->status] }}
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

    {{-- Approval Trail --}}
    <div class="card mb-3">
      <div class="card-header font-weight-bold">Riwayat Approval</div>
      <div class="card-body py-2">
        <div class="d-flex align-items-center mb-2">
          <div class="mr-3 text-center" style="width:36px">
            @if(in_array($leave->status, ['approved_manager','approved_hr','rejected']))
              <i class="gd-check text-success" style="font-size:1.2rem"></i>
            @else
              <i class="gd-time text-warning" style="font-size:1.2rem"></i>
            @endif
          </div>
          <div>
            <div class="font-weight-bold small">Atasan Langsung</div>
            <small class="text-muted">
              {{ $leave->managerApprover?->name ?? 'Menunggu' }}
              @if($leave->manager_approved_at) — {{ $leave->manager_approved_at->format('d/m/Y H:i') }} @endif
            </small>
            @if($leave->manager_notes)<br><small class="text-muted">Catatan: {{ $leave->manager_notes }}</small>@endif
          </div>
        </div>
        <div class="d-flex align-items-center">
          <div class="mr-3 text-center" style="width:36px">
            @if($leave->status === 'approved_hr')
              <i class="gd-check text-success" style="font-size:1.2rem"></i>
            @elseif($leave->status === 'rejected' && $leave->hr_approved_by)
              <i class="gd-close text-danger" style="font-size:1.2rem"></i>
            @else
              <i class="gd-time text-secondary" style="font-size:1.2rem"></i>
            @endif
          </div>
          <div>
            <div class="font-weight-bold small">HR / Admin</div>
            <small class="text-muted">
              {{ $leave->hrApprover?->name ?? 'Menunggu' }}
              @if($leave->hr_approved_at) — {{ $leave->hr_approved_at->format('d/m/Y H:i') }} @endif
            </small>
            @if($leave->hr_notes)<br><small class="text-muted">Catatan: {{ $leave->hr_notes }}</small>@endif
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Actions --}}
  <div class="col-md-5">
    @if($leave->isSubmitted())
    <div class="card mb-3 border-warning">
      <div class="card-header font-weight-bold text-warning">Approval Atasan Langsung</div>
      <div class="card-body">
        <form method="POST" action="{{ route('hr.leave.approve.manager', $leave) }}" class="mb-3">
          @csrf
          <div class="form-group">
            <label class="small font-weight-bold">Catatan (opsional)</label>
            <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea>
          </div>
          <button type="submit" class="btn btn-success btn-sm">Setujui (Atasan)</button>
        </form>
        <hr>
        <form method="POST" action="{{ route('hr.leave.reject', $leave) }}">
          @csrf
          <div class="form-group">
            <label class="small font-weight-bold">Alasan Penolakan <span class="text-danger">*</span></label>
            <textarea name="notes" class="form-control form-control-sm" rows="2" required></textarea>
          </div>
          <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tolak pengajuan ini?')">Tolak</button>
        </form>
      </div>
    </div>
    @endif

    @if($leave->isApprovedManager())
    <div class="card mb-3 border-info">
      <div class="card-header font-weight-bold text-info">Approval HR / Admin</div>
      <div class="card-body">
        <form method="POST" action="{{ route('hr.leave.approve.hr', $leave) }}" class="mb-3">
          @csrf
          <div class="form-group">
            <label class="small font-weight-bold">Catatan (opsional)</label>
            <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea>
          </div>
          <button type="submit" class="btn btn-success btn-sm">Setujui Final (HR)</button>
        </form>
        <hr>
        <form method="POST" action="{{ route('hr.leave.reject', $leave) }}">
          @csrf
          <div class="form-group">
            <label class="small font-weight-bold">Alasan Penolakan <span class="text-danger">*</span></label>
            <textarea name="notes" class="form-control form-control-sm" rows="2" required></textarea>
          </div>
          <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tolak pengajuan ini?')">Tolak</button>
        </form>
      </div>
    </div>
    @endif
  </div>
</div>
@endsection
