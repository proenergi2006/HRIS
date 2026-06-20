@extends('layouts.grain')
@section('title', 'Semua Pengajuan Reimbursement')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center">
  <div class="h3 mb-0">Semua Pengajuan Reimbursement</div>
  <a href="{{ route('reimbursement.admin.balances') }}" class="btn btn-outline-primary btn-sm">
    <i class="gd-wallet mr-1"></i> Kelola Saldo
  </a>
</div>

{{-- Filter --}}
<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Karyawan</label>
        <select name="user_id" class="form-control form-control-sm">
          <option value="">Semua</option>
          @foreach($users as $u)
            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-2 mb-0">
        <label class="small font-weight-bold">Status</label>
        <select name="status" class="form-control form-control-sm">
          <option value="">Semua</option>
          @foreach(\App\Models\Reimbursement\ReimbursementRequest::$statusLabels as $val => $lbl)
            <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-2 mb-0">
        <label class="small font-weight-bold">Tahun</label>
        <select name="year" class="form-control form-control-sm">
          <option value="">Semua</option>
          @for($y = now()->year; $y >= 2024; $y--)
            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
          @endfor
        </select>
      </div>
      <div class="form-group col-auto mb-0">
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('reimbursement.admin.index') }}" class="btn btn-outline-secondary btn-sm ml-1">Reset</a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <table id="dt-admin-reimb" class="table table-hover mb-0" style="width:100%">
      <thead class="thead-light">
        <tr>
          <th>No. Pengajuan</th>
          <th>Karyawan</th>
          <th>Tanggal</th>
          <th>Untuk</th>
          <th class="text-right">Total Klaim</th>
          <th class="text-center">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      @foreach($requests as $r)
        <tr>
          <td class="font-weight-bold">{{ $r->request_number }}</td>
          <td>{{ $r->user->name }}</td>
          <td>{{ $r->request_date->format('d/m/Y') }}</td>
          <td>{{ \App\Models\Reimbursement\ReimbursementRequest::$medicalForLabels[$r->medical_for] }}</td>
          <td class="text-right">Rp {{ number_format($r->total_claim, 0, ',', '.') }}</td>
          <td class="text-center">
            <span class="badge badge-{{ \App\Models\Reimbursement\ReimbursementRequest::$statusBadges[$r->status] }}">
              {{ \App\Models\Reimbursement\ReimbursementRequest::$statusLabels[$r->status] }}
            </span>
          </td>
          <td class="text-right">
            <a href="{{ route('reimbursement.admin.show', $r) }}" class="btn btn-xs btn-outline-info">
              <i class="gd-eye icon-text"></i>
            </a>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

@section('scripts')
<script>
$('#dt-admin-reimb').DataTable({ language: window.siproDtLang, order: [[2,'desc']], columnDefs: [{orderable:false,targets:-1}] });
</script>
@endsection
