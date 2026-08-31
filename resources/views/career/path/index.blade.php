@extends('layouts.grain')
@section('title', 'Career Path')

@section('content')
@include('components.notification')

<div class="mb-3">
  <a href="{{ route('career.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali ke Career Management</a>
</div>

<div class="h3 mb-4">Career Path</div>

<div class="card mb-4">
  <div class="card-header font-weight-bold">Career Path Baru</div>
  <div class="card-body">
    <form method="POST" action="{{ route('career.paths.store') }}" class="form-row align-items-end">
      @csrf
      <div class="form-group col-md-4 mb-2">
        <input type="text" name="title" class="form-control" placeholder="mis. Jalur Karir Finance" required>
      </div>
      <div class="form-group col-md-3 mb-2">
        <select name="company_id" class="form-control">
          <option value="">Semua PT</option>
          @foreach($companies as $c)<option value="{{ $c->id }}">{{ $c->short_name ?? $c->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-group col-md-4 mb-2">
        <input type="text" name="description" class="form-control" placeholder="Deskripsi (opsional)">
      </div>
      <div class="form-group col-md-1 mb-2">
        <button type="submit" class="btn btn-primary btn-block">+</button>
      </div>
    </form>
  </div>
</div>

@forelse($paths as $path)
<div class="card mb-4">
  <div class="card-header font-weight-bold d-flex justify-content-between align-items-center">
    <span>{{ $path->title }} <span class="text-muted small font-weight-normal">({{ $path->company?->short_name ?? 'Semua PT' }})</span></span>
    <a href="#" class="btn btn-xs btn-outline-danger" data-confirm="Hapus career path {{ $path->title }}?" data-confirm-title="Hapus Career Path" data-form="cp-del-{{ $path->id }}"><i class="gd-trash"></i></a>
    <form id="cp-del-{{ $path->id }}" method="POST" action="{{ route('career.paths.destroy', $path) }}" class="d-none">@csrf @method('DELETE')</form>
  </div>
  <div class="card-body">
    @if($path->description)<p class="small text-muted">{{ $path->description }}</p>@endif

    @if($path->steps->isEmpty())
      <p class="text-muted small">Belum ada jenjang jabatan.</p>
    @else
      <ol class="mb-3">
        @foreach($path->steps as $step)
          <li class="mb-1">
            {{ $step->position?->name }}
            <a href="#" class="text-danger small ml-2" data-confirm="Hapus jenjang {{ $step->position?->name }}?" data-confirm-title="Hapus Jenjang" data-form="cps-del-{{ $step->id }}">Hapus</a>
            <form id="cps-del-{{ $step->id }}" method="POST" action="{{ route('career.paths.steps.destroy', [$path, $step]) }}" class="d-none">@csrf @method('DELETE')</form>
          </li>
        @endforeach
      </ol>
    @endif

    <form method="POST" action="{{ route('career.paths.steps.store', $path) }}" class="form-row align-items-end mb-0">
      @csrf
      <div class="form-group col-md-6 mb-0">
        <select name="position_id" class="form-control form-control-sm" required>
          <option value="">-- Tambah Jabatan ke Jenjang --</option>
          @foreach($positions as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-group col-md-3 mb-0">
        <button type="submit" class="btn btn-sm btn-outline-primary">Tambah Jenjang</button>
      </div>
    </form>
  </div>
</div>
@empty
  <p class="text-muted small">Belum ada career path.</p>
@endforelse
@endsection
