@extends('layouts.grain')
@section('title', 'Activity Log')

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
  <div class="card-body">
    <nav class="d-none d-md-block" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Activity Log</li>
      </ol>
    </nav>
    <div class="h3 mb-3">Activity Log</div>

    {{-- Filter --}}
    <form method="GET" class="form-row align-items-end mb-3">
      <div class="form-group col-12 col-md-2 mb-2">
        <label class="small font-weight-bold">Modul</label>
        <select name="log_name" class="form-control form-control-sm">
          <option value="">Semua</option>
          <option value="appraisal"    {{ request('log_name') === 'appraisal'    ? 'selected' : '' }}>Penilaian</option>
          <option value="reimbursement"{{ request('log_name') === 'reimbursement'? 'selected' : '' }}>Reimbursement</option>
          <option value="default"      {{ request('log_name') === 'default'      ? 'selected' : '' }}>Lainnya</option>
        </select>
      </div>
      <div class="form-group col-12 col-md-2 mb-2">
        <label class="small font-weight-bold">Tanggal</label>
        <input type="date" name="date" class="form-control form-control-sm"
               value="{{ request('date') }}">
      </div>
      <div class="form-group col-12 col-md-4 mb-2">
        <label class="small font-weight-bold">Cari</label>
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Nama user / deskripsi..." value="{{ request('search') }}">
      </div>
      <div class="form-group col-auto mb-2">
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('admin.activity-log.index') }}" class="btn btn-outline-secondary btn-sm ml-1">Reset</a>
      </div>
    </form>

    <div class="table-responsive">
    <table class="table table-sm mb-0">
      <thead class="thead-light">
        <tr>
          <th style="width:140px">Waktu</th>
          <th style="width:100px">Modul</th>
          <th style="width:140px">User</th>
          <th>Aksi</th>
          <th>Perubahan</th>
        </tr>
      </thead>
      <tbody>
      @forelse($logs as $log)
        @php
          $badgeColor = match($log->log_name) {
            'appraisal'    => 'primary',
            'reimbursement'=> 'success',
            default        => 'secondary',
          };
          $eventColor = match($log->event) {
            'created' => 'success',
            'updated' => 'warning',
            'deleted' => 'danger',
            default   => 'secondary',
          };
          $props = $log->properties->toArray();
          $old   = $props['old']        ?? [];
          $new   = $props['attributes'] ?? [];
        @endphp
        <tr>
          <td class="text-muted" style="font-size:.8rem;white-space:nowrap">
            {{ $log->created_at->format('d/m/Y H:i:s') }}
          </td>
          <td>
            <span class="badge badge-{{ $badgeColor }}">{{ $log->log_name }}</span>
          </td>
          <td style="font-size:.85rem">
            {{ $log->causer?->name ?? '<i>System</i>' }}
          </td>
          <td>
            <span class="badge badge-{{ $eventColor }}">{{ $log->event }}</span>
            <span class="text-muted ml-1" style="font-size:.8rem">{{ $log->description }}</span>
          </td>
          <td style="font-size:.8rem">
            @if($log->event === 'updated' && count($old))
              @foreach($old as $field => $before)
                @php $after = $new[$field] ?? null; @endphp
                <div>
                  <span class="font-weight-bold">{{ $field }}:</span>
                  <span class="text-danger">{{ is_array($before) ? json_encode($before) : $before }}</span>
                  <i class="gd-angle-right icon-text text-muted"></i>
                  <span class="text-success">{{ is_array($after) ? json_encode($after) : $after }}</span>
                </div>
              @endforeach
            @elseif($log->event === 'created' && count($new))
              @foreach($new as $field => $val)
                <span class="text-muted">{{ $field }}: </span>
                <span>{{ is_array($val) ? json_encode($val) : $val }}</span>{{ !$loop->last ? ' · ' : '' }}
              @endforeach
            @else
              <span class="text-muted">—</span>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada log aktivitas.</td></tr>
      @endforelse
      </tbody>
    </table>
    </div>

    @if($logs->hasPages())
    <div class="mt-3 d-flex justify-content-between align-items-center">
      <small class="text-muted">
        Menampilkan {{ $logs->firstItem() }}–{{ $logs->lastItem() }} dari {{ $logs->total() }} log
      </small>
      {{ $logs->links() }}
    </div>
    @endif

  </div>
</div>
@endsection
