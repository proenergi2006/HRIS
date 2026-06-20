@extends('layouts.grain')
@section('title', 'Ruang Meeting')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center">
  <div class="h3 mb-0">Ruang Meeting</div>
  <a href="{{ route('ga.admin.rooms.create') }}" class="btn btn-primary">
    <i class="gd-plus mr-1"></i> Tambah Ruangan
  </a>
</div>

<div class="card">
  <div class="card-body">
    <div class="table-responsive">
    <table id="dt-rooms" class="table table-hover mb-0" style="width:100%">
      <thead class="thead-light">
        <tr>
          <th>Nama Ruangan</th>
          <th>Lokasi</th>
          <th class="text-center">Item Kebersihan</th>
          <th class="text-center">Total Laporan</th>
          <th class="text-center">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      @foreach($rooms as $room)
        <tr>
          <td class="font-weight-bold">{{ $room->name }}</td>
          <td>{{ $room->location ?? '-' }}</td>
          <td class="text-center">{{ $room->cleaning_items_count ?? $room->cleaningItems()->count() }}</td>
          <td class="text-center">{{ $room->cleaning_logs_count }}</td>
          <td class="text-center">
            @if($room->is_active)
              <span class="badge badge-success">Aktif</span>
            @else
              <span class="badge badge-secondary">Non-aktif</span>
            @endif
          </td>
          <td class="text-right" style="white-space:nowrap">
            <a href="{{ route('ga.admin.rooms.qrcode', $room) }}" class="btn btn-xs btn-outline-info mr-1" title="QR Code">
              <i class="gd-layers icon-text"></i>
            </a>
            <a href="{{ route('ga.admin.rooms.edit', $room) }}" class="btn btn-xs btn-outline-warning mr-1">
              <i class="gd-pencil icon-text"></i>
            </a>
            <form method="POST" action="{{ route('ga.admin.rooms.destroy', $room) }}" class="d-inline">
              @csrf @method('DELETE')
              <button type="button" class="btn btn-xs btn-outline-danger"
                      data-confirm="Hapus ruangan {{ $room->name }}?"
                      data-confirm-title="Hapus Ruangan"
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
</div>
@endsection

@section('scripts')
<script>
$('#dt-rooms').DataTable({ language: window.siproDtLang, order: [[0,'asc']], columnDefs: [{orderable:false,targets:-1}] });
</script>
@endsection
