@extends('layouts.grain')
@section('title', $role->id ? 'Edit Role' : 'Role Baru')

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
    <div class="card-body">
        <nav class="d-none d-md-block" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Role & Hak Akses</a></li>
                <li class="breadcrumb-item active">{{ $role->id ? 'Edit' : 'Baru' }}</li>
            </ol>
        </nav>

        <div class="mb-4">
            <div class="h3 mb-0">{{ $role->id ? 'Edit Role: ' . $role->name : 'Role Baru' }}</div>
        </div>

        <form method="POST" action="{{ $role->id ? route('admin.roles.update', $role) : route('admin.roles.store') }}">
            @if($role->id) @method('PUT') @endif
            @csrf

            <div class="form-group col-12 col-md-4 px-0">
                <label for="name">Nama Role <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $role->name) }}"
                       placeholder="mis. hr_admin_pfr" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">Huruf kecil, angka, strip/underscore — tanpa spasi.</small>
            </div>

            <hr>
            <div class="font-weight-bold mb-2">Hak Akses per Modul</div>
            <p class="small text-muted">Centang aksi yang boleh dilakukan role ini pada tiap modul.</p>

            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="min-width:220px">Modul</th>
                            <th class="text-center">Lihat</th>
                            <th class="text-center">Tambah</th>
                            <th class="text-center">Ubah</th>
                            <th class="text-center">Hapus</th>
                            <th class="text-center">Approve</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($modules as $key => $def)
                        <tr>
                            <td class="align-middle">{{ $def['label'] }}</td>
                            @foreach(['view' => 'Lihat', 'create' => 'Tambah', 'edit' => 'Ubah', 'delete' => 'Hapus', 'approve' => 'Approve'] as $action => $label)
                                <td class="text-center align-middle">
                                    @if(in_array($action, $def['actions']))
                                        @php $perm = "{$key}.{$action}"; @endphp
                                        <input type="checkbox" name="permissions[]" value="{{ $perm }}"
                                               {{ in_array($perm, old('permissions', $checked)) ? 'checked' : '' }}>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between mt-3">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">
                    {{ $role->id ? 'Simpan Perubahan' : 'Buat Role' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
