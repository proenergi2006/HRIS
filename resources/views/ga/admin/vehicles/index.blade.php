@extends('layouts.grain')
@section('title', 'Kendaraan GA')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center">
  <div class="h3 mb-0">🚗 Kendaraan</div>
  <a href="{{ route('ga.admin.vehicles.create') }}" class="btn btn-primary">
    <i class="gd-plus mr-1"></i> Tambah Kendaraan
  </a>
</div>

<div class="card">
  <div class="card-body p-0">
    <table id="dt-vehicles" class="table table-hover mb-0" style="width:100%">
      <thead class="thead-light">
        <tr>
          <th>Kendaraan</th>
          <th>No. Polisi</th>
          <th>Tipe</th>
          <th class="text-center">Status</th>
          <th class="text-center">Total Trip</th>
          <th class="text-center">Aktif</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      @foreach($vehicles as $v)
        <tr>
          <td>
            <div class="font-weight-bold">{{ $v->name }}</div>
            @if($v->color || $v->year)
              <small class="text-muted">{{ implode(', ', array_filter([$v->color, $v->year])) }}</small>
            @endif
          </td>
          <td><span class="badge badge-dark" style="font-size:.85rem;letter-spacing:.08em">{{ $v->plate }}</span></td>
          <td>{{ $v->type ?? '-' }}</td>
          <td class="text-center">
            @if($v->active_count)
              <span class="badge badge-warning">Digunakan</span>
            @else
              <span class="badge badge-success">Tersedia</span>
            @endif
          </td>
          <td class="text-center">{{ $v->usages_count }}</td>
          <td class="text-center">
            @if($v->is_active) <span class="badge badge-success">Ya</span>
            @else <span class="badge badge-secondary">Non-aktif</span> @endif
          </td>
          <td class="text-right" style="white-space:nowrap">
            <a href="{{ route('ga.admin.vehicles.qrcode', $v) }}" class="btn btn-xs btn-outline-info mr-1" title="QR Code">
              <i class="gd-layers icon-text"></i>
            </a>
            <a href="{{ route('ga.admin.vehicles.edit', $v) }}" class="btn btn-xs btn-outline-warning mr-1">
              <i class="gd-pencil icon-text"></i>
            </a>
            <form method="POST" action="{{ route('ga.admin.vehicles.destroy', $v) }}" class="d-inline">
              @csrf @method('DELETE')
              <button type="button" class="btn btn-xs btn-outline-danger"
                      data-confirm="Hapus kendaraan {{ $v->name }}?"
                      data-confirm-title="Hapus Kendaraan"
                      data-confirm-type="danger"
                      data-form="this.closest('form')">
                <i class="gd-trash icon-text"></i>
              </button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

@section('scripts')
<script>
$('#dt-vehicles').DataTable({ language: window.siproDtLang, order: [[0,'asc']], columnDefs: [{orderable:false,targets:-1}] });
</script>
@endsection
