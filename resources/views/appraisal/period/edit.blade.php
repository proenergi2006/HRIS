@extends('layouts.grain')
@section('title', $period->id ? 'Edit Periode' : 'Tambah Periode')

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
    <div class="card-body">
        <nav class="d-none d-md-block" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('appraisal.periods.index') }}">Periode Penilaian</a></li>
                <li class="breadcrumb-item active">{{ $period->id ? 'Edit' : 'Tambah' }}</li>
            </ol>
        </nav>

        <div class="mb-3 mb-md-4">
            <div class="h3 mb-0">{{ $period->id ? 'Edit Periode: ' . $period->name : 'Tambah Periode Penilaian' }}</div>
        </div>

        <form method="POST" action="{{ $period->id ? route('appraisal.periods.update', $period) : route('appraisal.periods.store') }}">
            @csrf
            @if($period->id) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label for="name">Nama Periode <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name"
                           class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                           value="{{ old('name', $period->name) }}"
                           placeholder="cth: Penilaian Kinerja Semester 1 2025">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-6 col-md-2">
                    <label for="year">Tahun <span class="text-danger">*</span></label>
                    <input type="number" id="year" name="year"
                           class="form-control{{ $errors->has('year') ? ' is-invalid' : '' }}"
                           value="{{ old('year', $period->year ?? date('Y')) }}"
                           min="2000" max="2100">
                    @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-6 col-md-4">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="open"   {{ old('status', $period->status ?? 'open') === 'open'   ? 'selected' : '' }}>Buka</option>
                        <option value="closed" {{ old('status', $period->status ?? 'open') === 'closed' ? 'selected' : '' }}>Tutup</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label for="start_date">Tanggal Mulai</label>
                    <input type="date" id="start_date" name="start_date"
                           class="form-control{{ $errors->has('start_date') ? ' is-invalid' : '' }}"
                           value="{{ old('start_date', $period->start_date?->format('Y-m-d')) }}">
                    @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-12 col-md-6">
                    <label for="end_date">Tanggal Selesai</label>
                    <input type="date" id="end_date" name="end_date"
                           class="form-control{{ $errors->has('end_date') ? ' is-invalid' : '' }}"
                           value="{{ old('end_date', $period->end_date?->format('Y-m-d')) }}">
                    @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex justify-content-between mt-2">
                <a href="{{ route('appraisal.periods.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">{{ $period->id ? 'Simpan Perubahan' : 'Buat Periode' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
