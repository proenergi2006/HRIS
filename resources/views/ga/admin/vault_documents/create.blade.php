@extends('layouts.grain')
@section('title', $document->exists ? 'Edit Dokumen Brankas' : 'Tambah Dokumen Brankas')

@section('content')
@include('components.notification')

<div class="mb-3">
  <a href="{{ route('ga.admin.vault-documents.index') }}" class="text-muted small">
    <i class="gd-angle-left"></i> Kembali
  </a>
</div>

<div class="h3 mb-4">{{ $document->exists ? 'Edit Dokumen Brankas' : 'Tambah Dokumen Brankas' }}</div>

<div class="card" style="max-width:600px">
  <div class="card-body">
    <form method="POST"
          action="{{ $document->exists ? route('ga.admin.vault-documents.update', $document) : route('ga.admin.vault-documents.store') }}">
      @csrf
      @if($document->exists) @method('PUT') @endif

      @if($document->exists)
      <div class="form-group">
        <label>Barcode</label>
        <input type="text" class="form-control" value="{{ $document->barcode }}" disabled>
      </div>
      @endif

      <div class="form-group">
        <label>Kategori Dokumen <span class="text-danger">*</span></label>
        <select name="category_id" class="form-control @error('category_id') is-invalid @enderror">
          <option value="">-- Pilih Kategori --</option>
          @foreach($categories as $c)
            <option value="{{ $c->id }}" {{ old('category_id', $document->category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
          @endforeach
        </select>
        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if($categories->isEmpty())
          <small class="text-muted">Belum ada kategori. <a href="{{ route('ga.admin.vault-categories.index') }}">Tambah kategori dulu</a>.</small>
        @endif
      </div>

      <div class="form-group">
        <label>Detail Dokumen <span class="text-danger">*</span></label>
        <textarea name="detail" rows="4" class="form-control @error('detail') is-invalid @enderror"
                  placeholder="Contoh: Sertifikat Tanah Blok A No. 123, an. PT Pro Energi">{{ old('detail', $document->detail) }}</textarea>
        @error('detail') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="form-group">
        <div class="custom-control custom-checkbox">
          <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                 {{ old('is_active', $document->is_active ?? true) ? 'checked' : '' }}>
          <label class="custom-control-label" for="is_active">Dokumen aktif</label>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary mr-2">
          {{ $document->exists ? 'Simpan Perubahan' : 'Tambah Dokumen' }}
        </button>
        <a href="{{ route('ga.admin.vault-documents.index') }}" class="btn btn-outline-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
@endsection
