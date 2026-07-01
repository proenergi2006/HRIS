@extends('layouts.grain')
@section('title', $employee->id ? 'Edit Karyawan' : 'Tambah Karyawan')

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
    <div class="card-body">
        <nav class="d-none d-md-block" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('appraisal.employees.index') }}">Data Karyawan</a></li>
                <li class="breadcrumb-item active">{{ $employee->id ? 'Edit' : 'Tambah' }}</li>
            </ol>
        </nav>

        <div class="mb-3 mb-md-4">
            <div class="h3 mb-0">{{ $employee->id ? 'Edit Karyawan: ' . $employee->name : 'Tambah Karyawan' }}</div>
        </div>

        <form method="POST" action="{{ $employee->id ? route('appraisal.employees.update', $employee) : route('appraisal.employees.store') }}">
            @csrf
            @if($employee->id) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name"
                           class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                           value="{{ old('name', $employee->name) }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-12 col-md-6">
                    <label for="nip">NIP</label>
                    <input type="text" id="nip" name="nip"
                           class="form-control{{ $errors->has('nip') ? ' is-invalid' : '' }}"
                           value="{{ old('nip', $employee->nip) }}">
                    @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label for="department">Departemen</label>
                    <input type="text" id="department" name="department"
                           class="form-control{{ $errors->has('department') ? ' is-invalid' : '' }}"
                           value="{{ old('department', $employee->department) }}">
                    @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-12 col-md-6">
                    <label for="lob">LOB (Line of Business)</label>
                    <input type="text" id="lob" name="lob"
                           class="form-control{{ $errors->has('lob') ? ' is-invalid' : '' }}"
                           value="{{ old('lob', $employee->lob) }}">
                    @error('lob')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label for="position">Jabatan</label>
                    <input type="text" id="position" name="position"
                           class="form-control{{ $errors->has('position') ? ' is-invalid' : '' }}"
                           value="{{ old('position', $employee->position) }}">
                    @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-12 col-md-6">
                    <label for="level_id">Level Jabatan</label>
                    <select id="level_id" name="level_id" class="form-control{{ $errors->has('level_id') ? ' is-invalid' : '' }}">
                        <option value="">-- Pilih Level --</option>
                        @foreach($levels as $level)
                            <option value="{{ $level->id }}" {{ old('level_id', $employee->level_id) == $level->id ? 'selected' : '' }}>
                                {{ $level->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('level_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label for="manager_id">Atasan Langsung</label>
                    <select id="manager_id" name="manager_id" class="form-control{{ $errors->has('manager_id') ? ' is-invalid' : '' }}">
                        <option value="">-- Tidak ada / langsung ke HR --</option>
                        @foreach($managers as $mgr)
                            <option value="{{ $mgr->id }}" {{ old('manager_id', $employee->manager_id) == $mgr->id ? 'selected' : '' }}>
                                {{ $mgr->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Digunakan untuk alur persetujuan perjalanan dinas.</small>
                    @error('manager_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-12 col-md-4">
                    <label for="start_date">Tanggal Mulai Kerja</label>
                    <input type="date" id="start_date" name="start_date"
                           class="form-control{{ $errors->has('start_date') ? ' is-invalid' : '' }}"
                           value="{{ old('start_date', $employee->start_date?->format('Y-m-d')) }}">
                    @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-12 col-md-4">
                    <label for="employment_status">Status Kontrak <span class="text-danger">*</span></label>
                    <select id="employment_status" name="employment_status" class="form-control{{ $errors->has('employment_status') ? ' is-invalid' : '' }}">
                        <option value="permanent"  {{ old('employment_status', $employee->employment_status) == 'permanent'  ? 'selected' : '' }}>Tetap</option>
                        <option value="contract"   {{ old('employment_status', $employee->employment_status) == 'contract'   ? 'selected' : '' }}>Kontrak</option>
                        <option value="probation"  {{ old('employment_status', $employee->employment_status) == 'probation'  ? 'selected' : '' }}>Probation</option>
                    </select>
                    @error('employment_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-12 col-md-4 d-flex align-items-end">
                    <div class="form-check">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input"
                               {{ old('is_active', $employee->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Karyawan Aktif</label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-2">
                <a href="{{ route('appraisal.employees.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">{{ $employee->id ? 'Simpan Perubahan' : 'Tambah Karyawan' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
