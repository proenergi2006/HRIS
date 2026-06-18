@extends('layouts.grain')
@section('title', $config->id ? 'Edit Konfigurasi Approval' : 'Tambah Konfigurasi Approval')

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
    <div class="card-body">
        <nav class="d-none d-md-block" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('appraisal.flow-configs.index') }}">Alur Approval</a></li>
                <li class="breadcrumb-item active">{{ $config->id ? 'Edit' : 'Tambah' }}</li>
            </ol>
        </nav>

        <div class="mb-3 mb-md-4">
            <div class="h3 mb-0">{{ $config->id ? 'Edit Konfigurasi Approval' : 'Tambah Konfigurasi Approval' }}</div>
        </div>

        <form method="POST"
              action="{{ $config->id ? route('appraisal.flow-configs.update', $config) : route('appraisal.flow-configs.store') }}">
            @csrf
            @if($config->id) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group col-12 col-md-4">
                    <label for="department">
                        Departemen
                        <small class="text-muted">(kosongkan = default semua dept)</small>
                    </label>
                    <input type="text" id="department" name="department"
                           class="form-control{{ $errors->has('department') ? ' is-invalid' : '' }}"
                           value="{{ old('department', $config->department) }}"
                           placeholder="cth: IT, Finance, HR">
                    @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group col-6 col-md-2">
                    <label for="step">Step Approval <span class="text-danger">*</span></label>
                    <select id="step" name="step"
                            class="form-control{{ $errors->has('step') ? ' is-invalid' : '' }}">
                        <option value="1" {{ old('step', $config->step) == 1 ? 'selected' : '' }}>
                            Step 1 (Atasan Langsung)
                        </option>
                        <option value="2" {{ old('step', $config->step) == 2 ? 'selected' : '' }}>
                            Step 2 (Persetujuan Final)
                        </option>
                    </select>
                    @error('step')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group col-12 col-md-3">
                    <label for="role">Role Approver <span class="text-danger">*</span></label>
                    <select id="role" name="role"
                            class="form-control{{ $errors->has('role') ? ' is-invalid' : '' }}">
                        <option value="">-- Pilih Role --</option>
                        @foreach($roles as $r)
                            <option value="{{ $r }}" {{ old('role', $config->role) === $r ? 'selected' : '' }}>
                                {{ $r }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group col-12 col-md-3">
                    <label for="label">Label Tampil <span class="text-danger">*</span></label>
                    <input type="text" id="label" name="label"
                           class="form-control{{ $errors->has('label') ? ' is-invalid' : '' }}"
                           value="{{ old('label', $config->label) }}"
                           placeholder="cth: CFO, CEO, Direktur">
                    @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex justify-content-between mt-2">
                <a href="{{ route('appraisal.flow-configs.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    {{ $config->id ? 'Simpan Perubahan' : 'Tambah Konfigurasi' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
