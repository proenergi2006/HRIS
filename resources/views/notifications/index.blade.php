@extends('layouts.grain')
@section('title', 'Notifikasi Saya')

@section('content')
@include('components.notification')

<div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:.5rem">
  <div class="h3 mb-0">Notifikasi Saya</div>
  <form method="POST" action="{{ route('notifications.read-all') }}">
    @csrf
    <button type="submit" class="btn btn-sm btn-outline-secondary">Tandai Semua Dibaca</button>
  </form>
</div>

<div class="card">
  <div class="card-body p-0">
    @forelse($notifications as $n)
      <a href="{{ route('notifications.read', $n->id) }}"
         class="d-flex align-items-start px-3 py-3 {{ $loop->last ? '' : 'border-bottom' }}"
         style="text-decoration:none;color:inherit;{{ $n->read_at ? '' : 'background:#f5f8ff' }}">
        <div class="mr-3 mt-1"><i class="{{ $n->data['icon'] ?? 'gd-bell' }}" style="font-size:1.2rem;color:#0F2A4A"></i></div>
        <div class="flex-grow-1" style="min-width:0">
          <div class="font-weight-bold small">
            {{ $n->data['title'] ?? 'Notifikasi' }}
            @if(!$n->read_at)<span class="badge badge-primary ml-1" style="font-size:.6rem">Baru</span>@endif
          </div>
          <div class="text-muted small">{{ $n->data['message'] ?? '' }}</div>
          <div class="text-muted mt-1" style="font-size:.72rem"><i class="gd-time mr-1"></i>{{ $n->created_at->diffForHumans() }}</div>
        </div>
      </a>
    @empty
      <div class="text-muted small p-4 text-center">Belum ada notifikasi.</div>
    @endforelse
  </div>
</div>

<div class="mt-3">{{ $notifications->links() }}</div>
@endsection
