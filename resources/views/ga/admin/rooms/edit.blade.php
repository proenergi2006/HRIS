@extends('layouts.grain')
@section('title', 'Edit Ruangan')

@section('content')
@include('components.notification')

<div class="mb-3">
  <a href="{{ route('ga.admin.rooms.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="gd-angle-left mr-1"></i> Kembali
  </a>
</div>

<div class="row">

  {{-- Edit Ruangan --}}
  <div class="col-md-5">
    <div class="card mb-4">
      <div class="card-header font-weight-bold">Edit Ruangan</div>
      <div class="card-body">
        <form method="POST" action="{{ route('ga.admin.rooms.update', $room) }}">
          @csrf @method('PUT')
          <div class="form-group">
            <label class="font-weight-bold">Nama Ruangan <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $room->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Lokasi</label>
            <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
                   value="{{ old('location', $room->location) }}">
            @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="form-group mb-0">
            <div class="custom-control custom-switch">
              <input type="hidden" name="is_active" value="0">
              <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                     {{ $room->is_active ? 'checked' : '' }}>
              <label class="custom-control-label" for="is_active">Ruangan Aktif</label>
            </div>
          </div>
          <hr>
          <button type="submit" class="btn btn-primary btn-sm">Perbarui</button>
        </form>
      </div>
    </div>
  </div>

  {{-- Daftar Item Kebersihan --}}
  <div class="col-md-7">
    <div class="card">
      <div class="card-header font-weight-bold">Daftar Item Kebersihan</div>
      <div class="card-body">

        {{-- Tambah item baru --}}
        <form method="POST" action="{{ route('ga.admin.rooms.items.store', $room) }}" class="mb-3">
          @csrf
          <div class="input-group">
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   placeholder="Nama item, misal: Membersihkan Lampu" required>
            <div class="input-group-append">
              <button type="submit" class="btn btn-primary">Tambah</button>
            </div>
          </div>
          @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </form>

        @if($items->isEmpty())
          <p class="text-muted small">Belum ada item. Tambahkan di atas.</p>
        @else
          <ul class="list-group">
            @foreach($items as $item)
            <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-2">
              <span class="mr-2 text-muted small">{{ $loop->iteration }}.</span>
              <form method="POST" action="{{ route('ga.admin.rooms.items.update', [$room, $item]) }}"
                    class="d-flex align-items-center flex-grow-1 mr-2">
                @csrf @method('PUT')
                <input type="text" name="name" value="{{ $item->name }}"
                       class="form-control form-control-sm mr-2">
                <button type="submit" class="btn btn-xs btn-outline-warning">
                  <i class="gd-pencil"></i>
                </button>
              </form>
              <form method="POST" action="{{ route('ga.admin.rooms.items.destroy', [$room, $item]) }}">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-xs btn-outline-danger"
                        onclick="return confirm('Hapus item ini?')">
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
