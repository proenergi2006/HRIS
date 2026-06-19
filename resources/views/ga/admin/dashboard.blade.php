@extends('layouts.grain')
@section('title', 'Dashboard GA')

@section('content')
<div class="mb-3 mb-md-4 d-flex justify-content-between align-items-center">
  <div class="h3 mb-0">Dashboard GA</div>
  <small class="text-muted">Selamat datang, <strong>{{ auth()->user()->name }}</strong></small>
</div>

<div class="row">
  <div class="col-6 col-xl-3 mb-3">
    <div class="card flex-row align-items-center p-3 p-md-4">
      <div class="icon icon-lg bg-soft-primary rounded-circle mr-3">
        <i class="gd-layers icon-text d-inline-block text-primary"></i>
      </div>
      <div>
        <h4 class="lh-1 mb-1">{{ $totalVehicles }}</h4>
        <h6 class="mb-0 text-muted">Total Kendaraan</h6>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3 mb-3">
    <div class="card flex-row align-items-center p-3 p-md-4">
      <div class="icon icon-lg bg-soft-success rounded-circle mr-3">
        <i class="gd-check icon-text d-inline-block text-success"></i>
      </div>
      <div>
        <h4 class="lh-1 mb-1">{{ $availVehicles }}</h4>
        <h6 class="mb-0 text-muted">Tersedia</h6>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3 mb-3">
    <div class="card flex-row align-items-center p-3 p-md-4">
      <div class="icon icon-lg bg-soft-warning rounded-circle mr-3">
        <i class="gd-alert icon-text d-inline-block text-warning"></i>
      </div>
      <div>
        <h4 class="lh-1 mb-1">{{ $inUseVehicles }}</h4>
        <h6 class="mb-0 text-muted">Sedang Digunakan</h6>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3 mb-3">
    <div class="card flex-row align-items-center p-3 p-md-4">
      <div class="icon icon-lg bg-soft-info rounded-circle mr-3">
        <i class="gd-calendar icon-text d-inline-block text-info"></i>
      </div>
      <div>
        <h4 class="lh-1 mb-1">{{ $todayUsages }}</h4>
        <h6 class="mb-0 text-muted">Trip Hari Ini</h6>
      </div>
    </div>
  </div>
</div>

@if($activeUsages->count())
<div class="card mb-4">
  <div class="card-body">
    <h6 class="font-weight-bold mb-3">🟡 Kendaraan Sedang Digunakan</h6>
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead><tr>
          <th class="border-top-0">Kendaraan</th>
          <th class="border-top-0">Peminjam</th>
          <th class="border-top-0">Tujuan</th>
          <th class="border-top-0">Check In</th>
          <th class="border-top-0"></th>
        </tr></thead>
        <tbody>
        @foreach($activeUsages as $u)
          <tr>
            <td>
              <div class="font-weight-bold" style="font-size:.85rem">{{ $u->vehicle->name }}</div>
              <small class="text-muted">{{ $u->vehicle->plate }}</small>
            </td>
            <td>{{ $u->driver_name }}</td>
            <td><small>{{ $u->destination }}</small></td>
            <td><small>{{ $u->check_in_at->format('H:i, d M') }}</small></td>
            <td>
              <a href="{{ route('ga.admin.usages.show', $u) }}" class="btn btn-xs btn-outline-info">
                <i class="gd-eye icon-text"></i>
              </a>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif

@if($recentUsages->count())
<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h6 class="font-weight-bold mb-0">✅ Kendaraan Baru Kembali</h6>
      <a href="{{ route('ga.admin.usages.index') }}" class="text-muted small">Lihat semua</a>
    </div>
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead><tr>
          <th class="border-top-0">Kendaraan</th>
          <th class="border-top-0">Peminjam</th>
          <th class="border-top-0">Tujuan</th>
          <th class="border-top-0">Kembali</th>
          <th class="border-top-0 text-center">Keluhan</th>
        </tr></thead>
        <tbody>
        @foreach($recentUsages as $u)
          <tr>
            <td>
              <div class="font-weight-bold" style="font-size:.85rem">{{ $u->vehicle->name }}</div>
              <small class="text-muted">{{ $u->vehicle->plate }}</small>
            </td>
            <td>{{ $u->driver_name }}</td>
            <td><small>{{ $u->destination }}</small></td>
            <td><small>{{ $u->check_out_at->format('H:i, d M') }}</small></td>
            <td class="text-center">
              @if($u->keluhan)
                <span class="badge badge-warning" title="{{ $u->keluhan }}">⚠️</span>
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif
@endsection
