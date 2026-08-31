@extends('layouts.grain')
@section('title', $announcement->title)

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('announcements.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="card">
  <div class="card-body">
    <span class="badge badge-{{ $announcement->categoryBadge() }}">{{ $announcement->categoryLabel() }}</span>
    <h3 class="mt-2">{{ $announcement->title }}</h3>
    <div class="text-muted small mb-3">
      {{ $announcement->company?->short_name ?? 'Semua Perusahaan' }} — {{ $announcement->published_at?->translatedFormat('d F Y H:i') }}
      @if($announcement->createdBy) — oleh {{ $announcement->createdBy->name }}@endif
    </div>
    <div style="white-space:pre-wrap">{{ $announcement->body }}</div>

    @if($announcement->attachment_path)
      <div class="mt-3">
        <a href="{{ route('announcements.attachment', $announcement) }}" class="btn btn-sm btn-outline-secondary">
          <i class="gd-download mr-1"></i> {{ $announcement->attachment_name }}
        </a>
      </div>
    @endif
  </div>
</div>
@endsection
