@extends('layouts.grain')
@section('title', 'Kelola Pengumuman')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Kelola Pengumuman</div>
  <a href="{{ route('announcements.create') }}" class="btn btn-primary btn-sm"><i class="gd-plus mr-1"></i> Pengumuman Baru</a>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light"><tr><th>Judul</th><th>Kategori</th><th>Perusahaan</th><th>Terbit</th><th class="text-center">Pin</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($announcements as $a)
          <tr>
            <td>{{ $a->title }}</td>
            <td><span class="badge badge-{{ $a->categoryBadge() }}">{{ $a->categoryLabel() }}</span></td>
            <td class="small">{{ $a->company?->short_name ?? 'Semua PT' }}</td>
            <td class="small">{{ $a->published_at?->format('d/m/Y H:i') }}</td>
            <td class="text-center">{{ $a->is_pinned ? '📌' : '' }}</td>
            <td>
              <form method="POST" action="{{ route('announcements.toggle-publish', $a) }}" class="d-inline">
                @csrf
                <button class="btn btn-xs btn-{{ $a->is_active ? 'success' : 'outline-secondary' }}">{{ $a->is_active ? 'Terbit' : 'Draft' }}</button>
              </form>
            </td>
            <td>
              <a href="{{ route('announcements.edit', $a) }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></a>
              <form method="POST" action="{{ route('announcements.destroy', $a) }}" class="d-inline" onsubmit="return confirm('Hapus pengumuman {{ $a->title }}?')">
                @csrf @method('DELETE')
                <button class="btn btn-xs btn-outline-danger"><i class="gd-trash"></i></button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-center text-muted py-3">Belum ada pengumuman.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
