@extends('layouts.grain')
@section('title', 'Manajemen Cuti')

@section('content')
@include('components.notification')

<nav class="d-none d-md-block" aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Manajemen Cuti</li>
  </ol>
</nav>

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Manajemen Cuti</div>
  <div class="d-flex" style="gap:.5rem">
    <a href="{{ route('hr.leave.balances') }}" class="btn btn-outline-secondary btn-sm">Saldo Cuti</a>
    <a href="{{ route('hr.leave.create') }}" class="btn btn-primary btn-sm">
      <i class="gd-plus mr-1"></i> Buat Pengajuan
    </a>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Perusahaan</label>
        <select name="company_id" class="form-control form-control-sm">
          <option value="">Semua</option>
          @foreach($companies as $c)
            <option value="{{ $c->id }}" {{ $companyId == $c->id ? 'selected' : '' }}>{{ $c->short_name ?? $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-auto mb-0">
        <label class="small font-weight-bold">Status</label>
        <select name="status" class="form-control form-control-sm">
          <option value="">Semua</option>
          @foreach(\App\Models\HR\LeaveRequest::$statusLabels as $val => $lbl)
            <option value="{{ $val }}" {{ $status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-auto mb-0">
        <label class="small font-weight-bold">Tahun</label>
        <select name="year" class="form-control form-control-sm">
          @foreach(range(now()->year, now()->year - 2) as $y)
            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-auto mb-0">
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('hr.leave.index') }}" class="btn btn-outline-secondary btn-sm ml-1">Reset</a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="thead-light">
          <tr>
            <th>Karyawan</th>
            <th>Perusahaan</th>
            <th>Jenis Cuti</th>
            <th>Tanggal</th>
            <th class="text-center">Hari</th>
            <th class="text-center">Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($requests as $req)
          <tr>
            <td>
              <div class="font-weight-bold" style="font-size:.88rem">{{ $req->employee->name }}</div>
              <small class="text-muted">{{ $req->employee->nip }}</small>
            </td>
            <td><small>{{ $req->employee->company?->short_name }}</small></td>
            <td><small>{{ $req->leaveType->name }}</small></td>
            <td>
              <small>{{ $req->start_date->format('d/m/Y') }}
              @if(! $req->start_date->equalTo($req->end_date))
                — {{ $req->end_date->format('d/m/Y') }}
              @endif</small>
            </td>
            <td class="text-center">{{ $req->total_days }}</td>
            <td class="text-center">
              <span class="badge badge-{{ \App\Models\HR\LeaveRequest::$statusBadges[$req->status] }}">
                {{ \App\Models\HR\LeaveRequest::$statusLabels[$req->status] }}
              </span>
            </td>
            <td>
              <a href="{{ route('hr.leave.show', $req) }}" class="btn btn-xs btn-outline-info">
                <i class="gd-eye"></i>
              </a>
            </td>
          </tr>
          @empty
          <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada pengajuan cuti.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="px-3 py-2">{{ $requests->links() }}</div>
  </div>
</div>
@endsection
