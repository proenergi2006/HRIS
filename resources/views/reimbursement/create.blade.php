@extends('layouts.grain')
@section('title', 'Buat Pengajuan Reimbursement')

@section('content')
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
@include('reimbursement._form')
</form>
@endsection

@section('scripts')
@include('reimbursement._form_scripts')
@endsection
