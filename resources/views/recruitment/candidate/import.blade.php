@extends('layouts.grain')
@section('title', 'Import Kandidat')

@section('content')
@include('components.notification')

<div class="mb-3">
  <a href="{{ route('recruitment.requisitions.show', $requisition) }}" class="text-muted small">
    <i class="gd-angle-left"></i> Kembali ke {{ $requisition->title }}
  </a>
</div>

<div class="h3 mb-1">Import Kandidat</div>
<p class="text-muted small mb-4">Requisition: <strong>{{ $requisition->title }}</strong> — {{ $requisition->scopeLabel() }}</p>

@if(session('import_result'))
  @php $result = session('import_result'); @endphp
  <div class="card mb-4">
    <div class="card-body">
      <h6 class="font-weight-bold mb-3">Hasil Import</h6>
      <div class="row text-center mb-3">
        <div class="col-4">
          <div class="h3 mb-0 text-success">{{ $result['created'] }}</div>
          <div class="small text-muted">Kandidat Baru</div>
        </div>
        <div class="col-4">
          <div class="h3 mb-0 text-muted">{{ $result['skipped'] }}</div>
          <div class="small text-muted">Dilewati (sudah ada)</div>
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

      @if($result['created'] > 0)
        <a href="{{ route('recruitment.requisitions.show', $requisition) }}" class="btn btn-sm btn-outline-primary mt-3">
          Lihat Daftar Kandidat
        </a>
      @endif
    </div>
  </div>
@endif

<div class="row">
  <div class="col-md-7">
    <div class="card">
      <div class="card-header font-weight-bold">Upload File Excel</div>
      <div class="card-body">
        <form method="POST" action="{{ route('recruitment.candidates.import', $requisition) }}" enctype="multipart/form-data">
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
          Cocok untuk daftar pelamar yang sudah diseleksi/diekspor dari portal lowongan luar
          (mis. Jobstreet) — tidak perlu ketik ulang satu-satu.
        </p>
        <a href="{{ route('recruitment.candidates.import.template') }}" class="btn btn-outline-secondary btn-sm mb-3">
          <i class="gd-download mr-1"></i> Download Template
        </a>
        <ul class="small text-muted pl-3 mb-0">
          <li>Semua kandidat di file akan masuk ke requisition <strong>{{ $requisition->title }}</strong> ini —
            kalau ada pelamar untuk posisi lain, upload di halaman requisition masing-masing.</li>
          <li>Kolom <strong>Nama</strong> wajib diisi.</li>
          <li>Kolom <strong>Email</strong> dipakai mencegah dobel — kalau email sudah pernah diimport ke
            requisition yang sama, baris itu <strong>dilewati</strong> (aman diupload ulang file yang sama).</li>
          <li>Kolom <strong>Sumber</strong> kalau dikosongkan otomatis diisi "Jobstreet".</li>
          <li>Kandidat masuk dengan status awal <strong>Lamaran Masuk</strong> — lanjutkan proses
            seleksinya seperti biasa dari halaman kandidat.</li>
        </ul>
      </div>
    </div>
  </div>
</div>
@endsection
