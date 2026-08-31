@extends('layouts.grain')
@section('title', $announcement->exists ? 'Edit Pengumuman' : 'Pengumuman Baru')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('announcements.manage') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>
<div class="h3 mb-4">{{ $announcement->exists ? 'Edit Pengumuman' : 'Pengumuman Baru' }}</div>

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ $announcement->exists ? route('announcements.update', $announcement) : route('announcements.store') }}" enctype="multipart/form-data">
      @csrf
      @if($announcement->exists) @method('PUT') @endif

      <div class="form-row">
        <div class="form-group col-md-6">
          <label>Judul <span class="text-danger">*</span></label>
          <input type="text" name="title" class="form-control" value="{{ old('title', $announcement->title) }}" required>
        </div>
        <div class="form-group col-md-3">
          <label>Kategori <span class="text-danger">*</span></label>
          <select name="category" class="form-control" required>
            @foreach(\App\Models\Announcement::$categoryLabels as $k => $v)<option value="{{ $k }}" @selected(old('category', $announcement->category) === $k)>{{ $v }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Perusahaan</label>
          <select name="company_id" class="form-control">
            <option value="">Semua PT</option>
            @foreach($companies as $c)<option value="{{ $c->id }}" @selected(old('company_id', $announcement->company_id) == $c->id)>{{ $c->short_name ?? $c->name }}</option>@endforeach
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>Isi <span class="text-danger">*</span></label>
        <textarea name="body" rows="8" class="form-control" required>{{ old('body', $announcement->body) }}</textarea>
      </div>

      <div class="form-row">
        <div class="form-group col-md-4">
          <label>Terbit Mulai</label>
          <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', $announcement->published_at?->format('Y-m-d\TH:i')) }}">
        </div>
        <div class="form-group col-md-4">
          <label>Berlaku Sampai (opsional)</label>
          <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at', $announcement->expires_at?->format('Y-m-d')) }}">
        </div>
        <div class="form-group col-md-4">
          <label>Lampiran</label>
          <input type="file" name="attachment" class="form-control-file">
          @if($announcement->attachment_name)<div class="small text-muted">Saat ini: {{ $announcement->attachment_name }}</div>@endif
        </div>
      </div>

      <div class="custom-control custom-checkbox mb-3">
        <input type="checkbox" class="custom-control-input" id="pin" name="is_pinned" value="1" @checked(old('is_pinned', $announcement->is_pinned))>
        <label class="custom-control-label" for="pin">Sematkan di atas (pinned)</label>
      </div>

      <div class="d-flex justify-content-between">
        <a href="{{ route('announcements.manage') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection
