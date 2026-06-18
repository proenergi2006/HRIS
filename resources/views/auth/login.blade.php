@extends('layouts.grain-auth')
@section('title', 'Masuk')

@section('content')
<style>
    :root {
        --pe-dark:   #0d2137;
        --pe-blue:   #1a4a8a;
        --pe-accent: #e8a020;
    }
    body { background: #f0f4f8; }

    .login-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: stretch;
    }

    /* Left panel — branding */
    .login-left {
        flex: 1;
        background: linear-gradient(160deg, var(--pe-dark) 0%, var(--pe-blue) 100%);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 50px 40px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .login-left::before {
        content: '';
        position: absolute;
        width: 500px; height: 500px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
        top: -200px; right: -180px;
    }
    .login-left::after {
        content: '';
        position: absolute;
        width: 300px; height: 300px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
        bottom: -120px; left: -80px;
    }
    .login-logo {
        font-size: 2rem;
        font-weight: 900;
        letter-spacing: 3px;
        color: var(--pe-accent);
        margin-bottom: 6px;
        position: relative;
        z-index: 1;
    }
    .login-company {
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 2px;
        text-transform: uppercase;
        opacity: 0.7;
        margin-bottom: 40px;
        position: relative;
        z-index: 1;
    }
    .login-tagline {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.35;
        margin-bottom: 14px;
        position: relative;
        z-index: 1;
    }
    .login-desc {
        font-size: 0.88rem;
        opacity: 0.7;
        max-width: 300px;
        text-align: center;
        line-height: 1.6;
        position: relative;
        z-index: 1;
    }

    /* Right panel — form */
    .login-right {
        width: 420px;
        min-width: 380px;
        background: #fff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 50px 44px;
    }
    .login-right h2 {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--pe-dark);
        margin-bottom: 6px;
    }
    .login-right .subtitle {
        font-size: 0.85rem;
        color: #6b7280;
        margin-bottom: 32px;
    }
    .login-right .form-label {
        font-size: 0.82rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 5px;
        display: block;
    }
    .login-right .form-control {
        border: 1.5px solid #d1d5db;
        border-radius: 6px;
        padding: 10px 14px;
        font-size: 0.92rem;
        transition: border-color .2s, box-shadow .2s;
    }
    .login-right .form-control:focus {
        border-color: var(--pe-blue);
        box-shadow: 0 0 0 3px rgba(26,74,138,0.12);
        outline: none;
    }
    .btn-signin {
        background: var(--pe-blue);
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 12px;
        width: 100%;
        font-size: 0.95rem;
        font-weight: 700;
        cursor: pointer;
        transition: background .2s, transform .1s;
        margin-top: 8px;
    }
    .btn-signin:hover { background: var(--pe-dark); transform: translateY(-1px); }
    .login-footer {
        margin-top: 30px;
        text-align: center;
        font-size: 0.78rem;
        color: #9ca3af;
    }

    @media (max-width: 768px) {
        .login-left { display: none; }
        .login-right { width: 100%; min-width: unset; padding: 40px 28px; }
    }
</style>

<div class="login-wrapper">

    <!-- Left branding panel -->
    <div class="login-left">
        <div class="login-logo">SIPRO</div>
        <div class="login-company">PT. Pro Energi</div>
        <div class="login-tagline">{{ __('common.login_tagline') }}</div>
        <p class="login-desc">{{ __('common.login_desc') }}</p>
    </div>

    <!-- Right form panel -->
    <div class="login-right">
        <h2>{{ __('common.login_welcome') }}</h2>
        <p class="subtitle">{{ __('common.login_subtitle') }}</p>

        @if(session('status'))
            <div class="alert alert-warning py-2 mb-3" style="font-size:0.85rem;">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger py-2 mb-3" style="font-size:0.85rem;">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group mb-3">
                <label class="form-label" for="email">{{ __('common.login_email') }}</label>
                <input id="email" type="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}"
                       placeholder="nama@proenergi.co.id"
                       required autofocus>
            </div>

            <div class="form-group mb-3">
                <div class="d-flex justify-content-between">
                    <label class="form-label" for="password">{{ __('common.login_password') }}</label>
                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                           style="font-size:0.78rem; color:#6b7280;">{{ __('common.login_forgot') }}</a>
                    @endif
                </div>
                <input id="password" type="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="••••••••"
                       required>
            </div>

            <div class="form-group mb-1">
                <label style="display:flex;align-items:center;gap:8px;font-size:0.82rem;color:#4b5563;cursor:pointer;">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    {{ __('common.login_remember') }}
                </label>
            </div>

            <button type="submit" class="btn-signin">{{ __('common.login_btn') }}</button>
        </form>

        <div class="login-footer">
            &copy; {{ date('Y') }} PT. Pro Energi &nbsp;&mdash;&nbsp; SIPRO v1.0
        </div>
    </div>

</div>
@endsection
