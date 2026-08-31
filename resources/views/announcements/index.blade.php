@extends('layouts.grain')
@section('title', 'Pengumuman')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Pengumuman</div>
  @can('announcement.edit')
    <a href="{{ route('announcements.manage') }}" class="btn btn-outline-secondary btn-sm"><i class="gd-settings mr-1"></i> Kelola Pengumuman</a>
  @endcan
</div>

@if($announcements->isEmpty())
  <div class="card"><div class="card-body text-muted small">Belum ada pengumuman.</div></div>
@else
  @foreach($announcements as $a)
    <a href="{{ route('announcements.show', $a) }}" class="text-decoration-none">
      <div class="card mb-2 {{ $a->isReadBy(auth()->user()) ? '' : 'border-primary' }}">
        <div class="card-body py-3">
          <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.5rem">
            <div>
              @if($a->is_pinned)<i class="gd-pin text-warning mr-1" title="Disematkan"></i>@endif
              <span class="badge badge-{{ $a->categoryBadge() }}">{{ $a->categoryLabel() }}</span>
              <strong class="ml-1" style="color:#212529">{{ $a->title }}</strong>
              @unless($a->isReadBy(auth()->user()))<span class="badge badge-primary badge-pill ml-1">Baru</span>@endunless
            </div>
            <div class="small text-muted">{{ $a->published_at?->format('d/m/Y H:i') }}</div>
          </div>
          <div class="small text-muted mt-1">{{ \Illuminate\Support\Str::limit(strip_tags($a->body), 140) }}</div>
        </div>
      </div>
    </a>
  @endforeach
@endif
@endsection
