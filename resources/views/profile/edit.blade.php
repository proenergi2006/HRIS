@extends('layouts.grain')
@section('title', 'Profil Saya')

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
    <div class="card-body">
        <nav class="d-none d-md-block" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Profil Saya</li>
            </ol>
        </nav>

        <div class="mb-4 d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center font-weight-bold"
                 style="width:52px;height:52px;font-size:1.3rem;flex-shrink:0;">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div>
                <div class="h4 mb-0">{{ auth()->user()->name }}</div>
                <small class="text-muted">{{ auth()->user()->email }}
                    @foreach(auth()->user()->getRoleNames() as $role)
                        <span class="badge badge-secondary ml-1">{{ $role }}</span>
                    @endforeach
                </small>
            </div>
        </div>

        <form action="{{ route('profile.update') }}" method="POST">
            @csrf

            {{-- Info dasar --}}
            <div class="font-weight-bold text-uppercase small text-muted mb-3" style="letter-spacing:.6px;">Informasi Akun</div>
            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" id="name" name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', auth()->user()->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-12 col-md-6">
                    <label for="email">Alamat Email</label>
                    <input type="email" id="email" name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email', auth()->user()->email) }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr class="my-4">

            {{-- Ganti password --}}
            <div class="font-weight-bold text-uppercase small text-muted mb-1" style="letter-spacing:.6px;">Ganti Password</div>
            <small class="text-muted d-block mb-3">Kosongkan jika tidak ingin mengganti password.</small>
            <div class="form-row">
                <div class="form-group col-12 col-md-4">
                    <label for="old_password">Password Saat Ini</label>
                    <input type="password" id="old_password" name="old_password"
                        class="form-control @error('old_password') is-invalid @enderror"
                        placeholder="Password lama Anda" autocomplete="current-password">
                    @error('old_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-12 col-md-4">
                    <label for="password">Password Baru</label>
                    <input type="password" id="password" name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Min. 6 karakter" autocomplete="new-password">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-12 col-md-4">
                    <label for="password_confirmation">Konfirmasi Password Baru</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="form-control"
                        placeholder="Ulangi password baru" autocomplete="new-password">
                </div>
            </div>

            <div class="d-flex justify-content-end mt-2">
                <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
