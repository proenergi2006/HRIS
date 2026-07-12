@extends('layouts.grain')
@section('title', 'Kategori Dokumen Brankas')

@section('content')
@include('components.notification')

<div class="mb-3">
  <a href="{{ route('ga.admin.vault-documents.index') }}" class="text-muted small">
    <i class="gd-angle-left"></i> Kembali
  </a>
</div>

<div class="h3 mb-4">Kategori Dokumen Brankas</div>

<div class="row">
  <div class="col-md-7">
    <div class="card">
      <div class="card-header font-weight-bold">Daftar Kategori</div>
      <div class="card-body">

        {{-- Tambah kategori baru --}}
        <form method="POST" action="{{ route('ga.admin.vault-categories.store') }}" class="mb-3">
          @csrf
          <div class="input-group">
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   placeholder="Nama kategori, misal: Legal, Keuangan, Aset" required>
            <div class="input-group-append">
              <button type="submit" class="btn btn-primary">Tambah</button>
            </div>
          </div>
          @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </form>

        @if($categories->isEmpty())
          <p class="text-muted small">Belum ada kategori. Tambahkan di atas.</p>
        @else
          <ul class="list-group">
            @foreach($categories as $category)
            <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-2">
              <form method="POST" action="{{ route('ga.admin.vault-categories.update', $category) }}"
                    class="d-flex align-items-center flex-grow-1 mr-2">
                @csrf @method('PUT')
                <input type="text" name="name" value="{{ $category->name }}"
                       class="form-control form-control-sm mr-2">
                <div class="custom-control custom-switch mr-2" style="white-space:nowrap">
                  <input type="hidden" name="is_active" value="0">
                  <input type="checkbox" class="custom-control-input" id="active-{{ $category->id }}"
                         name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }}>
                  <label class="custom-control-label small" for="active-{{ $category->id }}">Aktif</label>
                </div>
                <button type="submit" class="btn btn-xs btn-outline-warning">
                  <i class="gd-pencil"></i>
                </button>
              </form>
              <form method="POST" action="{{ route('ga.admin.vault-categories.destroy', $category) }}">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-xs btn-outline-danger"
                        onclick="return confirm('Hapus kategori {{ $category->name }}?')"
                        {{ $category->documents_count > 0 ? 'disabled title=Masih dipakai dokumen' : '' }}>
                  <i class="gd-trash"></i>
                </button>
              </form>
            </li>
            @endforeach
          </ul>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
