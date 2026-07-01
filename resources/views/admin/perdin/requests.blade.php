@extends('layouts.grain')
@section('title', 'Daftar Permohonan Perjalanan Dinas')

@php
  $labels = \App\Models\Perdin\PerdinRequest::$statusLabels;
  $badges = \App\Models\Perdin\PerdinRequest::$statusBadges;
@endphp

@section('content')
@include('components.notification')

<div class="mb-3">
  <div class="h3 mb-0">Permohonan Perjalanan Dinas</div>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-inline mb-0">
      <label class="font-weight-bold mr-2">Status:</label>
      <select name="status" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
        <option value="">Semua</option>
        @foreach($labels as $key => $lbl)
          <option value="{{ $key }}" {{ $status === $key ? 'selected' : '' }}>{{ $lbl }}</option>
        @endforeach
      </select>
    </form>
  </div>
</div>

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
      @forelse($requests as $r)
        <tr>
          <td class="font-weight-bold">{{ $r->no_advance }}</td>
          <td>{{ $r->user->name }}</td>
          <td>{{ $r->destination }}</td>
          <td>{{ $r->departure_date->format('d/m/Y') }}</td>
          <td class="text-right">Rp {{ number_format($r->total_budget, 0, ',', '.') }}</td>
          <td class="text-center"><span class="badge badge-{{ $badges[$r->status] }}">{{ $labels[$r->status] }}</span></td>
          <td class="text-right">
            <a href="{{ route('perdin.show', $r) }}" class="btn btn-xs btn-outline-info">
              <i class="gd-eye icon-text"></i>
            </a>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada permohonan.</td></tr>
      @endforelse
      </tbody>
    </table>
    </div>

    @if($requests->hasPages())
    <div class="mt-3">{{ $requests->links() }}</div>
    @endif
  </div>
</div>
@endsection
