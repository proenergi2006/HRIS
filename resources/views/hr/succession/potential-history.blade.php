@extends('layouts.grain')
@section('title', 'Riwayat Potensi — ' . $employee->name)

@php
  $ratingLabels = ['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi'];
  $ratingBadges = ['low' => 'secondary', 'medium' => 'warning', 'high' => 'success'];
@endphp

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('succession.nine-box') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali ke Grid 9-Kotak</a></div>
<div class="h3 mb-3">Riwayat Penilaian Potensi — {{ $employee->name }}</div>

<div class="card">
  <div class="card-body p-0">
    @forelse($history as $h)
      <div class="px-3 py-3 border-bottom">
        <div class="d-flex justify-content-between">
          <span class="badge badge-{{ $ratingBadges[$h->potential_rating] ?? 'secondary' }}">
            {{ $ratingLabels[$h->potential_rating] ?? $h->potential_rating }}
          </span>
          <span class="small text-muted">{{ $h->assessed_at->format('d/m/Y') }} — {{ $h->assessedBy?->name ?? '-' }}</span>
        </div>
        @if($h->notes)<div class="small mt-2">{{ $h->notes }}</div>@endif
      </div>
    @empty
      <div class="text-muted small p-4 text-center">Belum ada riwayat penilaian.</div>
    @endforelse
  </div>
</div>
@endsection
