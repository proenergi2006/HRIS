@extends('layouts.grain')
@section('title', 'Medical Reimbursement')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center">
  <div class="h3 mb-0">Medical Reimbursement</div>
  <a href="{{ route('reimbursement.create') }}" class="btn btn-primary">
    <i class="gd-plus mr-1"></i> Buat Pengajuan
  </a>
</div>

{{-- Saldo Card --}}
@if($balance)
<div class="row mb-4">
  <div class="col-sm-4">
    <div class="card text-center py-3">
      <div class="text-muted small">Saldo Awal {{ $balance->period_year }}</div>
      <div class="h4 font-weight-bold text-primary mt-1">Rp {{ number_format($balance->initial_balance, 0, ',', '.') }}</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card text-center py-3">
      <div class="text-muted small">Terpakai</div>
      <div class="h4 font-weight-bold text-danger mt-1">Rp {{ number_format($balance->used_balance, 0, ',', '.') }}</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card text-center py-3">
      <div class="text-muted small">Sisa Saldo</div>
      <div class="h4 font-weight-bold text-success mt-1">Rp {{ number_format($balance->remaining_balance, 0, ',', '.') }}</div>
    </div>
  </div>
</div>
@else
<div class="alert alert-warning mb-3">
  Anda belum memiliki saldo medical tahun {{ now()->year }}. Hubungi HR untuk pengaturan saldo.
</div>
@endif

{{-- Request Table --}}
<div class="card">
  <div class="card-body">
    <table id="dt-reimb" class="table table-hover mb-0" style="width:100%">
      <thead class="thead-light">
        <tr>
          <th>No. Pengajuan</th>
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
          <td>{{ $r->request_date->format('d/m/Y') }}</td>
          <td>{{ \App\Models\Reimbursement\ReimbursementRequest::$medicalForLabels[$r->medical_for] }}</td>
          <td class="text-right">Rp {{ number_format($r->total_claim, 0, ',', '.') }}</td>
          <td class="text-center">
            <span class="badge badge-{{ \App\Models\Reimbursement\ReimbursementRequest::$statusBadges[$r->status] }}">
              {{ \App\Models\Reimbursement\ReimbursementRequest::$statusLabels[$r->status] }}
            </span>
          </td>
          <td class="text-right" style="white-space:nowrap">
            <a href="{{ route('reimbursement.show', $r) }}" class="btn btn-xs btn-outline-info mr-1">
              <i class="gd-eye icon-text"></i>
            </a>
            @if($r->isDraft())
            <a href="{{ route('reimbursement.edit', $r) }}" class="btn btn-xs btn-outline-warning">
              <i class="gd-pencil icon-text"></i>
            </a>
            @endif
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
$('#dt-reimb').DataTable({ language: window.siproDtLang, order: [[1,'desc']], columnDefs: [{orderable:false,targets:-1}] });
</script>
@endsection
