@extends('layouts.grain')
@section('title', 'Import Absensi dari Mesin')

@section('content')
@include('components.notification')

<nav class="d-none d-md-block" aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('hr.attendance.index') }}">Rekap Absensi</a></li>
    <li class="breadcrumb-item active">Import</li>
  </ol>
</nav>

<div class="mb-3">
  <div class="h3 mb-0">Import Absensi dari Mesin</div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header font-weight-bold">Upload File dari Mesin Absensi</div>
      <div class="card-body">
        <form method="POST" action="{{ route('hr.attendance.import') }}" enctype="multipart/form-data">
          @csrf

          <div class="form-group">
            <label class="font-weight-bold">Perusahaan <span class="text-danger">*</span></label>
            <select name="company_id" class="form-control @error('company_id') is-invalid @enderror" required>
              @foreach($companies as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-row">
            <div class="form-group col">
              <label class="font-weight-bold">Bulan <span class="text-danger">*</span></label>
              <select name="month" class="form-control" required>
                @foreach(range(1,12) as $m)
                  <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="form-group col">
              <label class="font-weight-bold">Tahun <span class="text-danger">*</span></label>
              <select name="year" class="form-control" required>
                @foreach(range(now()->year, now()->year - 2) as $y)
                  <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="font-weight-bold">File Absensi <span class="text-danger">*</span></label>
            <div class="custom-file">
              <input type="file" name="file" id="importFile" class="custom-file-input" accept=".txt,.dat,.csv" required>
              <label class="custom-file-label" for="importFile">Pilih file</label>
            </div>
            <small class="form-text text-muted">Format: .txt / .dat / .csv dari mesin Solution X606-s / X601</small>
          </div>

          <button type="submit" class="btn btn-primary">Import</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card">
      <div class="card-header font-weight-bold">Cara Export dari Mesin Solution</div>
      <div class="card-body">
        <ol class="small mb-0" style="line-height:2">
          <li>Login ke software mesin Solution (USB / Ethernet)</li>
          <li>Pilih menu <strong>Laporan → Data Absensi</strong></li>
          <li>Pilih rentang tanggal sesuai bulan yang diinginkan</li>
          <li>Klik <strong>Export</strong> → pilih format <strong>TXT</strong> atau <strong>CSV</strong></li>
          <li>Upload file hasil export di form ini</li>
        </ol>
        <hr>
        <div class="small text-muted">
          <strong>Format file yang didukung:</strong><br>
          <code>ID&nbsp;&nbsp;Tanggal&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Waktu&nbsp;&nbsp;&nbsp;&nbsp;Device&nbsp;Status</code><br>
          <code>001&nbsp;2026-07-01&nbsp;&nbsp;08:00:10&nbsp;1&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;0</code><br>
          <small>Status: 0=Check In, 1=Check Out</small>
        </div>
        <hr>
        <p class="small text-muted mb-0">
          <strong>Catatan:</strong> NIP karyawan di SIPRO harus sama dengan ID karyawan di mesin absensi.
        </p>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('importFile').addEventListener('change', function() {
  this.nextElementSibling.textContent = this.files.length ? this.files[0].name : 'Pilih file';
});
</script>
@endsection
