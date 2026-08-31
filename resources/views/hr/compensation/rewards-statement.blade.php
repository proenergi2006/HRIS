@extends('layouts.grain')
@section('title', 'Total Rewards Statement')

@php
  $isMine = $isMine ?? false;
  $pdfUrl = $isMine
      ? route('payroll.my.rewards.pdf', ['year' => $year])
      : route('hr.compensation.rewards.pdf', ['employee' => $employee, 'year' => $year]);
  $baseUrl = $isMine ? route('payroll.my.rewards.index') : route('hr.compensation.rewards', $employee);
@endphp

@section('content')
@include('components.notification')

<div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:.5rem">
  <div>
    <div class="h3 mb-0">Total Rewards Statement</div>
    <div class="text-muted small">{{ $employee->name }} @if(!$isMine)— {{ $employee->nip }}@endif</div>
  </div>
  <div class="d-flex align-items-center" style="gap:.5rem">
    <form method="GET" action="{{ $baseUrl }}" class="form-inline">
      <select name="year" class="form-control form-control-sm" onchange="this.form.submit()">
        @foreach(range(now()->year, now()->year - 4) as $y)
          <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
        @endforeach
      </select>
    </form>
    <a href="{{ $pdfUrl }}" class="btn btn-sm btn-primary"><i class="gd-download mr-1"></i>Unduh PDF</a>
  </div>
</div>

<div class="alert alert-info small">
  Dihitung dari data payroll/THR/bonus <strong>periode yang sudah ditutup (closed)</strong> tahun {{ $year }} —
  bukan proyeksi. Bila tahun berjalan belum selesai, angka ini mencerminkan apa yang sudah benar-benar dibayarkan
  s.d. saat ini ({{ $monthsCounted }} periode gaji).
</div>

<div class="row">
  <div class="col-md-8">
    <div class="card mb-4">
      <div class="card-header font-weight-bold">Ringkasan Kompensasi Tunai {{ $year }}</div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <tbody>
            <tr><td>Gaji &amp; Tunjangan Tetap ({{ $monthsCounted }} periode)</td><td class="text-right">Rp {{ number_format($cashCompYtd,0,',','.') }}</td></tr>
            <tr><td>THR</td><td class="text-right">Rp {{ number_format($thrYtd,0,',','.') }}</td></tr>
            <tr><td>Bonus / Insentif</td><td class="text-right">Rp {{ number_format($bonusYtd,0,',','.') }}</td></tr>
            <tr class="font-weight-bold" style="background:#f1f3f6"><td>Total Kompensasi Tunai</td><td class="text-right">Rp {{ number_format($totalCash,0,',','.') }}</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header font-weight-bold">Slip Gaji {{ $year }}</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead class="thead-light"><tr><th>Periode</th><th class="text-right">Bruto</th><th class="text-right">Netto</th></tr></thead>
            <tbody>
            @forelse($slips as $s)
              <tr><td>{{ $s->period->period_label }}</td><td class="text-right">Rp {{ number_format($s->gross_salary,0,',','.') }}</td><td class="text-right">Rp {{ number_format($s->net_salary,0,',','.') }}</td></tr>
            @empty
              <tr><td colspan="3" class="text-muted small p-3">Belum ada slip gaji closed tahun ini.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-header font-weight-bold">Pengembangan Diri</div>
      <div class="card-body">
        <div class="h4 mb-0">{{ $trainingCount }}</div>
        <div class="text-muted small">Program training selesai tahun {{ $year }}</div>
      </div>
    </div>
  </div>
</div>
@endsection
