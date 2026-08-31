@extends('layouts.grain')
@section('title', 'Survey')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Survey Kepuasan / Engagement</div>
  @can('survey.edit')
    <a href="{{ route('surveys.manage.index') }}" class="btn btn-outline-secondary btn-sm"><i class="gd-settings mr-1"></i> Kelola Survey</a>
  @endcan
</div>

@if($surveys->isEmpty())
  <div class="card"><div class="card-body text-muted small">Tidak ada survey yang perlu diisi saat ini.</div></div>
@else
  @foreach($surveys as $s)
    <div class="card mb-2">
      <div class="card-body py-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
        <div>
          <strong>{{ $s->title }}</strong>
          @if($s->is_anonymous)<span class="badge badge-light border ml-1" style="font-size:.65rem">Anonim</span>@endif
          <div class="small text-muted">{{ \Illuminate\Support\Str::limit($s->description, 120) }}</div>
        </div>
        <a href="{{ route('surveys.show', $s) }}" class="btn btn-primary btn-sm">Isi Survey</a>
      </div>
    </div>
  @endforeach
@endif
@endsection
