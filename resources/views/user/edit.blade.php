@extends('layouts.grain')
@section('title', $user->id ? 'Edit User' : 'Tambah User')

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
    <div class="card-body">
        <nav class="d-none d-md-block" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('user.index') }}">Users</a></li>
                <li class="breadcrumb-item active">{{ $user->id ? 'Edit' : 'Tambah Baru' }}</li>
            </ol>
        </nav>

        <div class="mb-4">
            <div class="h3 mb-0">{{ $user->id ? 'Edit User' : 'Tambah User Baru' }}</div>
        </div>

        <form method="POST"
              action="{{ $user->id ? route('user.update', $user) : route('user.store') }}">
            @if($user->id)
                @method('PATCH')
            @endif
            @csrf

            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label for="name">Nama <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $user->name) }}"
                           placeholder="Nama lengkap" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-12 col-md-6">
                    <label for="email">Email <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $user->email) }}"
                           placeholder="nama@proenergi.co.id" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label for="employee_id">Hubungkan ke Data Karyawan</label>
                    <select id="employee_id" name="employee_id"
                            class="form-control @error('employee_id') is-invalid @enderror">
                        <option value="">-- Tidak dihubungkan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}"
                                {{ old('employee_id', $user->employee?->id) == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }}
                                @if($emp->department) — {{ $emp->department->name }}@endif
                                @if($emp->position) ({{ $emp->position->name }})@endif
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr>
            <div class="font-weight-bold mb-2">Role & Company</div>
            <p class="small text-muted">
                Satu user bisa punya beberapa role, masing-masing bisa dibatasi ke 1 company atau
                berlaku di semua company. Kelola daftar role &amp; hak aksesnya di
                <a href="{{ route('admin.roles.index') }}">Role & Hak Akses</a>.
            </p>

            <div id="role-rows">
                @forelse($assignments as $i => $a)
                    <div class="form-row role-row">
                        <div class="form-group col-5">
                            <select name="role_id[]" class="form-control">
                                <option value="">-- Pilih Role --</option>
                                @foreach($roles as $r)
                                    @php $roleObj = \Spatie\Permission\Models\Role::where('name', $r)->first(); @endphp
                                    <option value="{{ $roleObj?->id }}" {{ $a->role_id == $roleObj?->id ? 'selected' : '' }}>{{ $r }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-5">
                            <select name="company_id[]" class="form-control">
                                <option value="">-- Semua Company --</option>
                                @foreach($companies as $c)
                                    <option value="{{ $c->id }}" {{ $a->company_id == $c->id ? 'selected' : '' }}>{{ $c->short_name ?? $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-2">
                            <button type="button" class="btn btn-outline-danger btn-block remove-role-row"><i class="gd-trash"></i></button>
                        </div>
                    </div>
                @empty
                @endforelse
            </div>
            <button type="button" id="add-role-row" class="btn btn-sm btn-outline-primary mb-3">
                <i class="gd-plus mr-1"></i> Tambah Role
            </button>

            <template id="role-row-template">
                <div class="form-row role-row">
                    <div class="form-group col-5">
                        <select name="role_id[]" class="form-control">
                            <option value="">-- Pilih Role --</option>
                            @foreach($roles as $r)
                                @php $roleObj = \Spatie\Permission\Models\Role::where('name', $r)->first(); @endphp
                                <option value="{{ $roleObj?->id }}">{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-5">
                        <select name="company_id[]" class="form-control">
                            <option value="">-- Semua Company --</option>
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}">{{ $c->short_name ?? $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-2">
                        <button type="button" class="btn btn-outline-danger btn-block remove-role-row"><i class="gd-trash"></i></button>
                    </div>
                </div>
            </template>

            <script>
            (function () {
                var rows = document.getElementById('role-rows');
                var tpl  = document.getElementById('role-row-template');

                document.getElementById('add-role-row').addEventListener('click', function () {
                    rows.appendChild(tpl.content.cloneNode(true));
                });

                rows.addEventListener('click', function (e) {
                    var btn = e.target.closest('.remove-role-row');
                    if (btn) btn.closest('.role-row').remove();
                });
            })();
            </script>

            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label for="department">Departemen</label>
                    <input type="text" id="department" name="department"
                           class="form-control @error('department') is-invalid @enderror"
                           value="{{ old('department', $user->department) }}"
                           placeholder="Contoh: IT, HR &amp; GA, Finance (kosongkan = akses semua)">
                    @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">
                        Harus sama persis dengan kolom Departemen di Data Karyawan.
                        Kosongkan untuk approver yang bisa akses semua departemen.
                    </small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label for="password">
                        Password {{ $user->id ? '(kosongkan jika tidak diubah)' : '' }}
                        @if(!$user->id)<span class="text-danger">*</span>@endif
                    </label>
                    <input type="password" id="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Minimal 6 karakter"
                           {{ !$user->id ? 'required' : '' }}>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-12 col-md-6">
                    <label for="password_confirmation">Konfirmasi Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="form-control"
                           placeholder="Ulangi password"
                           {{ !$user->id ? 'required' : '' }}>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-2">
                <a href="{{ route('user.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">
                    {{ $user->id ? 'Simpan Perubahan' : 'Tambah User' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
