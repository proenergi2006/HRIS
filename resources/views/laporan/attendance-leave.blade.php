@extends('layouts.grain')
@section('title', 'Laporan Absensi & Cuti')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Laporan & Rekap</div>
</div>

<ul class="nav nav-pills mb-4">
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.index') }}">Rekap Umum</a></li>
  <li class="nav-item"><a class="nav-link active" href="{{ route('laporan.attendance-leave') }}">Absensi & Cuti</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.payroll') }}">Payroll Summary</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.headcount') }}">Headcount</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.analytics') }}">HR Analytics</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.report-builder') }}">Report Builder</a></li>
</ul>

<div class="card mb-4">
  <div class="card-header font-weight-bold">Filter</div>
  <div class="card-body">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-auto mb-0">
        <label class="small font-weight-bold">Perusahaan</label>
        <select name="company_id" class="form-control form-control-sm">
          @foreach($companies as $c)
            <option value="{{ $c->id }}" {{ $companyId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-auto mb-0">
        <label class="small font-weight-bold">Bulan</label>
        <select name="bulan" class="form-control form-control-sm">
          @foreach(range(1,12) as $m)
            <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
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
        <a href="{{ route('laporan.attendance-leave.pdf', ['company_id' => $companyId, 'bulan' => $bulan, 'tahun' => $tahun]) }}"
           class="btn btn-outline-danger btn-sm ml-1"><i class="gd-file mr-1"></i> Download PDF</a>
      </div>
    </form>
  </div>
</div>

@php $periodLabel = \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y'); @endphp
<h5 class="mb-3 text-muted">Periode: <strong class="text-dark">{{ $periodLabel }}</strong></h5>

{{-- Ringkasan status absensi --}}
<div class="row mb-4">
  @foreach($data['statusLabels'] as $key => $label)
    <div class="col-6 col-md-3 col-xl mb-3">
      <div class="card h-100" style="border-left:4px solid #0F2A4A">
        <div class="card-body py-3">
          <div class="small text-muted font-weight-bold mb-1">{{ $label }}</div>
          <div class="h4 mb-0 font-weight-bold">{{ $data['totals'][$key] ?? 0 }}</div>
        </div>
      </div>
    </div>
  @endforeach
</div>

<div class="card mb-4">
  <div class="card-header font-weight-bold">Rekap Absensi per Karyawan ({{ $data['total_records'] }} record)</div>
  <div class="card-body p-0">
    @if($data['per_employee']->isEmpty())
      <p class="text-center text-muted py-4">Tidak ada data absensi untuk perusahaan &amp; periode ini.</p>
    @else
    <div class="table-responsive">
      <table class="table table-sm mb-0 text-nowrap">
        <thead class="thead-light">
          <tr>
            <th>Karyawan</th>
            @foreach($data['statusLabels'] as $label)<th class="text-center">{{ $label }}</th>@endforeach
            <th class="text-right">Telat (mnt)</th>
            <th class="text-right">Lembur (mnt)</th>
          </tr>
        </thead>
        <tbody>
          @foreach($data['per_employee'] as $row)
          <tr>
            <td>{{ $row['name'] }}</td>
            @foreach($data['statusLabels'] as $key => $label)<td class="text-center">{{ $row['counts'][$key] ?? 0 }}</td>@endforeach
            <td class="text-right">{{ number_format($row['late_minutes'],0,',','.') }}</td>
            <td class="text-right">{{ number_format($row['overtime_minutes'],0,',','.') }}</td>
          </tr>
          @endforeach
        </tbody>
        <tfoot class="table-light font-weight-bold">
          <tr>
            <td>Total</td>
            @foreach($data['statusLabels'] as $key => $label)<td class="text-center">{{ $data['totals'][$key] ?? 0 }}</td>@endforeach
            <td colspan="2"></td>
          </tr>
        </tfoot>
      </table>
    </div>
    @endif
  </div>
</div>

<div class="card mb-4">
  <div class="card-header font-weight-bold">Rekap Cuti Disetujui per Tipe ({{ $data['leave_total_count'] }} pengajuan, {{ $data['leave_total_days'] }} hari)</div>
  <div class="card-body p-0">
    @if($data['leave_by_type']->isEmpty())
      <p class="text-center text-muted py-4">Tidak ada cuti disetujui untuk perusahaan &amp; periode ini.</p>
    @else
    <table class="table table-sm mb-0">
      <thead class="thead-light"><tr><th>Tipe Cuti</th><th class="text-center">Pengajuan</th><th class="text-right">Total Hari</th></tr></thead>
      <tbody>
        @foreach($data['leave_by_type'] as $row)
        <tr><td>{{ $row['type'] }}</td><td class="text-center">{{ $row['count'] }}</td><td class="text-right">{{ $row['days'] }}</td></tr>
        @endforeach
      </tbody>
      <tfoot class="table-light font-weight-bold">
        <tr><td>Total</td><td class="text-center">{{ $data['leave_total_count'] }}</td><td class="text-right">{{ $data['leave_total_days'] }}</td></tr>
      </tfoot>
    </table>
    @endif
  </div>
</div>
@endsection
