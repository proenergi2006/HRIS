@extends('layouts.grain')
@section('title', '360° Feedback Saya')

@section('content')
@include('components.notification')

<div class="h3 mb-1">360° Feedback Saya</div>
<p class="text-muted">Penilaian yang perlu Anda isi sebagai rater.</p>

@forelse($reviews as $r)
  <div class="card mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
      <div>
        <div class="font-weight-bold">{{ $r->subject->name }}</div>
        <div class="small text-muted">{{ $r->cycle->title }} &middot; sebagai {{ \App\Models\Appraisal\Feedback360Review::$relationLabels[$r->relation_type] }}</div>
      </div>
      @if($r->isSubmitted())
        <span class="badge badge-success">Terkirim</span>
      @else
        <a href="{{ route('feedback360.fill', $r) }}" class="btn btn-sm btn-primary">Isi Penilaian</a>
      @endif
    </div>
  </div>
@empty
  <div class="card"><div class="card-body text-muted small">Tidak ada penilaian yang perlu diisi saat ini.</div></div>
@endforelse
@endsection
