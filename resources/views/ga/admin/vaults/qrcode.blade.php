@extends('layouts.grain')
@section('title', 'Barcode — ' . $vault->name)

@section('content')
<div class="mb-3">
  <a href="{{ route('ga.admin.vaults.index') }}" class="text-muted small">
    <i class="gd-angle-left"></i> Kembali
  </a>
</div>

<div class="h3 mb-4">Barcode — {{ $vault->name }}</div>

<div class="row justify-content-center">
  <div class="col-12 col-md-5">
    <div class="card text-center">
      <div class="card-body py-5" id="print-area">
        <div style="font-weight:700;font-size:1.1rem;margin-bottom:4px">{{ $vault->name }}</div>
        <div style="font-size:13px;color:#6b7280;margin-bottom:20px;letter-spacing:.08em">{{ $vault->barcode }}</div>
        <div style="background:#fff;display:inline-block;padding:12px;border-radius:12px;border:1px solid #e5e7eb">
          {!! $qr !!}
        </div>
        <div style="margin-top:20px;font-size:11px;color:#9ca3af">Scan untuk Melihat Isi Berangkas & Pengambilan / Pengembalian Dokumen</div>
        <div style="font-size:11px;color:#9ca3af">PT Pro Energi</div>
      </div>
      <div class="card-footer">
        <button onclick="window.print()" class="btn btn-primary mr-2">
          <i class="gd-print mr-1"></i> Cetak Barcode
        </button>
        <a href="{{ $url }}" target="_blank" class="btn btn-outline-secondary btn-sm">
          <i class="gd-link mr-1"></i> Buka Link
        </a>
      </div>
    </div>
  </div>
</div>
@endsection

@section('styles')
<style>
@media print {
  body * { visibility: hidden; }
  #print-area, #print-area * { visibility: visible; }
  #print-area { position: absolute; left: 50%; top: 50%; transform: translate(-50%,-50%); }
}
</style>
@endsection
