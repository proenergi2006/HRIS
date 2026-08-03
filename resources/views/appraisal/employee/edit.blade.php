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

        <form method="POST" enctype="multipart/form-data"
              action="{{ $employee->id ? route('appraisal.employees.update', $employee) : route('appraisal.employees.store') }}">
            @csrf
            @if($employee->id) @method('PUT') @endif

            {{-- ── Header Profil ─────────────────────────────────────── --}}
            <div class="d-flex align-items-center flex-wrap mb-4" style="gap:1.25rem">
                <div class="position-relative" style="width:88px;height:88px;flex:0 0 auto">
                    <div id="photo-preview-wrap" class="rounded-circle d-flex align-items-center justify-content-center overflow-hidden"
                         style="width:88px;height:88px;background:#eef1f5;border:2px solid #e5e7eb;cursor:pointer"
                         onclick="document.getElementById('photo-input').click()">
                        @if($employee->photo)
                            <img id="photo-preview" src="{{ route('appraisal.employees.photo', $employee) }}"
                                 style="width:100%;height:100%;object-fit:cover">
                        @else
                            <img id="photo-preview" style="width:100%;height:100%;object-fit:cover;display:none">
                            <i id="photo-placeholder" class="gd-user" style="font-size:32px;color:#9ca3af"></i>
                        @endif
                    </div>
                    <input type="file" id="photo-input" name="photo" accept="image/*" class="d-none" onchange="previewEmployeePhoto(this)">
                    <span class="badge badge-secondary" style="position:absolute;bottom:0;right:0;font-size:.6rem;pointer-events:none">ubah</span>
                </div>
                <div>
                    <div class="h4 mb-1">{{ $employee->id ? $employee->name : 'Karyawan Baru' }}</div>
                    <div class="d-flex align-items-center flex-wrap" style="gap:.4rem">
                        @if($employee->nip)
                            <span class="badge badge-dark" style="font-size:.75rem;letter-spacing:.04em">{{ $employee->nip }}</span>
                        @endif
                        @if($employee->id)
                            @if($employee->is_active)
                                <span class="badge badge-success">ACTIVE</span>
                            @else
                                <span class="badge badge-secondary">INACTIVE</span>
                            @endif
                        @endif
                        @if($employee->position || $employee->department)
                            <span class="badge badge-info" style="font-size:.75rem">
                                {{ $employee->position->code ?? '-' }} / {{ $employee->department->code ?? '-' }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Tabs ──────────────────────────────────────────────── --}}
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#tab-personal" role="tab">Personal</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#tab-employment" role="tab">Employee Information</a>
                </li>
            </ul>

            <div class="tab-content">

                {{-- ══ TAB 1: PERSONAL ══ --}}
                <div class="tab-pane fade show active" id="tab-personal" role="tabpanel">

                    <h6 class="font-weight-bold text-uppercase text-muted mb-3" style="font-size:.75rem;letter-spacing:.05em">Data Pribadi</h6>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6">
                            <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                                   value="{{ old('name', $employee->name) }}">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-6 col-md-3">
                            <label for="gender">Jenis Kelamin</label>
                            <select id="gender" name="gender" class="form-control{{ $errors->has('gender') ? ' is-invalid' : '' }}">
                                <option value="">-- Pilih --</option>
                                <option value="L" {{ old('gender', $employee->gender) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('gender', $employee->gender) == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-6 col-md-3">
                            <label for="employee_type">Tipe Karyawan <span class="text-danger">*</span></label>
                            <select id="employee_type" name="employee_type" class="form-control{{ $errors->has('employee_type') ? ' is-invalid' : '' }}">
                                <option value="local" {{ old('employee_type', $employee->employee_type ?? 'local') == 'local' ? 'selected' : '' }}>Local</option>
                                <option value="expat" {{ old('employee_type', $employee->employee_type) == 'expat' ? 'selected' : '' }}>Expat</option>
                            </select>
                            @error('employee_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-12 col-md-4">
                            <label for="birth_place">Tempat Lahir</label>
                            <input type="text" id="birth_place" name="birth_place" class="form-control{{ $errors->has('birth_place') ? ' is-invalid' : '' }}"
                                   value="{{ old('birth_place', $employee->birth_place) }}">
                            @error('birth_place')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-12 col-md-4">
                            <label for="birth_date">Tanggal Lahir</label>
                            <input type="date" id="birth_date" name="birth_date" class="form-control{{ $errors->has('birth_date') ? ' is-invalid' : '' }}"
                                   value="{{ old('birth_date', $employee->birth_date?->format('Y-m-d')) }}">
                            @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-12 col-md-4">
                            <label for="finger_id">Finger ID</label>
                            <input type="text" id="finger_id" name="finger_id" class="form-control{{ $errors->has('finger_id') ? ' is-invalid' : '' }}"
                                   value="{{ old('finger_id', $employee->finger_id) }}">
                            @error('finger_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-12 col-md-4">
                            <label for="ktp_number">No. KTP</label>
                            <input type="text" id="ktp_number" name="ktp_number" class="form-control{{ $errors->has('ktp_number') ? ' is-invalid' : '' }}"
                                   value="{{ old('ktp_number', $employee->ktp_number) }}">
                            @error('ktp_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-6 col-md-4">
                            <label for="status_kawin_dummy">Status Kawin</label>
                            <select id="marital_status" name="marital_status" class="form-control{{ $errors->has('marital_status') ? ' is-invalid' : '' }}">
                                <option value="">-- Pilih --</option>
                                <option value="belum_kawin" {{ old('marital_status', $employee->marital_status) == 'belum_kawin' ? 'selected' : '' }}>Belum Kawin</option>
                                <option value="kawin" {{ old('marital_status', $employee->marital_status) == 'kawin' ? 'selected' : '' }}>Kawin</option>
                                <option value="cerai_hidup" {{ old('marital_status', $employee->marital_status) == 'cerai_hidup' ? 'selected' : '' }}>Cerai Hidup</option>
                                <option value="cerai_mati" {{ old('marital_status', $employee->marital_status) == 'cerai_mati' ? 'selected' : '' }}>Cerai Mati</option>
                            </select>
                            @error('marital_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-6 col-md-4">
                            <label for="agama_dummy">Agama</label>
                            <select id="religion" name="religion" class="form-control{{ $errors->has('religion') ? ' is-invalid' : '' }}">
                                <option value="">-- Pilih --</option>
                                @foreach(['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu','Lainnya'] as $r)
                                    <option value="{{ $r }}" {{ old('religion', $employee->religion) == $r ? 'selected' : '' }}>{{ $r }}</option>
                                @endforeach
                            </select>
                            @error('religion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-6 col-md-3">
                            <label for="blood_type">Golongan Darah</label>
                            <select id="blood_type" name="blood_type" class="form-control{{ $errors->has('blood_type') ? ' is-invalid' : '' }}">
                                <option value="">-- Pilih --</option>
                                @foreach(['A','B','AB','O'] as $bt)
                                    <option value="{{ $bt }}" {{ old('blood_type', $employee->blood_type) == $bt ? 'selected' : '' }}>{{ $bt }}</option>
                                @endforeach
                            </select>
                            @error('blood_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-6 col-md-3">
                            <label for="npwp_number">NPWP</label>
                            <input type="text" id="npwp_number" name="npwp_number" class="form-control{{ $errors->has('npwp_number') ? ' is-invalid' : '' }}"
                                   value="{{ old('npwp_number', $employee->npwp_number) }}">
                            @error('npwp_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-6 col-md-3">
                            <label for="npwp_city">Kota NPWP</label>
                            <input type="text" id="npwp_city" name="npwp_city" class="form-control{{ $errors->has('npwp_city') ? ' is-invalid' : '' }}"
                                   value="{{ old('npwp_city', $employee->npwp_city) }}">
                            @error('npwp_city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-6 col-md-3">
                            <label for="npwp_date">Tanggal NPWP</label>
                            <input type="date" id="npwp_date" name="npwp_date" class="form-control{{ $errors->has('npwp_date') ? ' is-invalid' : '' }}"
                                   value="{{ old('npwp_date', $employee->npwp_date?->format('Y-m-d')) }}">
                            @error('npwp_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <hr>
                    <h6 class="font-weight-bold text-uppercase text-muted mb-3" style="font-size:.75rem;letter-spacing:.05em">Email &amp; Telepon</h6>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-4">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                   value="{{ old('email', $employee->email) }}">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-6 col-md-4">
                            <label for="phone">No. HP</label>
                            <input type="text" id="phone" name="phone" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}"
                                   value="{{ old('phone', $employee->phone) }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-6 col-md-4">
                            <label for="home_phone">No. Telp Rumah</label>
                            <input type="text" id="home_phone" name="home_phone" class="form-control{{ $errors->has('home_phone') ? ' is-invalid' : '' }}"
                                   value="{{ old('home_phone', $employee->home_phone) }}">
                            @error('home_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <hr>
                    <h6 class="font-weight-bold text-uppercase text-muted mb-3" style="font-size:.75rem;letter-spacing:.05em">Alamat Domisili</h6>
                    <div class="form-row">
                        <div class="form-group col-12">
                            <label for="domicile_address">Alamat</label>
                            <textarea id="domicile_address" name="domicile_address" rows="2" class="form-control{{ $errors->has('domicile_address') ? ' is-invalid' : '' }}">{{ old('domicile_address', $employee->domicile_address) }}</textarea>
                            @error('domicile_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-4">
                            <label for="domicile_city">Kota</label>
                            <input type="text" id="domicile_city" name="domicile_city" class="form-control{{ $errors->has('domicile_city') ? ' is-invalid' : '' }}"
                                   value="{{ old('domicile_city', $employee->domicile_city) }}">
                            @error('domicile_city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-6 col-md-4">
                            <label for="domicile_district">Kecamatan</label>
                            <input type="text" id="domicile_district" name="domicile_district" class="form-control{{ $errors->has('domicile_district') ? ' is-invalid' : '' }}"
                                   value="{{ old('domicile_district', $employee->domicile_district) }}">
                            @error('domicile_district')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-6 col-md-4">
                            <label for="domicile_subdistrict">Kelurahan</label>
                            <input type="text" id="domicile_subdistrict" name="domicile_subdistrict" class="form-control{{ $errors->has('domicile_subdistrict') ? ' is-invalid' : '' }}"
                                   value="{{ old('domicile_subdistrict', $employee->domicile_subdistrict) }}">
                            @error('domicile_subdistrict')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="font-weight-bold text-uppercase text-muted mb-0" style="font-size:.75rem;letter-spacing:.05em">Alamat KTP</h6>
                        <button type="button" class="btn btn-xs btn-outline-secondary" onclick="copyDomicileToKtp()">Salin dari Domisili</button>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12">
                            <label for="ktp_address">Alamat</label>
                            <textarea id="ktp_address" name="ktp_address" rows="2" class="form-control{{ $errors->has('ktp_address') ? ' is-invalid' : '' }}">{{ old('ktp_address', $employee->ktp_address) }}</textarea>
                            @error('ktp_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-4">
                            <label for="ktp_city">Kota</label>
                            <input type="text" id="ktp_city" name="ktp_city" class="form-control{{ $errors->has('ktp_city') ? ' is-invalid' : '' }}"
                                   value="{{ old('ktp_city', $employee->ktp_city) }}">
                            @error('ktp_city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-6 col-md-4">
                            <label for="ktp_district">Kecamatan</label>
                            <input type="text" id="ktp_district" name="ktp_district" class="form-control{{ $errors->has('ktp_district') ? ' is-invalid' : '' }}"
                                   value="{{ old('ktp_district', $employee->ktp_district) }}">
                            @error('ktp_district')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-6 col-md-4">
                            <label for="ktp_subdistrict">Kelurahan</label>
                            <input type="text" id="ktp_subdistrict" name="ktp_subdistrict" class="form-control{{ $errors->has('ktp_subdistrict') ? ' is-invalid' : '' }}"
                                   value="{{ old('ktp_subdistrict', $employee->ktp_subdistrict) }}">
                            @error('ktp_subdistrict')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <hr>
                    <h6 class="font-weight-bold text-uppercase text-muted mb-3" style="font-size:.75rem;letter-spacing:.05em">Kontak Darurat</h6>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-4">
                            <label for="emergency_contact_name">Nama</label>
                            <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="form-control{{ $errors->has('emergency_contact_name') ? ' is-invalid' : '' }}"
                                   value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}">
                            @error('emergency_contact_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-6 col-md-4">
                            <label for="emergency_contact_relation">Hubungan</label>
                            <input type="text" id="emergency_contact_relation" name="emergency_contact_relation" class="form-control{{ $errors->has('emergency_contact_relation') ? ' is-invalid' : '' }}"
                                   value="{{ old('emergency_contact_relation', $employee->emergency_contact_relation) }}" placeholder="Suami/Istri, Orang Tua, ...">
                            @error('emergency_contact_relation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-6 col-md-4">
                            <label for="emergency_contact_phone">No. Telp</label>
                            <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" class="form-control{{ $errors->has('emergency_contact_phone') ? ' is-invalid' : '' }}"
                                   value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}">
                            @error('emergency_contact_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- ══ TAB 2: EMPLOYEE INFORMATION ══ --}}
                <div class="tab-pane fade" id="tab-employment" role="tabpanel">

                    <div class="form-row">
                        <div class="form-group col-12 col-md-6">
                            <label for="company_id">Perusahaan <span class="text-danger">*</span></label>
                            <select id="company_id" name="company_id" class="form-control{{ $errors->has('company_id') ? ' is-invalid' : '' }}">
                                <option value="">-- Pilih Perusahaan --</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id', $employee->company_id) == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-12 col-md-6">
                            <label for="nip">NIP</label>
                            <input type="text" id="nip" name="nip" class="form-control{{ $errors->has('nip') ? ' is-invalid' : '' }}"
                                   value="{{ old('nip', $employee->nip) }}">
                            @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-12 col-md-6">
                            <label for="branch">Cabang</label>
                            <input type="text" id="branch" name="branch" class="form-control{{ $errors->has('branch') ? ' is-invalid' : '' }}"
                                   value="{{ old('branch', $employee->branch ?? 'HO') }}" placeholder="HO">
                            @error('branch')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-12 col-md-6">
                            <label for="department_id">Departemen</label>
                            <select id="department_id" name="department_id" class="form-control{{ $errors->has('department_id') ? ' is-invalid' : '' }}">
                                <option value="">-- Pilih Departemen --</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}" {{ old('department_id', $employee->department_id) == $d->id ? 'selected' : '' }}>
                                        [{{ $d->code }}] {{ $d->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Belum ada di daftar? <a href="{{ route('appraisal.departments.index') }}" target="_blank">Kelola Departemen</a></small>
                            @error('department_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-12 col-md-6">
                            <label for="lob">LOB (Line of Business)</label>
                            <input type="text" id="lob" name="lob" class="form-control{{ $errors->has('lob') ? ' is-invalid' : '' }}"
                                   value="{{ old('lob', $employee->lob) }}">
                            @error('lob')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-12 col-md-6">
                            <label for="position_id">Jabatan</label>
                            <select id="position_id" name="position_id" class="form-control{{ $errors->has('position_id') ? ' is-invalid' : '' }}">
                                <option value="">-- Pilih Jabatan --</option>
                                @foreach($positions as $p)
                                    <option value="{{ $p->id }}" {{ old('position_id', $employee->position_id) == $p->id ? 'selected' : '' }}>
                                        [{{ $p->code }}] {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Belum ada di daftar? <a href="{{ route('appraisal.positions.index') }}" target="_blank">Kelola Jabatan</a></small>
                            @error('position_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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

                    <div class="form-row" id="contract-end-row" style="{{ old('employment_status', $employee->employment_status) === 'contract' ? '' : 'display:none;' }}">
                        <div class="form-group col-12 col-md-4">
                            <label for="contract_end_date">Tanggal Kontrak Berakhir <span class="text-danger">*</span></label>
                            <input type="date" id="contract_end_date" name="contract_end_date"
                                   class="form-control{{ $errors->has('contract_end_date') ? ' is-invalid' : '' }}"
                                   value="{{ old('contract_end_date', $employee->contract_end_date?->format('Y-m-d')) }}">
                            @error('contract_end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Wajib diisi jika status karyawan adalah Kontrak.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('appraisal.employees.index') }}" class="btn btn-secondary">Batal</a>
                <div>
                    @if($employee->id)
                    <a href="{{ route('appraisal.employees.documents.index', $employee) }}"
                       class="btn btn-outline-info mr-2">
                        <i class="gd-file icon-text"></i> Dokumen
                    </a>
                    @endif
                    <button type="submit" class="btn btn-primary">{{ $employee->id ? 'Simpan Perubahan' : 'Tambah Karyawan' }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    var statusEl = document.getElementById('employment_status');
    var contractRow = document.getElementById('contract-end-row');
    var contractInput = document.getElementById('contract_end_date');

    function toggle() {
        var isContract = statusEl.value === 'contract';
        contractRow.style.display = isContract ? '' : 'none';
        contractInput.required = isContract;
        if (!isContract) contractInput.value = '';
    }

    statusEl.addEventListener('change', toggle);
})();

function previewEmployeePhoto(input) {
    var file = input.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var img = document.getElementById('photo-preview');
        var placeholder = document.getElementById('photo-placeholder');
        img.src = e.target.result;
        img.style.display = 'block';
        if (placeholder) placeholder.style.display = 'none';
    };
    reader.readAsDataURL(file);
}

function copyDomicileToKtp() {
    document.getElementById('ktp_address').value = document.getElementById('domicile_address').value;
    document.getElementById('ktp_city').value = document.getElementById('domicile_city').value;
    document.getElementById('ktp_district').value = document.getElementById('domicile_district').value;
    document.getElementById('ktp_subdistrict').value = document.getElementById('domicile_subdistrict').value;
}
</script>
@endsection
