@extends('layouts.grain')
@section('title', 'Riwayat Penggunaan Kendaraan')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center">
  <div class="h3 mb-0">Riwayat Penggunaan</div>
</div>

{{-- Filter --}}
<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-4 mb-0">
        <label class="small font-weight-bold">Kendaraan</label>
        <select name="vehicle_id" class="form-control form-control-sm">
          <option value="">Semua</option>
          @foreach($vehicles as $v)
            <option value="{{ $v->id }}" {{ request('vehicle_id') == $v->id ? 'selected' : '' }}>
              {{ $v->name }} ({{ $v->plate }})
            </option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Status</label>
        <select name="status" class="form-control form-control-sm">
          <option value="">Semua</option>
          <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }}>Sedang Digunakan</option>
          <option value="checked_out" {{ request('status') == 'checked_out' ? 'selected' : '' }}>Sudah Kembali</option>
        </select>
      </div>
      <div class="form-group col-auto mb-0">
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('ga.admin.usages.index') }}" class="btn btn-outline-secondary btn-sm ml-1">Reset</a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <div class="table-responsive">
    <table id="dt-usages" class="table table-hover mb-0" style="width:100%">
      <thead class="thead-light">
        <tr>
          <th>Kendaraan</th>
          <th>Peminjam</th>
          <th>Tujuan</th>
          <th>Check In</th>
          <th>Check Out</th>
          <th class="text-center">KM</th>
          <th class="text-center">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      @foreach($usages as $u)
        <tr>
          <td>
            <div class="font-weight-bold" style="font-size:.85rem">{{ $u->vehicle->name }}</div>
            <small class="text-muted">{{ $u->vehicle->plate }}</small>
          </td>
          <td>{{ $u->driver_name }}</td>
          <td style="max-width:160px"><small>{{ $u->destination }}</small></td>
          <td><small>{{ $u->check_in_at->format('d/m/y H:i') }}</small></td>
          <td><small>{{ $u->check_out_at?->format('d/m/y H:i') ?? '-' }}</small></td>
          <td class="text-center"><small>{{ $u->km_out ? number_format($u->km_out) : '-' }}</small></td>
          <td class="text-center">
            @if($u->status === 'checked_in')
              <span class="badge badge-warning">Digunakan</span>
            @else
              <span class="badge badge-success">Kembali</span>
            @endif
          </td>
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
@endsection

@section('scripts')
<script>
$('#dt-usages').DataTable({ language: window.siproDtLang, order: [[3,'desc']], columnDefs: [{orderable:false,targets:-1}] });
</script>
@endsection
