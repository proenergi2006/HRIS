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
                            <label for="employee_type_id">Tipe Karyawan</label>
                            <select id="employee_type_id" name="employee_type_id" class="form-control{{ $errors->has('employee_type_id') ? ' is-invalid' : '' }}">
                                <option value="">-- Pilih --</option>
                                @foreach($employeeTypes as $et)
                                    <option value="{{ $et->id }}" {{ (int) old('employee_type_id', $employee->employee_type_id) === $et->id ? 'selected' : '' }}>{{ $et->name }}</option>
                                @endforeach
                            </select>
                            @error('employee_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-6 col-md-3">
                            <label for="employee_type">Status Kewarganegaraan <span class="text-danger">*</span></label>
                            <select id="employee_type" name="employee_type" class="form-control{{ $errors->has('employee_type') ? ' is-invalid' : '' }}">
                                <option value="local" {{ old('employee_type', $employee->employee_type ?? 'local') == 'local' ? 'selected' : '' }}>Local / WNI</option>
                                <option value="expat" {{ old('employee_type', $employee->employee_type) == 'expat' ? 'selected' : '' }}>Expat / WNA</option>
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
                            <label for="marital_status_id">Status Kawin</label>
                            <select id="marital_status_id" name="marital_status_id" class="form-control{{ $errors->has('marital_status_id') ? ' is-invalid' : '' }}">
                                <option value="">-- Pilih --</option>
                                @foreach($maritalStatuses as $ms)
                                    <option value="{{ $ms->id }}" {{ (int) old('marital_status_id', $employee->marital_status_id) === $ms->id ? 'selected' : '' }}>{{ $ms->name }}</option>
                                @endforeach
                            </select>
                            @error('marital_status_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-6 col-md-4">
                            <label for="religion_id">Agama</label>
                            <select id="religion_id" name="religion_id" class="form-control{{ $errors->has('religion_id') ? ' is-invalid' : '' }}">
                                <option value="">-- Pilih --</option>
                                @foreach($religions as $r)
                                    <option value="{{ $r->id }}" {{ (int) old('religion_id', $employee->religion_id) === $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                                @endforeach
                            </select>
                            @error('religion_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-6 col-md-3">
                            <label for="blood_type_id">Golongan Darah</label>
                            <select id="blood_type_id" name="blood_type_id" class="form-control{{ $errors->has('blood_type_id') ? ' is-invalid' : '' }}">
                                <option value="">-- Pilih --</option>
                                @foreach($bloodTypes as $bt)
                                    <option value="{{ $bt->id }}" {{ (int) old('blood_type_id', $employee->blood_type_id) === $bt->id ? 'selected' : '' }}>{{ $bt->name }}</option>
                                @endforeach
                            </select>
                            @error('blood_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                        <div class="form-group col-6 col-md-3">
                            <label for="domicile_province_id">Provinsi</label>
                            <select id="domicile_province_id" name="domicile_province_id" class="form-control region-province" data-city-target="domicile_city_id">
                                <option value="">-- Pilih --</option>
                                @foreach($provinces as $prov)
                                    <option value="{{ $prov->id }}" {{ (int) old('domicile_province_id', $employee->domicile_province_id) === $prov->id ? 'selected' : '' }}>{{ $prov->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-6 col-md-3">
                            <label for="domicile_city_id">Kota / Kabupaten</label>
                            <select id="domicile_city_id" name="domicile_city_id" class="form-control{{ $errors->has('domicile_city_id') ? ' is-invalid' : '' }}"
                                    data-selected="{{ old('domicile_city_id', $employee->domicile_city_id) }}">
                                <option value="">-- Pilih Provinsi dulu --</option>
                            </select>
                            @error('domicile_city_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-6 col-md-3">
                            <label for="domicile_district_id">Kecamatan</label>
                            <select id="domicile_district_id" name="domicile_district_id" class="form-control region-district"
                                    data-city-source="domicile_city_id" data-village-target="domicile_village_id"
                                    data-selected="{{ old('domicile_district_id', $employee->domicile_district_id) }}">
                                <option value="">-- Pilih Kota dulu --</option>
                            </select>
                            <input type="text" name="domicile_district" class="form-control form-control-sm mt-1 region-manual"
                                   placeholder="atau ketik manual" value="{{ old('domicile_district', $employee->domicile_district) }}">
                        </div>
                        <div class="form-group col-6 col-md-3">
                            <label for="domicile_village_id">Kelurahan</label>
                            <select id="domicile_village_id" name="domicile_village_id" class="form-control"
                                    data-selected="{{ old('domicile_village_id', $employee->domicile_village_id) }}">
                                <option value="">-- Pilih Kecamatan dulu --</option>
                            </select>
                            <input type="text" name="domicile_subdistrict" class="form-control form-control-sm mt-1 region-manual"
                                   placeholder="atau ketik manual" value="{{ old('domicile_subdistrict', $employee->domicile_subdistrict) }}">
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
                        <div class="form-group col-6 col-md-3">
                            <label for="ktp_province_id">Provinsi</label>
                            <select id="ktp_province_id" name="ktp_province_id" class="form-control region-province" data-city-target="ktp_city_id">
                                <option value="">-- Pilih --</option>
                                @foreach($provinces as $prov)
                                    <option value="{{ $prov->id }}" {{ (int) old('ktp_province_id', $employee->ktp_province_id) === $prov->id ? 'selected' : '' }}>{{ $prov->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-6 col-md-3">
                            <label for="ktp_city_id">Kota / Kabupaten</label>
                            <select id="ktp_city_id" name="ktp_city_id" class="form-control{{ $errors->has('ktp_city_id') ? ' is-invalid' : '' }}"
                                    data-selected="{{ old('ktp_city_id', $employee->ktp_city_id) }}">
                                <option value="">-- Pilih Provinsi dulu --</option>
                            </select>
                            @error('ktp_city_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-6 col-md-3">
                            <label for="ktp_district_id">Kecamatan</label>
                            <select id="ktp_district_id" name="ktp_district_id" class="form-control region-district"
                                    data-city-source="ktp_city_id" data-village-target="ktp_village_id"
                                    data-selected="{{ old('ktp_district_id', $employee->ktp_district_id) }}">
                                <option value="">-- Pilih Kota dulu --</option>
                            </select>
                            <input type="text" name="ktp_district" class="form-control form-control-sm mt-1 region-manual"
                                   placeholder="atau ketik manual" value="{{ old('ktp_district', $employee->ktp_district) }}">
                        </div>
                        <div class="form-group col-6 col-md-3">
                            <label for="ktp_village_id">Kelurahan</label>
                            <select id="ktp_village_id" name="ktp_village_id" class="form-control"
                                    data-selected="{{ old('ktp_village_id', $employee->ktp_village_id) }}">
                                <option value="">-- Pilih Kecamatan dulu --</option>
                            </select>
                            <input type="text" name="ktp_subdistrict" class="form-control form-control-sm mt-1 region-manual"
                                   placeholder="atau ketik manual" value="{{ old('ktp_subdistrict', $employee->ktp_subdistrict) }}">
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
                            <label for="branch_id">Cabang</label>
                            <select id="branch_id" name="branch_id" class="form-control{{ $errors->has('branch_id') ? ' is-invalid' : '' }}">
                                <option value="">-- Pilih Cabang --</option>
                                @foreach($branches as $br)
                                    <option value="{{ $br->id }}" data-company="{{ $br->company_id }}" {{ (int) old('branch_id', $employee->branch_id) === $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                                @endforeach
                            </select>
                            @error('branch_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-12 col-md-6">
                            <label for="division_id">Divisi</label>
                            <select id="division_id" name="division_id" class="form-control{{ $errors->has('division_id') ? ' is-invalid' : '' }}">
                                <option value="">-- Pilih Divisi --</option>
                                @foreach($divisions as $dv)
                                    <option value="{{ $dv->id }}" data-company="{{ $dv->company_id }}" {{ (int) old('division_id', $employee->division_id) === $dv->id ? 'selected' : '' }}>{{ $dv->name }}</option>
                                @endforeach
                            </select>
                            @error('division_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-12 col-md-6">
                            <label for="department_id">Departemen</label>
                            <select id="department_id" name="department_id" class="form-control{{ $errors->has('department_id') ? ' is-invalid' : '' }}">
                                <option value="">-- Pilih Departemen --</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}" data-company="{{ $d->company_id }}" data-division="{{ $d->division_id }}" {{ old('department_id', $employee->department_id) == $d->id ? 'selected' : '' }}>
                                        [{{ $d->code }}] {{ $d->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Belum ada di daftar? <a href="{{ route('appraisal.departments.index') }}" target="_blank">Kelola Departemen</a></small>
                            @error('department_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-12 col-md-6">
                            <label for="section_id">Section / Bagian</label>
                            <select id="section_id" name="section_id" class="form-control{{ $errors->has('section_id') ? ' is-invalid' : '' }}"
                                    data-selected="{{ old('section_id', $employee->section_id) }}">
                                <option value="">-- Pilih Departemen dulu --</option>
                            </select>
                            @error('section_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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
                                <option value="mitra"      {{ old('employment_status', $employee->employment_status) == 'mitra'      ? 'selected' : '' }}>Mitra</option>
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

        @if($employee->id)
        <hr class="my-4">
        <div class="h5 mb-3">Data Lengkap Karyawan</div>
        @include('appraisal.employee._data_tabs')
        @endif
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

// Cabang (branch_id) beda nama per Perusahaan (mis. "HO" ada di 3 PT) — filter
// opsinya sesuai Perusahaan terpilih, biar tidak nampilin nama dobel dari PT lain.
(function () {
    var companyEl = document.getElementById('company_id');
    var branchEl  = document.getElementById('branch_id');
    if (!companyEl || !branchEl) return;

    function filterBranches() {
        var companyId = companyEl.value;
        var stillValid = false;
        Array.prototype.forEach.call(branchEl.options, function (opt) {
            if (!opt.value) { opt.hidden = false; return; }
            var match = !companyId || opt.dataset.company === companyId;
            opt.hidden = !match;
            if (match && opt.selected) stillValid = true;
        });
        if (!stillValid) branchEl.value = '';
    }

    companyEl.addEventListener('change', filterBranches);
    filterBranches();
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
    document.querySelector('[name="ktp_district"]').value = document.querySelector('[name="domicile_district"]').value;
    document.querySelector('[name="ktp_subdistrict"]').value = document.querySelector('[name="domicile_subdistrict"]').value;

    var srcProv = document.getElementById('domicile_province_id');
    var dstProv = document.getElementById('ktp_province_id');
    dstProv.value = srcProv.value;
    renderCities(dstProv, document.getElementById('domicile_city_id').value);
    // biar cascade kecamatan/kelurahan KTP ikut ke-load setelah kota tersalin
    document.getElementById('ktp_city_id').dispatchEvent(new Event('change'));
}

// ── Dependent dropdown: Provinsi → Kota → Kecamatan → Kelurahan, Departemen → Section ──
(function () {
    var CITIES   = @json($cities->map->only(['id', 'name', 'province_id'])->values());
    var SECTIONS = @json($sections->map->only(['id', 'name', 'department_id'])->values());
    var REGION_API = "{{ url('region-api') }}";

    window.renderCities = function (provinceSelect, selectedId) {
        var target = document.getElementById(provinceSelect.dataset.cityTarget);
        if (!target) return;
        var pid = parseInt(provinceSelect.value, 10);
        target.innerHTML = '<option value="">-- Pilih --</option>';
        CITIES.filter(function (c) { return c.province_id === pid; })
              .sort(function (a, b) { return a.name.localeCompare(b.name); })
              .forEach(function (c) {
                  var o = document.createElement('option');
                  o.value = c.id;
                  o.textContent = c.name;
                  if (String(c.id) === String(selectedId)) o.selected = true;
                  target.appendChild(o);
              });
        target.dispatchEvent(new Event('change'));
    };

    function fillSelect(sel, rows, selectedId, placeholder) {
        sel.innerHTML = '<option value="">' + placeholder + '</option>';
        rows.forEach(function (r) {
            var o = document.createElement('option');
            o.value = r.id;
            o.textContent = r.name;
            if (String(r.id) === String(selectedId)) o.selected = true;
            sel.appendChild(o);
        });
    }

    function loadRegion(url, params) {
        var qs = Object.keys(params).map(function (k) { return k + '=' + encodeURIComponent(params[k]); }).join('&');
        return fetch(REGION_API + '/' + url + '?' + qs, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : []; })
            .catch(function () { return []; });
    }

    // Kecamatan: bereaksi saat kota berubah
    document.querySelectorAll('.region-district').forEach(function (distSel) {
        var citySel   = document.getElementById(distSel.dataset.citySource);
        var villageSel = document.getElementById(distSel.dataset.villageTarget);

        function reloadDistricts(keepSelected) {
            var cityId = citySel.value;
            if (!cityId) { fillSelect(distSel, [], null, '-- Pilih Kota dulu --'); fillSelect(villageSel, [], null, '-- Pilih Kecamatan dulu --'); return; }
            loadRegion('districts', { city_id: cityId }).then(function (rows) {
                fillSelect(distSel, rows, keepSelected ? distSel.dataset.selected : null, rows.length ? '-- Pilih Kecamatan --' : '-- (belum ada data, isi manual) --');
                distSel.dispatchEvent(new Event('change'));
            });
        }
        function reloadVillages(keepSelected) {
            var distId = distSel.value;
            if (!distId) { fillSelect(villageSel, [], null, '-- Pilih Kecamatan dulu --'); return; }
            loadRegion('villages', { district_id: distId }).then(function (rows) {
                fillSelect(villageSel, rows, keepSelected ? villageSel.dataset.selected : null, rows.length ? '-- Pilih Kelurahan/Desa --' : '-- (belum ada data, isi manual) --');
            });
        }

        citySel.addEventListener('change', function () { reloadDistricts(true); });
        distSel.addEventListener('change', function () { reloadVillages(true); });
        // load awal (kalau kota sudah ada nilainya)
        if (citySel.value) reloadDistricts(true);
    });

    function renderSections(selectedId) {
        var target = document.getElementById('section_id');
        var did = parseInt(document.getElementById('department_id').value, 10);
        target.innerHTML = '<option value="">-- Pilih --</option>';
        SECTIONS.filter(function (s) { return s.department_id === did; })
                .forEach(function (s) {
                    var o = document.createElement('option');
                    o.value = s.id;
                    o.textContent = s.name;
                    if (String(s.id) === String(selectedId)) o.selected = true;
                    target.appendChild(o);
                });
    }

    document.querySelectorAll('.region-province').forEach(function (sel) {
        var cityEl = document.getElementById(sel.dataset.cityTarget);
        renderCities(sel, cityEl ? cityEl.dataset.selected : null);
        sel.addEventListener('change', function () { renderCities(sel, null); });
    });

    var deptEl = document.getElementById('department_id');
    var sectionEl = document.getElementById('section_id');
    if (deptEl && sectionEl) {
        renderSections(sectionEl.dataset.selected);
        deptEl.addEventListener('change', function () { renderSections(null); });
    }
})();
</script>
@endsection
