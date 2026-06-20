@extends('layouts.grain')
@section('title', 'Detail Penggunaan')

@section('content')
@include('components.notification')

<div class="mb-3">
  <a href="{{ route('ga.admin.usages.index') }}" class="text-muted small">
    <i class="gd-angle-left"></i> Kembali
  </a>
</div>

<div class="row">
  <div class="col-12 col-md-5 mb-4">
    <div class="card">
      <div class="card-body">
        <h6 class="font-weight-bold mb-3">Informasi Penggunaan</h6>
        <div class="table-responsive">
        <table class="table table-sm mb-0">
          <tr><td class="text-muted" style="width:40%">Kendaraan</td><td class="font-weight-bold">{{ $usage->vehicle->name }}</td></tr>
          <tr><td class="text-muted">No. Polisi</td><td><span class="badge badge-dark">{{ $usage->vehicle->plate }}</span></td></tr>
          <tr><td class="text-muted">Peminjam</td><td>{{ $usage->driver_name }}</td></tr>
          <tr><td class="text-muted">No. HP</td><td>{{ $usage->driver_phone ?? '-' }}</td></tr>
          <tr><td class="text-muted">Tujuan</td><td>{{ $usage->destination }}</td></tr>
          <tr><td class="text-muted">Check In</td><td>{{ $usage->check_in_at->format('d M Y, H:i') }}</td></tr>
          <tr><td class="text-muted">Check Out</td><td>{{ $usage->check_out_at?->format('d M Y, H:i') ?? '-' }}</td></tr>
          <tr><td class="text-muted">Durasi</td><td>{{ $usage->duration }}</td></tr>
          <tr><td class="text-muted">KM</td><td>{{ $usage->km_out ? number_format($usage->km_out) . ' km' : '-' }}</td></tr>
          <tr><td class="text-muted">Status</td>
            <td>
              @if($usage->status === 'checked_in')
                <span class="badge badge-warning">Sedang Digunakan</span>
              @else
                <span class="badge badge-success">Sudah Kembali</span>
              @endif
            </td>
          </tr>
        </table>
        </div>
        @if($usage->keluhan)
          <div class="mt-3 p-3" style="background:#fef3c7;border-radius:8px">
            <div class="font-weight-bold text-warning mb-1" style="font-size:13px">⚠️ Keluhan</div>
            <div style="font-size:13px">{{ $usage->keluhan }}</div>
          </div>
        @endif
      </div>
    </div>
  </div>

  @if($usage->photo_dashboard)
  <div class="col-12 col-md-7 mb-4">
    <div class="card">
      <div class="card-body">
        <h6 class="font-weight-bold mb-3">Foto Dokumentasi</h6>
        <div class="row g-2">
          @foreach([
            'dashboard' => ['label'=>'Dashboard','emoji'=>'📊'],
            'front'     => ['label'=>'Depan','emoji'=>'⬆️'],
            'back'      => ['label'=>'Belakang','emoji'=>'⬇️'],
            'left'      => ['label'=>'Kiri','emoji'=>'◀️'],
            'right'     => ['label'=>'Kanan','emoji'=>'▶️'],
          ] as $side => $info)
            @if($usage->{'photo_'.$side})
            <div class="{{ $side === 'dashboard' ? 'col-12' : 'col-6' }} mb-2">
              <a href="{{ route('ga.admin.usages.photo', [$usage, $side]) }}" target="_blank">
                <img src="{{ route('ga.admin.usages.photo', [$usage, $side]) }}"
                     alt="{{ $info['label'] }}"
                     style="width:100%;height:{{ $side==='dashboard'?'180px':'120px' }};object-fit:cover;border-radius:10px;cursor:zoom-in">
              </a>
              <div class="text-center mt-1" style="font-size:11px;color:#6b7280">
                {{ $info['emoji'] }} {{ $info['label'] }}
              </div>
            </div>
            @endif
          @endforeach
        </div>
      </div>
    </div>
  </div>
  @endif
</div>
@endsection
