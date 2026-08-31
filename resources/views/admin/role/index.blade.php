@extends('layouts.grain')
@section('title', 'Role & Hak Akses')

@section('content')
@include('components.notification')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="h3 mb-0">Role & Hak Akses</div>
    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
        <i class="gd-plus mr-1"></i> Role Baru
    </a>
</div>

<div class="card">
    <div class="card-header font-weight-bold">Daftar Role</div>
    <div class="card-body">
        <p class="small text-muted">
            Role menentukan modul apa saja yang bisa diakses (lihat/tambah/ubah/hapus/approve).
            Untuk mengatur di company mana sebuah role berlaku bagi seorang user, buka
            <a href="{{ route('user.index') }}">Manajemen User</a>.
        </p>

        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Role</th>
                        <th class="text-center">Jumlah User</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($roles as $role)
                    <tr>
                        <td>{{ $role->name }}</td>
                        <td class="text-center">{{ $role->users_count }}</td>
                        <td style="width:120px;white-space:nowrap">
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-xs btn-outline-warning">
                                <i class="gd-pencil"></i>
                            </a>
                            <a href="#" class="btn btn-xs btn-outline-danger"
                               data-confirm="Hapus role {{ $role->name }}?" data-confirm-title="Hapus Role"
                               data-form="role-delete-{{ $role->id }}"
                               @if($role->users_count > 0) style="pointer-events:none;opacity:.4" title="Masih dipakai user" @endif>
                                <i class="gd-trash"></i>
                            </a>
                            <form id="role-delete-{{ $role->id }}" method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="d-none">
                                @csrf @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
