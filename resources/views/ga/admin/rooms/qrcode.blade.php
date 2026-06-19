@extends('layouts.grain')
@section('title', 'QR Code - ' . $room->name)

@section('content')
<div class="mb-3">
  <a href="{{ route('ga.admin.rooms.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="gd-angle-left mr-1"></i> Kembali
  </a>
</div>

<div class="card mx-auto text-center" style="max-width:420px">
  <div class="card-body py-4">
    <h5 class="font-weight-bold mb-1">{{ $room->name }}</h5>
    @if($room->location)
      <p class="text-muted small mb-3">{{ $room->location }}</p>
    @endif
    <div class="d-inline-block p-3 bg-white border rounded mb-3">
      {!! $qr !!}
    </div>
    <p class="text-muted small mb-3" style="word-break:break-all">{{ $url }}</p>
    <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-outline-primary mr-2">
      Buka Halaman
    </a>
    <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
      <i class="gd-printer mr-1"></i> Print
    </button>
  </div>
</div>
@endsection
