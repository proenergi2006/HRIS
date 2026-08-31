@extends('layouts.grain')
@section('title', 'Laporan Payroll Summary')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Laporan & Rekap</div>
</div>

<ul class="nav nav-pills mb-4">
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.index') }}">Rekap Umum</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.attendance-leave') }}">Absensi & Cuti</a></li>
  <li class="nav-item"><a class="nav-link active" href="{{ route('laporan.payroll') }}">Payroll Summary</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.headcount') }}">Headcount</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.analytics') }}">HR Analytics</a></li>
</ul>

<div class="card mb-4">
  <div class="card-header font-weight-bold">Pilih Periode Gaji</div>
  <div class="card-body">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-auto mb-0">
        <label class="small font-weight-bold">Periode</label>
        <select name="period_id" class="form-control form-control-sm">
          @foreach($periods as $p)
            <option value="{{ $p->id }}" {{ $periodId == $p->id ? 'selected' : '' }}>
              {{ $p->company?->name }} — {{ $p->period_label }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-auto mb-0">
        <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
        @if($period)
        <a href="{{ route('laporan.payroll.pdf', ['period_id' => $period->id]) }}"
           class="btn btn-outline-danger btn-sm ml-1"><i class="gd-file mr-1"></i> Download PDF</a>
        @endif
      </div>
    </form>
  </div>
</div>

@if(!$period)
  <div class="alert alert-info">Belum ada periode gaji. Buat dulu di menu Penggajian.</div>
@else
<h5 class="mb-3 text-muted">
  Periode: <strong class="text-dark">{{ $period->company?->name }} — {{ $period->period_label }}</strong>
  <span class="badge badge-{{ $period->status === 'closed' ? 'success' : 'warning' }}">{{ ucfirst($period->status) }}</span>
</h5>

<div class="row mb-4">
  <div class="col-md-3 mb-3">
    <div class="card h-100" style="border-left:4px solid #0F2A4A"><div class="card-body py-3">
      <div class="small text-muted font-weight-bold mb-1">Jumlah Slip</div>
      <div class="h4 mb-0 font-weight-bold">{{ $data['slip_count'] }}</div>
    </div></div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="card h-100" style="border-left:4px solid #16a34a"><div class="card-body py-3">
      <div class="small text-muted font-weight-bold mb-1">Total Gross</div>
      <div class="h5 mb-0 font-weight-bold">Rp {{ number_format($data['total_gross'],0,',','.') }}</div>
    </div></div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="card h-100" style="border-left:4px solid #dc2626"><div class="card-body py-3">
      <div class="small text-muted font-weight-bold mb-1">Total Potongan</div>
      <div class="h5 mb-0 font-weight-bold">Rp {{ number_format($data['total_deductions'],0,',','.') }}</div>
    </div></div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="card h-100" style="border-left:4px solid #2563eb"><div class="card-body py-3">
      <div class="small text-muted font-weight-bold mb-1">Total Net (Take Home)</div>
      <div class="h5 mb-0 font-weight-bold">Rp {{ number_format($data['total_net'],0,',','.') }}</div>
    </div></div>
  </div>
</div>

<div class="row">
  <div class="col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-header font-weight-bold">Rincian Tunjangan (Allowance)</div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead class="thead-light"><tr><th>Komponen</th><th class="text-right">Total</th></tr></thead>
          <tbody>
            @forelse($data['allowances'] as $row)
            <tr><td>{{ $row->component_name }}</td><td class="text-right">Rp {{ number_format($row->total,0,',','.') }}</td></tr>
            @empty
            <tr><td colspan="2" class="text-center text-muted py-3">Tidak ada data.</td></tr>
            @endforelse
          </tbody>
          <tfoot class="table-light font-weight-bold"><tr><td>Total Tunjangan</td><td class="text-right">Rp {{ number_format($data['total_allowances'],0,',','.') }}</td></tr></tfoot>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-header font-weight-bold">Rincian Potongan (Deduction)</div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead class="thead-light"><tr><th>Komponen</th><th class="text-right">Total</th></tr></thead>
          <tbody>
            @forelse($data['deductions'] as $row)
            <tr><td>{{ $row->component_name }}</td><td class="text-right">Rp {{ number_format($row->total,0,',','.') }}</td></tr>
            @empty
            <tr><td colspan="2" class="text-center text-muted py-3">Tidak ada data.</td></tr>
            @endforelse
          </tbody>
          <tfoot class="table-light font-weight-bold"><tr><td>Total Potongan</td><td class="text-right">Rp {{ number_format($data['total_deductions'],0,',','.') }}</td></tr></tfoot>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header font-weight-bold">Slip per Karyawan</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0 text-nowrap">
        <thead class="thead-light"><tr><th>Karyawan</th><th class="text-right">Gross</th><th class="text-right">Tunjangan</th><th class="text-right">Potongan</th><th class="text-right">Net</th></tr></thead>
        <tbody>
          @foreach($data['slips'] as $slip)
          <tr>
            <td>{{ $slip->employee?->name ?? '-' }}</td>
            <td class="text-right">{{ number_format($slip->gross_salary,0,',','.') }}</td>
            <td class="text-right">{{ number_format($slip->total_allowances,0,',','.') }}</td>
            <td class="text-right">{{ number_format($slip->total_deductions,0,',','.') }}</td>
            <td class="text-right">{{ number_format($slip->net_salary,0,',','.') }}</td>
          </tr>
          @endforeach
        </tbody>
        <tfoot class="table-light font-weight-bold">
          <tr>
            <td>Total</td>
            <td class="text-right">{{ number_format($data['total_gross'],0,',','.') }}</td>
            <td class="text-right">{{ number_format($data['total_allowances'],0,',','.') }}</td>
            <td class="text-right">{{ number_format($data['total_deductions'],0,',','.') }}</td>
            <td class="text-right">{{ number_format($data['total_net'],0,',','.') }}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>
@endif
@endsection
