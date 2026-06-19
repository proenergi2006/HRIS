@extends('layouts.grain')
@section('title', 'Tambah Ruangan')

@section('content')
<div class="mb-3">
  <a href="{{ route('ga.admin.rooms.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="gd-angle-left mr-1"></i> Kembali
  </a>
</div>

<div class="card" style="max-width:560px">
  <div class="card-header font-weight-bold">Tambah Ruangan Baru</div>
  <div class="card-body">
    <form method="POST" action="{{ route('ga.admin.rooms.store') }}">
      @csrf
      <div class="form-group">
        <label class="font-weight-bold">Nama Ruangan <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name') }}" placeholder="Contoh: Meeting Room Lt.2" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="form-group">
        <label class="font-weight-bold">Lokasi</label>
        <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
               value="{{ old('location') }}" placeholder="Contoh: Gedung HO Lt.2">
        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="form-group mb-0">
        <div class="custom-control custom-switch">
          <input type="hidden" name="is_active" value="0">
          <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
          <label class="custom-control-label" for="is_active">Ruangan Aktif</label>
        </div>
      </div>
      <hr>
      <button type="submit" class="btn btn-primary">Simpan Ruangan</button>
    </form>
  </div>
</div>
@endsection
