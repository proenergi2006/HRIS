@extends('layouts.grain')
@section('title', 'Pengajuan Lembur')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Pengajuan Lembur</div>
  <a href="{{ route('hr.overtime-requests.create') }}" class="btn btn-primary btn-sm">
    <i class="gd-plus mr-1"></i> Ajukan Lembur
  </a>
</div>

<div class="card">
  <div class="card-body">
    @if($requests->isEmpty())
      <p class="text-muted small mb-0">Belum ada pengajuan lembur.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light">
            <tr>
              @if($isHr)<th>Karyawan</th>@endif
              <th>Tanggal</th><th class="text-center">Jam</th><th>Alasan</th><th>Status</th><th></th>
            </tr>
          </thead>
          <tbody>
          @foreach($requests as $r)
            <tr>
              @if($isHr)<td>{{ $r->employee?->name }}</td>@endif
              <td>{{ $r->date?->format('d/m/Y') }}</td>
              <td class="text-center">{{ rtrim(rtrim(number_format((float) $r->planned_hours, 1), '0'), '.') }}</td>
              <td class="small text-muted">{{ \Illuminate\Support\Str::limit($r->reason, 40) ?: '—' }}</td>
              <td><span class="badge badge-{{ \App\Models\OvertimeRequest::$statusBadges[$r->status] ?? 'secondary' }}">{{ \App\Models\OvertimeRequest::$statusLabels[$r->status] ?? $r->status }}</span></td>
              <td><a href="{{ route('hr.overtime-requests.show', $r) }}" class="btn btn-xs btn-outline-secondary">Detail</a></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
