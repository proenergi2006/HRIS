@extends('layouts.grain')
@section('title', $level->id ? 'Edit Level' : 'Tambah Level')

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
    <div class="card-body">
        <nav class="d-none d-md-block" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('appraisal.levels.index') }}">Level Jabatan</a></li>
                <li class="breadcrumb-item active">{{ $level->id ? 'Edit' : 'Tambah' }}</li>
            </ol>
        </nav>

        <div class="mb-3 mb-md-4">
            <div class="h3 mb-0">{{ $level->id ? 'Edit Level: ' . $level->name : 'Tambah Level Jabatan' }}</div>
        </div>

        <form method="POST" action="{{ $level->id ? route('appraisal.levels.update', $level) : route('appraisal.levels.store') }}">
            @csrf
            @if($level->id) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label for="name">Nama Level <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name"
                           class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                           value="{{ old('name', $level->name) }}" placeholder="cth: SPV, Manager">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-12 col-md-6">
                    <label for="description">Deskripsi</label>
                    <input type="text" id="description" name="description"
                           class="form-control{{ $errors->has('description') ? ' is-invalid' : '' }}"
                           value="{{ old('description', $level->description) }}" placeholder="Opsional">
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex justify-content-between mt-2">
                <a href="{{ route('appraisal.levels.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">{{ $level->id ? 'Simpan Perubahan' : 'Tambah Level' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
