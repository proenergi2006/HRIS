@extends('layouts.grain')
@section('title', 'Edit Gaji — ' . $employee->name)
@section('content')
@include('components.notification')
<nav class="d-none d-md-block mb-2" aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('hr.payroll.salary.index', ['company_id' => $employee->company_id]) }}">Struktur Gaji</a></li>
    <li class="breadcrumb-item active">{{ $employee->name }}</li>
  </ol>
</nav>
<div class="mb-3">
  <div class="h3 mb-0">Komponen Gaji — {{ $employee->name }}</div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header font-weight-bold">Diisi Manual</div>
      <div class="card-body">
        <form method="POST" action="{{ route('hr.payroll.salary.update', $employee) }}">
          @csrf
          @foreach($components as $comp)
          <div class="form-group">
            <label class="font-weight-bold">{{ $comp->name }}
              <small class="font-weight-normal text-muted">({{ $comp->type === 'allowance' ? 'Tunjangan' : 'Potongan' }})</small>
            </label>
            <div class="input-group input-group-sm">
              <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
              <input type="number" name="components[{{ $comp->id }}]"
                     class="form-control"
                     value="{{ $existing[$comp->id] ?? '' }}"
                     min="0" step="1000" placeholder="0 = tidak digunakan">
            </div>
          </div>
          @endforeach
          <button type="submit" class="btn btn-primary">Simpan</button>
          <a href="{{ route('hr.payroll.salary.index', ['company_id' => $employee->company_id]) }}"
             class="btn btn-outline-secondary ml-2">Batal</a>
        </form>
      </div>
    </div>
  </div>

  @if($autoComponents->isNotEmpty())
  <div class="col-md-6">
    <div class="card">
      <div class="card-header font-weight-bold">Dihitung Otomatis saat Generate Slip</div>
      <div class="card-body">
        <p class="small text-muted">
          Komponen di bawah ini tidak diisi manual — nilainya dihitung ulang setiap "Generate Slip"
          berdasarkan gaji tetap (Gaji Pokok + Tunjangan Jabatan), data absensi, atau data reimbursement.
        </p>
        <ul class="list-unstyled small mb-0">
          @foreach($autoComponents as $comp)
          <li class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
            <div>
              <div class="font-weight-bold">{{ $comp->name }}</div>
              <div class="text-muted">
                @switch($comp->calculation_type)
                  @case('position_fixed')
                    Nominal tetap sesuai Jabatan karyawan (atur di halaman Kelola Jabatan)
                    @break
                  @case('position_daily')
                    Tarif harian sesuai Jabatan &times; jumlah hari hadir (atur di halaman Kelola Jabatan)
                    @break
                  @case('overtime')
                    Jam lembur (Input Lembur) &times; tarif/jam sesuai Jabatan (atur di halaman Kelola Jabatan)
                    @break
                  @case('percent_of_base')
                    {{ $comp->rate_percent }}% dari gaji tetap{{ $comp->salary_cap ? ' (maks. Rp '.number_format($comp->salary_cap,0,',','.').')' : '' }}
                    @break
                  @case('late_deduction')
                    Menit terlambat &times; tarif per menit (basis 173 jam/bulan)
                    @break
                  @case('medical_claim')
                    Reimbursement medical approved (cut-off tgl 16 bulan lalu s/d tgl 16 bulan ini)
                    @break
                  @case('mirror_pph21')
                    Mengikuti nominal "Potongan PPh 21" (gross-up)
                    @break
                @endswitch
              </div>
            </div>
            <span class="badge badge-{{ $comp->type === 'allowance' ? 'success' : 'danger' }}">
              {{ $comp->type === 'allowance' ? 'Tunjangan' : 'Potongan' }}
            </span>
          </li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>
  @endif
</div>
@endsection
