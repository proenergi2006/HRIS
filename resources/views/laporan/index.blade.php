@extends('layouts.grain')
@section('title', 'Laporan & Rekap')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Laporan & Rekap</div>
</div>

{{-- ── Filter Periode ── --}}
<div class="card mb-4">
  <div class="card-header font-weight-bold">Filter Periode</div>
  <div class="card-body">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-auto mb-0">
        <label class="small font-weight-bold">Bulan</label>
        <select name="bulan" class="form-control form-control-sm">
          @foreach(range(1,12) as $m)
            <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
              {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-auto mb-0">
        <label class="small font-weight-bold">Tahun</label>
        <select name="tahun" class="form-control form-control-sm">
          @foreach(range(now()->year, now()->year - 4) as $y)
            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-auto mb-0">
        <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
        <a href="{{ route('laporan.pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
           class="btn btn-outline-danger btn-sm ml-1">
          <i class="gd-file mr-1"></i> Download PDF
        </a>
      </div>
    </form>
  </div>
</div>

@php
  $periodLabel = \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y');
@endphp

<h5 class="mb-3 text-muted">Periode: <strong class="text-dark">{{ $periodLabel }}</strong></h5>

{{-- ── Summary Cards ── --}}
<div class="row mb-4">
  <div class="col-md-4 mb-3">
    <div class="card border-left-primary h-100" style="border-left:4px solid #0F2A4A">
      <div class="card-body">
        <div class="small text-muted font-weight-bold mb-1">Reimbursement</div>
        <div class="h4 mb-0 font-weight-bold">{{ $stats['reimb']['total'] }} pengajuan</div>
        <div class="small text-success">{{ $stats['reimb']['approved'] }} disetujui &mdash; Rp {{ number_format($stats['reimb']['amount'],0,',','.') }}</div>
        <div class="small text-warning">{{ $stats['reimb']['pending'] }} menunggu</div>
        <div class="small text-danger">{{ $stats['reimb']['rejected'] }} ditolak</div>
      </div>
    </div>
  </div>
  <div class="col-md-4 mb-3">
    <div class="card h-100" style="border-left:4px solid #2e6da4">
      <div class="card-body">
        <div class="small text-muted font-weight-bold mb-1">Perjalanan Dinas</div>
        <div class="h4 mb-0 font-weight-bold">{{ $stats['perdin']['total'] }} pengajuan</div>
        <div class="small text-success">{{ $stats['perdin']['approved'] }} disetujui &mdash; Rp {{ number_format($stats['perdin']['amount'],0,',','.') }}</div>
        <div class="small text-warning">{{ $stats['perdin']['pending'] }} menunggu</div>
        <div class="small text-danger">{{ $stats['perdin']['rejected'] }} ditolak</div>
      </div>
    </div>
  </div>
  <div class="col-md-4 mb-3">
    <div class="card h-100" style="border-left:4px solid #e8a020">
      <div class="card-body">
        <div class="small text-muted font-weight-bold mb-1">Pengaduan (Whistleblower)</div>
        <div class="h4 mb-0 font-weight-bold">{{ $stats['wb']['total'] }} laporan</div>
        <div class="small text-danger">{{ $stats['wb']['new'] }} belum ditangani</div>
        <div class="small text-success">{{ $stats['wb']['resolved'] }} selesai</div>
        <div class="small text-muted">&nbsp;</div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  {{-- Reimbursement per karyawan --}}
  <div class="col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span class="font-weight-bold">Rekap Reimbursement per Karyawan</span>
        <a href="{{ route('laporan.export.reimb', ['month' => $bulan, 'year' => $tahun]) }}"
           class="btn btn-xs btn-outline-success">
          <i class="gd-download mr-1"></i> Excel
        </a>
      </div>
      <div class="card-body p-0">
        @if($stats['reimb']['per_user']->isEmpty())
          <p class="text-center text-muted py-4">Tidak ada data disetujui periode ini.</p>
        @else
        <table class="table table-sm mb-0">
          <thead class="thead-light">
            <tr><th>Karyawan</th><th class="text-center">Pengajuan</th><th class="text-right">Total Klaim</th></tr>
          </thead>
          <tbody>
            @foreach($stats['reimb']['per_user'] as $row)
            <tr>
              <td>{{ $row['name'] }}</td>
              <td class="text-center">{{ $row['count'] }}</td>
              <td class="text-right">Rp {{ number_format($row['total'],0,',','.') }}</td>
            </tr>
            @endforeach
          </tbody>
          <tfoot class="table-light font-weight-bold">
            <tr>
              <td>Total</td>
              <td class="text-center">{{ $stats['reimb']['approved'] }}</td>
              <td class="text-right">Rp {{ number_format($stats['reimb']['amount'],0,',','.') }}</td>
            </tr>
          </tfoot>
        </table>
        @endif
      </div>
    </div>
  </div>

  {{-- Perdin per karyawan --}}
  <div class="col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span class="font-weight-bold">Rekap Perdin per Karyawan</span>
        <a href="{{ route('laporan.export.perdin', ['month' => $bulan, 'year' => $tahun]) }}"
           class="btn btn-xs btn-outline-success">
          <i class="gd-download mr-1"></i> Excel
        </a>
      </div>
      <div class="card-body p-0">
        @if($stats['perdin']['per_user']->isEmpty())
          <p class="text-center text-muted py-4">Tidak ada data disetujui periode ini.</p>
        @else
        <table class="table table-sm mb-0">
          <thead class="thead-light">
            <tr><th>Karyawan</th><th class="text-center">Pengajuan</th><th class="text-right">Total Budget</th></tr>
          </thead>
          <tbody>
            @foreach($stats['perdin']['per_user'] as $row)
            <tr>
              <td>{{ $row['name'] }}</td>
              <td class="text-center">{{ $row['count'] }}</td>
              <td class="text-right">Rp {{ number_format($row['total'],0,',','.') }}</td>
            </tr>
            @endforeach
          </tbody>
          <tfoot class="table-light font-weight-bold">
            <tr>
              <td>Total</td>
              <td class="text-center">{{ $stats['perdin']['approved'] }}</td>
              <td class="text-right">Rp {{ number_format($stats['perdin']['amount'],0,',','.') }}</td>
            </tr>
          </tfoot>
        </table>
        @endif
      </div>
    </div>
  </div>
</div>

{{-- Kontrak karyawan --}}
@if($stats['karyawan']['expired']->isNotEmpty() || $stats['karyawan']['expiring']->isNotEmpty())
<div class="card mb-4">
  <div class="card-header font-weight-bold">Status Kontrak Karyawan</div>
  <div class="card-body p-0">
    <table class="table table-sm mb-0">
      <thead class="thead-light">
        <tr><th>Nama</th><th>Departemen</th><th>Tgl Kontrak Berakhir</th><th>Status</th></tr>
      </thead>
      <tbody>
        @foreach($stats['karyawan']['expired'] as $e)
        <tr class="table-danger">
          <td>{{ $e->name }}</td>
          <td>{{ $e->department ?? '-' }}</td>
          <td>{{ $e->contract_end_date->format('d M Y') }}</td>
          <td><span class="badge badge-danger">Sudah Berakhir</span></td>
        </tr>
        @endforeach
        @foreach($stats['karyawan']['expiring'] as $e)
        <tr class="table-warning">
          <td>{{ $e->name }}</td>
          <td>{{ $e->department ?? '-' }}</td>
          <td>{{ $e->contract_end_date->format('d M Y') }}</td>
          <td><span class="badge badge-warning">{{ now()->diffInDays($e->contract_end_date) }} hari lagi</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

{{-- ── Export Semua Data ── --}}
<div class="card mb-4">
  <div class="card-header font-weight-bold">Export Data</div>
  <div class="card-body">
    <div class="d-flex flex-wrap" style="gap:.5rem">
      <a href="{{ route('laporan.export.karyawan') }}" class="btn btn-outline-primary">
        <i class="gd-user mr-1"></i> Export Karyawan (Excel)
      </a>
      <a href="{{ route('laporan.export.reimb', ['month' => $bulan, 'year' => $tahun]) }}"
         class="btn btn-outline-primary">
        <i class="gd-file mr-1"></i> Export Reimbursement Bulan Ini (Excel)
      </a>
      <a href="{{ route('laporan.export.perdin', ['month' => $bulan, 'year' => $tahun]) }}"
         class="btn btn-outline-primary">
        <i class="gd-file mr-1"></i> Export Perdin Bulan Ini (Excel)
      </a>
      <a href="{{ route('laporan.export.reimb') }}" class="btn btn-outline-secondary">
        Export Semua Reimbursement
      </a>
      <a href="{{ route('laporan.export.perdin') }}" class="btn btn-outline-secondary">
        Export Semua Perdin
      </a>
    </div>
  </div>
</div>

@endsection
