@extends('layouts.grain')
@section('title', 'Riwayat Kebersihan')

@section('content')
@include('components.notification')

<div class="mb-3">
  <div class="h3 mb-0">Riwayat Kebersihan Ruangan</div>
</div>

{{-- Filter --}}
<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-4 mb-0">
        <label class="small font-weight-bold">Ruangan</label>
        <select name="room_id" class="form-control form-control-sm">
          <option value="">Semua</option>
          @foreach($rooms as $r)
            <option value="{{ $r->id }}" {{ request('room_id') == $r->id ? 'selected' : '' }}>
              {{ $r->name }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Tanggal</label>
        <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
      </div>
      <div class="form-group col-auto mb-0">
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('ga.admin.cleaning-logs.index') }}" class="btn btn-outline-secondary btn-sm ml-1">Reset</a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <div class="table-responsive">
    <table id="dt-logs" class="table table-hover mb-0" style="width:100%">
      <thead class="thead-light">
        <tr>
          <th>Ruangan</th>
          <th>Petugas</th>
          <th>Waktu</th>
          <th class="text-center">Item</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      @foreach($logs as $log)
        <tr>
          <td>
            <div class="font-weight-bold" style="font-size:.9rem">{{ $log->room->name }}</div>
            @if($log->room->location)
              <small class="text-muted">{{ $log->room->location }}</small>
            @endif
          </td>
          <td>{{ $log->cleaner_name }}</td>
          <td><small>{{ $log->cleaned_at->format('d/m/Y H:i') }}</small></td>
          <td class="text-center">{{ $log->details_count ?? $log->details()->count() }}</td>
          <td class="text-right">
            <a href="{{ route('ga.admin.cleaning-logs.show', $log) }}" class="btn btn-xs btn-outline-info">
              <i class="gd-eye icon-text"></i> Detail
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
$('#dt-logs').DataTable({ language: window.siproDtLang, order: [[2,'desc']], columnDefs: [{orderable:false,targets:-1}] });
</script>
@endsection
