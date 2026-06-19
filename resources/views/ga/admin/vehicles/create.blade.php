@extends('layouts.grain')
@section('title', $vehicle->exists ? 'Edit Kendaraan' : 'Tambah Kendaraan')

@section('content')
@include('components.notification')

<div class="mb-3">
  <a href="{{ route('ga.admin.vehicles.index') }}" class="text-muted small">
    <i class="gd-angle-left"></i> Kembali
  </a>
</div>

<div class="h3 mb-4">{{ $vehicle->exists ? 'Edit Kendaraan' : 'Tambah Kendaraan' }}</div>

<div class="card" style="max-width:600px">
  <div class="card-body">
    <form method="POST"
          action="{{ $vehicle->exists ? route('ga.admin.vehicles.update', $vehicle) : route('ga.admin.vehicles.store') }}">
      @csrf
      @if($vehicle->exists) @method('PUT') @endif

      <div class="form-group">
        <label>Nama Kendaraan <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $vehicle->name) }}" placeholder="Toyota Avanza, Mitsubishi L300, ...">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="form-row">
        <div class="form-group col-md-6">
          <label>Nomor Polisi <span class="text-danger">*</span></label>
          <input type="text" name="plate" class="form-control @error('plate') is-invalid @enderror"
                 value="{{ old('plate', $vehicle->plate) }}" placeholder="B 1234 XYZ"
                 style="text-transform:uppercase">
          @error('plate') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="form-group col-md-6">
          <label>Tipe</label>
          <select name="type" class="form-control">
            <option value="">-- Pilih --</option>
            @foreach(['Sedan','MPV','SUV','Pickup','Bus','Minibus','Van','Truk','Motor'] as $t)
              <option value="{{ $t }}" {{ old('type', $vehicle->type) == $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-6">
          <label>Warna</label>
          <input type="text" name="color" class="form-control"
                 value="{{ old('color', $vehicle->color) }}" placeholder="Putih, Hitam, ...">
        </div>
        <div class="form-group col-md-6">
          <label>Tahun</label>
          <input type="number" name="year" class="form-control"
                 value="{{ old('year', $vehicle->year) }}" placeholder="{{ date('Y') }}"
                 min="1990" max="{{ date('Y') + 1 }}">
        </div>
      </div>

      <div class="form-group">
        <div class="custom-control custom-checkbox">
          <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                 {{ old('is_active', $vehicle->is_active ?? true) ? 'checked' : '' }}>
          <label class="custom-control-label" for="is_active">Kendaraan aktif (bisa dipakai)</label>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary mr-2">
          {{ $vehicle->exists ? 'Simpan Perubahan' : 'Tambah Kendaraan' }}
        </button>
        <a href="{{ route('ga.admin.vehicles.index') }}" class="btn btn-outline-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
@endsection
