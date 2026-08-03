@extends('layouts.grain')
@section('title', 'Komponen Gaji')
@section('content')
@include('components.notification')

<nav class="d-none d-md-block" aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('hr.payroll.index') }}">Penggajian</a></li>
    <li class="breadcrumb-item active">Komponen Gaji</li>
  </ol>
</nav>

<div class="mb-3 d-flex justify-content-between align-items-center">
  <div class="h3 mb-0">Komponen Gaji</div>
  <a href="{{ route('hr.payroll.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>
<div class="row">
  <div class="col-md-5">
    <div class="card mb-3">
      <div class="card-header font-weight-bold">Tambah Komponen</div>
      <div class="card-body">
        <form method="POST" action="{{ route('hr.payroll.components.store') }}">
          @csrf
          <div class="form-group">
            <label class="small font-weight-bold">Berlaku untuk Perusahaan</label>
            <select name="company_id" class="form-control form-control-sm">
              <option value="">Semua Perusahaan</option>
              @foreach(\App\Models\Company::where('is_active',true)->get() as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label class="small font-weight-bold">Nama <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control form-control-sm" required placeholder="Contoh: Tunjangan Kehadiran">
          </div>
          <div class="form-group">
            <label class="small font-weight-bold">Jenis <span class="text-danger">*</span></label>
            <select name="type" class="form-control form-control-sm" required>
              <option value="allowance">Tunjangan / Pendapatan</option>
              <option value="deduction">Potongan</option>
            </select>
          </div>
          <div class="form-group">
            <label class="small font-weight-bold">Urutan</label>
            <input type="number" name="sort_order" class="form-control form-control-sm" value="99" min="0">
          </div>
          <button type="submit" class="btn btn-primary btn-sm">Tambah</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-7">
    <div class="card">
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead class="thead-light">
            <tr><th>Nama</th><th>Jenis</th><th>Kalkulasi</th><th>Perusahaan</th><th></th></tr>
          </thead>
          <tbody>
            @foreach($components as $c)
            <tr>
              <td>{{ $c->name }}</td>
              <td>
                <span class="badge badge-{{ $c->type === 'allowance' ? 'success' : 'danger' }}">
                  {{ $c->type === 'allowance' ? 'Tunjangan' : 'Potongan' }}
                </span>
              </td>
              <td style="min-width:180px">
                @if($c->calculation_type === 'manual')
                  <small class="text-muted">Manual</small>
                @elseif($c->calculation_type === 'percent_of_base')
                  <form method="POST" action="{{ route('hr.payroll.components.rate', $c) }}" class="form-inline">
                    @csrf @method('PUT')
                    <span class="badge badge-info mr-1">Otomatis</span>
                    <input type="number" name="rate_percent" value="{{ $c->rate_percent }}" step="0.01" min="0" max="100"
                           class="form-control form-control-sm mr-1" style="width:60px" title="Persen dari gaji tetap">
                    <span class="small mr-1">%</span>
                    <input type="number" name="salary_cap" value="{{ $c->salary_cap }}" min="0" step="1000"
                           class="form-control form-control-sm mr-1" style="width:90px" placeholder="Tanpa batas" title="Batas atas gaji">
                    <button type="submit" class="btn btn-xs btn-outline-primary">Simpan</button>
                  </form>
                @else
                  <span class="badge badge-info">Otomatis</span>
                  <small class="text-muted d-block">
                    {{ match($c->calculation_type) {
                      'late_deduction'  => 'Keterlambatan',
                      'medical_claim'   => 'Medical Claim',
                      'mirror_pph21'    => 'Gross-up PPh21',
                      'position_fixed'  => 'Tetap per Jabatan',
                      'position_daily'  => 'Harian per Jabatan',
                      'overtime'        => 'Lembur per Jabatan',
                      default           => '',
                    } }}
                  </small>
                @endif
              </td>
              <td><small>{{ $c->company?->short_name ?? 'Semua' }}</small></td>
              <td>
                <form method="POST" action="{{ route('hr.payroll.components.destroy', $c) }}"
                      onsubmit="return confirm('Hapus komponen ini?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-xs btn-outline-danger"><i class="gd-trash"></i></button>
                </form>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
