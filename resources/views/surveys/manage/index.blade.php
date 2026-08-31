@extends('layouts.grain')
@section('title', 'Kelola Survey')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Kelola Survey</div>
  <a href="{{ route('surveys.manage.create') }}" class="btn btn-primary btn-sm"><i class="gd-plus mr-1"></i> Survey Baru</a>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light"><tr><th>Judul</th><th>Perusahaan</th><th class="text-center">Anonim</th><th class="text-center">Responden</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($surveys as $s)
          <tr>
            <td>{{ $s->title }}</td>
            <td class="small">{{ $s->company?->short_name ?? 'Semua PT' }}</td>
            <td class="text-center">{{ $s->is_anonymous ? 'Ya' : 'Tidak' }}</td>
            <td class="text-center">{{ $s->responses_count }}</td>
            <td>
              <span class="badge badge-{{ $s->status === 'open' ? 'success' : ($s->status === 'closed' ? 'secondary' : 'warning') }}">
                {{ ucfirst($s->status) }}
              </span>
            </td>
            <td class="text-nowrap">
              <a href="{{ route('surveys.manage.results', $s) }}" class="btn btn-xs btn-outline-info">Hasil</a>
              <a href="{{ route('surveys.manage.edit', $s) }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></a>
              @if($s->status === 'draft')
                <form method="POST" action="{{ route('surveys.manage.open', $s) }}" class="d-inline" onsubmit="return confirm('Buka survey ini untuk diisi karyawan?')">
                  @csrf
                  <button class="btn btn-xs btn-outline-success">Buka</button>
                </form>
              @elseif($s->status === 'open')
                <form method="POST" action="{{ route('surveys.manage.close', $s) }}" class="d-inline" onsubmit="return confirm('Tutup survey ini?')">
                  @csrf
                  <button class="btn btn-xs btn-outline-secondary">Tutup</button>
                </form>
              @endif
              <form method="POST" action="{{ route('surveys.manage.destroy', $s) }}" class="d-inline" onsubmit="return confirm('Hapus survey {{ $s->title }}?')">
                @csrf @method('DELETE')
                <button class="btn btn-xs btn-outline-danger"><i class="gd-trash"></i></button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted py-3">Belum ada survey.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
