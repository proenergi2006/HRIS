@extends('layouts.grain')
@section('title', 'Pengajuan Perubahan Data')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Pengajuan Perubahan Data</div>
  <a href="{{ route('appraisal.employee-data-changes.create') }}" class="btn btn-primary btn-sm">
    <i class="gd-plus mr-1"></i> Ajukan Perubahan
  </a>
</div>

<div class="card">
  <div class="card-body">
    @if($requests->isEmpty())
      <p class="text-muted small mb-0">Belum ada pengajuan perubahan data.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light">
            <tr>
              @if($isHr)<th>Karyawan</th>@endif
              <th>Field</th><th>Dari</th><th>Ke</th><th>Status</th><th></th>
            </tr>
          </thead>
          <tbody>
          @foreach($requests as $r)
            <tr>
              @if($isHr)<td>{{ $r->employee?->name }}</td>@endif
              <td>{{ $r->fieldLabel() }}</td>
              <td class="small text-muted">{{ \Illuminate\Support\Str::limit($r->old_value, 30) ?: '—' }}</td>
              <td class="small">{{ \Illuminate\Support\Str::limit($r->new_value, 30) }}</td>
              <td><span class="badge badge-{{ \App\Models\EmployeeDataChangeRequest::$statusBadges[$r->status] ?? 'secondary' }}">{{ \App\Models\EmployeeDataChangeRequest::$statusLabels[$r->status] ?? $r->status }}</span></td>
              <td><a href="{{ route('appraisal.employee-data-changes.show', $r) }}" class="btn btn-xs btn-outline-secondary">Detail</a></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
