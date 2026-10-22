@extends('layouts.grain')
@section('title', 'Buat Pengajuan Reimbursement')

@section('content')
@include('components.notification')
<div class="mb-3">
  <a href="{{ route('reimbursement.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="gd-angle-left mr-1"></i> Kembali
  </a>
</div>

@if($balance)
<div class="alert alert-info mb-3 py-2">
  Sisa saldo medical {{ $balance->period_year }}: <strong>Rp {{ number_format($balance->remaining_balance, 0, ',', '.') }}</strong>
</div>
@else
<div class="alert alert-warning mb-3 py-2">Saldo belum diset oleh HR. Pengajuan bisa disimpan draft, namun tidak bisa disubmit.</div>
@endif

<form method="POST" action="{{ route('reimbursement.store') }}" enctype="multipart/form-data" id="reimb-form">
@csrf
@if($onBehalfOptions->isNotEmpty())
<div class="card mb-3"><div class="card-body py-3">
  <label class="mb-1">Atas nama karyawan</label>
  <select name="on_behalf_employee_id" class="form-control"
          onchange="window.location='{{ route('reimbursement.create') }}?on_behalf_employee_id=' + this.value">
    <option value="">— Diri sendiri —</option>
    @foreach($onBehalfOptions as $opt)
      <option value="{{ $opt->id }}" @selected($opt->id === $onBehalfId)>{{ $opt->name }}{{ $opt->nip ? ' — ' . $opt->nip : '' }}</option>
    @endforeach
  </select>
  <small class="text-muted">Admin HR dapat membuat pengajuan untuk karyawan lain (karyawan harus punya akun pengguna). Saldo dan daftar pasien mengikuti karyawan terpilih.</small>
</div></div>
@endif
@include('reimbursement._form')
</form>
@endsection

@section('scripts')
@include('reimbursement._form_scripts')
@endsection
