@extends('layouts.grain')
@section('title', $vault->exists ? 'Edit Berangkas' : 'Tambah Berangkas')

@section('content')
@include('components.notification')

<div class="mb-3">
  <a href="{{ route('ga.admin.vaults.index') }}" class="text-muted small">
    <i class="gd-angle-left"></i> Kembali
  </a>
</div>

<div class="h3 mb-4">{{ $vault->exists ? 'Edit Berangkas' : 'Tambah Berangkas' }}</div>

<div class="card" style="max-width:600px">
  <div class="card-body">
    <form method="POST"
          action="{{ $vault->exists ? route('ga.admin.vaults.update', $vault) : route('ga.admin.vaults.store') }}">
      @csrf
      @if($vault->exists) @method('PUT') @endif

      @if($vault->exists)
      <div class="form-group">
        <label>Barcode</label>
        <input type="text" class="form-control" value="{{ $vault->barcode }}" disabled>
      </div>
      @endif

      <div class="form-group">
        <label>Nama Berangkas <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               placeholder="Contoh: Berangkas 1, Berangkas Lantai 2" value="{{ old('name', $vault->name) }}">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="form-group">
        <div class="custom-control custom-checkbox">
          <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                 {{ old('is_active', $vault->is_active ?? true) ? 'checked' : '' }}>
          <label class="custom-control-label" for="is_active">Berangkas aktif</label>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary mr-2">
          {{ $vault->exists ? 'Simpan Perubahan' : 'Tambah Berangkas' }}
        </button>
        <a href="{{ route('ga.admin.vaults.index') }}" class="btn btn-outline-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
@endsection
