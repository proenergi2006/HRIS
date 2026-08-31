@extends('layouts.grain')
@section('title', 'Penggajian ' . $period->period_label)

@section('content')
@include('components.notification')

<nav class="d-none d-md-block" aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('hr.payroll.index') }}">Penggajian</a></li>
    <li class="breadcrumb-item active">{{ $period->period_label }}</li>
  </ol>
</nav>

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div>
    <div class="h3 mb-0">{{ $period->company?->name }} — {{ $period->period_label }}</div>
  </div>
  <div class="d-flex" style="gap:.5rem">
    @if($period->status === 'open')
      <form method="POST" action="{{ route('hr.payroll.generate', $period) }}">
        @csrf
        <button type="submit" class="btn btn-primary btn-sm"
                onclick="return confirm('Generate/update slip gaji semua karyawan aktif?')">
          <i class="gd-reload mr-1"></i> Generate Slip
        </button>
      </form>
      <form method="POST" action="{{ route('hr.payroll.close', $period) }}">
        @csrf
        <button type="submit" class="btn btn-outline-danger btn-sm"
                onclick="return confirm('Tutup periode ini? Slip tidak bisa digenerate ulang.')">
          Tutup Periode
        </button>
      </form>
    @else
      <span class="badge badge-success" style="font-size:.9rem;padding:.5em .8em">Ditutup</span>
    @endif
  </div>
</div>

@if($slipByEmp->isNotEmpty())
<div class="card mb-3">
  <div class="card-body py-3 d-flex align-items-end flex-wrap" style="gap:.75rem">
    <div>
      <label class="small font-weight-bold mb-1 d-block">File Transfer Bank</label>
      <select id="disb-format" class="form-control form-control-sm d-inline-block" style="width:auto">
        <option value="generic">Format Umum (CSV)</option>
        <option value="bca">BCA</option>
        <option value="mandiri">Mandiri</option>
      </select>
      <a href="#" id="disb-btn" class="btn btn-sm btn-outline-primary ml-1"
         data-base="{{ route('hr.payroll.disbursement', $period) }}">
        <i class="gd-download mr-1"></i> Unduh
      </a>
    </div>
    @if($period->status === 'open')
      <div class="small text-warning"><i class="gd-alert mr-1"></i> Periode masih terbuka — nilai gaji bersih belum final.</div>
    @endif
    <div class="small text-muted">Karyawan tanpa rekening akan dilewati. Format kolom per bank adalah perkiraan — verifikasi dengan pihak bank.</div>
  </div>
</div>
<script>
  document.getElementById('disb-btn')?.addEventListener('click', function (e) {
    e.preventDefault();
    var fmt = document.getElementById('disb-format').value;
    window.location = this.dataset.base + '?format=' + fmt;
  });
</script>
@endif

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="thead-light">
          <tr>
            <th>Karyawan</th>
            <th class="text-center">Hari Kerja</th>
            <th class="text-center">Hadir</th>
            <th class="text-center">Alpha</th>
            <th class="text-right">Total Tunjangan</th>
            <th class="text-right">Total Potongan</th>
            <th class="text-right">Gaji Bersih</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($employees as $emp)
          @php $slip = $slipByEmp[$emp->id] ?? null; @endphp
          <tr>
            <td>
              <div class="font-weight-bold" style="font-size:.88rem">{{ $emp->name }}</div>
              <small class="text-muted">{{ $emp->nip }}</small>
            </td>
            @if($slip)
              <td class="text-center">{{ $slip->working_days }}</td>
              <td class="text-center">{{ $slip->attendance_days }}</td>
              <td class="text-center {{ $slip->alpha_days > 0 ? 'text-danger font-weight-bold' : '' }}">{{ $slip->alpha_days }}</td>
              <td class="text-right">Rp {{ number_format($slip->total_allowances,0,',','.') }}</td>
              <td class="text-right text-danger">Rp {{ number_format($slip->total_deductions,0,',','.') }}</td>
              <td class="text-right font-weight-bold text-success">Rp {{ number_format($slip->net_salary,0,',','.') }}</td>
              <td>
                <a href="{{ route('hr.payroll.slip.pdf', [$period, $slip]) }}"
                   class="btn btn-xs btn-outline-danger" title="Download Slip PDF">
                  <i class="gd-file"></i>
                </a>
              </td>
            @else
              <td colspan="6" class="text-center text-muted small">Belum digenerate</td>
              <td></td>
            @endif
          </tr>
          @endforeach
        </tbody>
        @if($slipByEmp->isNotEmpty())
        <tfoot class="table-light font-weight-bold">
          <tr>
            <td colspan="4">Total</td>
            <td class="text-right">Rp {{ number_format($slipByEmp->sum('total_allowances'),0,',','.') }}</td>
            <td class="text-right text-danger">Rp {{ number_format($slipByEmp->sum('total_deductions'),0,',','.') }}</td>
            <td class="text-right text-success">Rp {{ number_format($slipByEmp->sum('net_salary'),0,',','.') }}</td>
            <td></td>
          </tr>
        </tfoot>
        @endif
      </table>
    </div>
  </div>
</div>
@endsection
