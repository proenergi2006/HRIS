@extends('layouts.grain')
@section('title', '360° Feedback')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">360° Feedback</div>
  <a href="{{ route('appraisal.feedback360.create') }}" class="btn btn-primary btn-sm"><i class="gd-plus mr-1"></i> Cycle Baru</a>
</div>

<div class="card">
  <div class="card-body p-0">
    @if($cycles->isEmpty())
      <p class="text-muted small p-3 mb-0">Belum ada cycle 360°.</p>
    @else
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light"><tr><th class="pl-3">Judul</th><th>Perusahaan</th><th>Periode</th><th class="text-center">Progres</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach($cycles as $c)
          <tr>
            <td class="pl-3">{{ $c->title }}</td>
            <td class="text-muted small">{{ $c->company?->short_name ?? $c->company?->name }}</td>
            <td class="small">{{ $c->period_start->format('d/m/Y') }} – {{ $c->period_end->format('d/m/Y') }}</td>
            <td class="text-center small">{{ $c->submitted_count }}/{{ $c->reviews_count }} terkirim</td>
            <td><span class="badge badge-{{ \App\Models\Appraisal\Feedback360Cycle::$statusBadges[$c->status] ?? 'secondary' }}">{{ \App\Models\Appraisal\Feedback360Cycle::$statusLabels[$c->status] ?? $c->status }}</span></td>
            <td class="pr-3 text-right"><a href="{{ route('appraisal.feedback360.show', $c) }}" class="btn btn-xs btn-outline-secondary">Kelola</a></td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
</div>
@endsection
