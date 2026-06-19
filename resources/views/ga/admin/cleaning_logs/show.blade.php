@extends('layouts.grain')
@section('title', 'Detail Laporan Kebersihan')

@section('content')
<div class="mb-3">
  <a href="{{ route('ga.admin.cleaning-logs.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="gd-angle-left mr-1"></i> Kembali
  </a>
</div>

<div class="card mb-4">
  <div class="card-body">
    <div class="row">
      <div class="col-sm-4">
        <div class="text-muted small">Ruangan</div>
        <div class="font-weight-bold">{{ $log->room->name }}</div>
        @if($log->room->location)
          <div class="text-muted small">{{ $log->room->location }}</div>
        @endif
      </div>
      <div class="col-sm-4">
        <div class="text-muted small">Petugas</div>
        <div class="font-weight-bold">{{ $log->cleaner_name }}</div>
      </div>
      <div class="col-sm-4">
        <div class="text-muted small">Waktu Kebersihan</div>
        <div class="font-weight-bold">{{ $log->cleaned_at->format('d M Y, H:i') }}</div>
      </div>
    </div>
  </div>
</div>

@forelse($log->details as $detail)
<div class="card mb-3">
  <div class="card-header d-flex align-items-center">
    <span class="badge badge-secondary mr-2">{{ $loop->iteration }}</span>
    <span class="font-weight-bold">{{ $detail->item->name }}</span>
  </div>
  <div class="card-body">

    @if($detail->notes)
      <p class="mb-3 text-muted" style="font-size:.9rem">
        <i class="gd-notepad mr-1"></i> {{ $detail->notes }}
      </p>
    @endif

    @if($detail->photos->isNotEmpty())
      <div class="d-flex flex-wrap" style="gap:.75rem">
        @foreach($detail->photos as $photo)
        <a href="{{ route('ga.admin.cleaning-logs.photo', [$log, $photo]) }}" target="_blank">
          <img src="{{ route('ga.admin.cleaning-logs.photo', [$log, $photo]) }}"
               alt="Foto {{ $loop->iteration }}"
               style="width:110px;height:110px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6">
        </a>
        @endforeach
      </div>
    @else
      <p class="text-muted small mb-0">Tidak ada foto.</p>
    @endif

  </div>
</div>
@empty
  <div class="alert alert-info">Tidak ada detail item pada laporan ini.</div>
@endforelse
@endsection
