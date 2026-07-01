@extends('layouts.grain')
@section('title', 'Perjalanan Dinas')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center">
  <div class="h3 mb-0">Perjalanan Dinas</div>
  <a href="{{ route('perdin.create') }}" class="btn btn-primary">
    <i class="gd-plus mr-1"></i> Buat Permohonan
  </a>
</div>

<div class="card">
  <div class="card-body">
    <div class="table-responsive">
    <table class="table table-hover mb-0" style="width:100%">
      <thead class="thead-light">
        <tr>
          <th>No. Advance</th>
          <th>Tujuan</th>
          <th>Keberangkatan</th>
          <th class="text-right">Total Anggaran</th>
          <th class="text-center">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      @forelse($requests as $r)
        <tr>
          <td class="font-weight-bold">{{ $r->no_advance }}</td>
          <td>{{ $r->destination }}</td>
          <td>{{ $r->departure_date->format('d/m/Y') }}</td>
          <td class="text-right">Rp {{ number_format($r->total_budget, 0, ',', '.') }}</td>
          <td class="text-center">
            <span class="badge badge-{{ \App\Models\Perdin\PerdinRequest::$statusBadges[$r->status] }}">
              {{ \App\Models\Perdin\PerdinRequest::$statusLabels[$r->status] }}
            </span>
          </td>
          <td class="text-right" style="white-space:nowrap">
            <a href="{{ route('perdin.show', $r) }}" class="btn btn-xs btn-outline-info mr-1">
              <i class="gd-eye icon-text"></i>
            </a>
            @if($r->isEditable())
            <a href="{{ route('perdin.edit', $r) }}" class="btn btn-xs btn-outline-warning">
              <i class="gd-pencil icon-text"></i>
            </a>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada permohonan.</td></tr>
      @endforelse
      </tbody>
    </table>
    </div>

    @if($requests->hasPages())
    <div class="mt-3 d-flex justify-content-between align-items-center">
      <small class="text-muted">
        Menampilkan {{ $requests->firstItem() }}–{{ $requests->lastItem() }} dari {{ $requests->total() }} permohonan
      </small>
      {{ $requests->links() }}
    </div>
    @endif
  </div>
</div>
@endsection
