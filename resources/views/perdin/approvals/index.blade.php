@extends('layouts.grain')
@section('title', 'Persetujuan Perjalanan Dinas')

@php
  $labels = \App\Models\Perdin\PerdinRequest::$statusLabels;
  $badges = \App\Models\Perdin\PerdinRequest::$statusBadges;
@endphp

@section('content')
@include('components.notification')

<div class="h3 mb-3">Persetujuan Perjalanan Dinas</div>

<div class="card">
  <div class="card-body">
    <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead class="thead-light">
        <tr>
          <th>No. Advance</th>
          <th>Pemohon</th>
          <th>Tujuan</th>
          <th>Berangkat</th>
          <th class="text-right">Total</th>
          <th class="text-center">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      @forelse($pending as $r)
        <tr>
          <td class="font-weight-bold">{{ $r->no_advance }}</td>
          <td>{{ $r->user->name }}</td>
          <td>{{ $r->destination }}</td>
          <td>{{ $r->departure_date->format('d/m/Y') }}</td>
          <td class="text-right">Rp {{ number_format($r->total_budget, 0, ',', '.') }}</td>
          <td class="text-center"><span class="badge badge-{{ $badges[$r->status] }}">{{ $labels[$r->status] }}</span></td>
          <td class="text-right">
            <a href="{{ route('perdin.show', $r) }}" class="btn btn-xs btn-outline-primary">
              <i class="gd-eye icon-text mr-1"></i> Review
            </a>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada permohonan yang menunggu persetujuan Anda.</td></tr>
      @endforelse
      </tbody>
    </table>
    </div>

    @if($pending->hasPages())
    <div class="mt-3">{{ $pending->links() }}</div>
    @endif
  </div>
</div>
@endsection
