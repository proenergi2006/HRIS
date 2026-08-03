@extends('layouts.grain')
@section('title', 'Import Data Karyawan')

@section('content')
@include('components.notification')

<div class="mb-3">
  <a href="{{ route('appraisal.employees.index') }}" class="text-muted small">
    <i class="gd-angle-left"></i> Kembali ke Data Karyawan
  </a>
</div>

<div class="h3 mb-4">Import Data Karyawan</div>

@if(session('import_result'))
  @php $result = session('import_result'); @endphp
  <div class="card mb-4">
    <div class="card-body">
      <h6 class="font-weight-bold mb-3">Hasil Import</h6>
      <div class="row text-center mb-3">
        <div class="col-4">
          <div class="h3 mb-0 text-success">{{ $result['created'] }}</div>
          <div class="small text-muted">Karyawan Baru</div>
        </div>
        <div class="col-4">
          <div class="h3 mb-0 text-info">{{ $result['updated'] }}</div>
          <div class="small text-muted">Diperbarui</div>
        </div>
        <div class="col-4">
          <div class="h3 mb-0 {{ count($result['errors']) ? 'text-danger' : 'text-muted' }}">{{ count($result['errors']) }}</div>
          <div class="small text-muted">Gagal</div>
        </div>
      </div>

      @if(count($result['errors']))
        <div class="alert alert-danger mb-0">
          <div class="font-weight-bold mb-2">Baris yang gagal diimport:</div>
          <ul class="mb-0 pl-3">
            @foreach($result['errors'] as $err)
              <li>Baris {{ $err['row'] }}: {{ $err['message'] }}</li>
            @endforeach
          </ul>
        </div>
      @endif
    </div>
  </div>
@endif

<div class="row">
  <div class="col-md-7">
    <div class="card">
      <div class="card-header font-weight-bold">Upload File Excel</div>
      <div class="card-body">
        <form method="POST" action="{{ route('appraisal.employees.import') }}" enctype="multipart/form-data">
          @csrf
          <div class="form-group">
            <label>File Excel (.xlsx / .xls / .csv)</label>
            <input type="file" name="file" class="form-control-file @error('file') is-invalid @enderror" accept=".xlsx,.xls,.csv" required>
            @error('file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
          </div>
          <button type="submit" class="btn btn-primary">
            <i class="gd-upload mr-1"></i> Import Sekarang
          </button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-5">
    <div class="card">
      <div class="card-header font-weight-bold">Panduan</div>
      <div class="card-body">
        <p class="small text-muted">
          Gunakan template Excel di bawah agar format kolom sesuai. Baris kedua pada template berisi contoh data —
          hapus atau ganti dengan data karyawan sebelum diupload.
        </p>
        <a href="{{ route('appraisal.employees.import.template') }}" class="btn btn-outline-secondary btn-sm mb-3">
          <i class="gd-download mr-1"></i> Download Template
        </a>
        <ul class="small text-muted pl-3 mb-0">
          <li>Kolom <strong>NIP</strong> dipakai untuk mencocokkan data. Jika NIP sudah ada, data karyawan itu akan
            <strong>diperbarui</strong>. Jika kosong atau belum ada, akan dibuat karyawan <strong>baru</strong>.</li>
          <li>Kolom <strong>Departemen</strong> dan <strong>Jabatan</strong> otomatis dibuatkan sebagai master data baru
            kalau namanya belum ada di sistem.</li>
          <li>Kolom <strong>Kode Perusahaan</strong> diisi salah satu: <code>proenergi</code>, <code>tds</code>, atau <code>pfr</code>.</li>
          <li>Kolom <strong>NIP Atasan</strong> diisi NIP karyawan lain yang sudah ada di sistem (untuk atasan langsung).</li>
          <li>Format tanggal: <code>YYYY-MM-DD</code> (contoh: 2024-01-15).</li>
        </ul>
      </div>
    </div>
  </div>
</div>
@endsection
