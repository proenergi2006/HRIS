@extends('layouts.grain')
@section('title', 'Detail Surat')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('appraisal.employee-letters.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="card">
  <div class="card-header font-weight-bold d-flex justify-content-between align-items-center">
    <span>{{ $letter->title }}</span>
    <a href="{{ route('appraisal.employee-letters.pdf', $letter) }}" class="btn btn-sm btn-outline-info"><i class="gd-download mr-1"></i> Unduh PDF</a>
  </div>
  <div class="card-body">
    <dl class="row mb-3">
      <dt class="col-sm-3 text-muted">No. Surat</dt><dd class="col-sm-9">{{ $letter->letter_number }}</dd>
      <dt class="col-sm-3 text-muted">Karyawan</dt><dd class="col-sm-9">{{ $letter->employee?->name }}</dd>
      <dt class="col-sm-3 text-muted">Kategori</dt><dd class="col-sm-9">{{ $letter->categoryLabel() }}</dd>
      <dt class="col-sm-3 text-muted">Tanggal Terbit</dt><dd class="col-sm-9">{{ $letter->issued_date?->format('d/m/Y') }}</dd>
      <dt class="col-sm-3 text-muted">Diterbitkan Oleh</dt><dd class="col-sm-9">{{ $letter->issuedBy?->name ?? '—' }}</dd>
    </dl>
    <hr>
    <div style="white-space:pre-wrap">{{ $letter->body }}</div>

    <form method="POST" action="{{ route('appraisal.employee-letters.destroy', $letter) }}" class="mt-3" onsubmit="return confirm('Hapus surat ini?')">
      @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Hapus</button>
    </form>
  </div>
</div>
@endsection
