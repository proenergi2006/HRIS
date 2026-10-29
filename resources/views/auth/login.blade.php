@extends('layouts.grain-auth')
@section('title', 'Masuk')

@section('content')
@php
    $groupCompanies = \App\Models\Company::where('is_active', true)->orderBy('id')->get();
    $chipColors = ['#e8a020', '#4ea8de', '#5cc9a7'];
@endphp
<style>
    :root {
        --pe-dark:   #0d2137;
        --pe-blue:   #1a4a8a;
        --pe-accent: #e8a020;
    }
    html, body { height: 100%; }
    body { margin: 0; }

    .login-page {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 24px;
        padding: 32px 20px;
        position: relative;
        overflow: hidden;
        background: var(--pe-dark);
    }
    /* Latar: foto yang sama di-blur memenuhi layar, jadi tidak ada sisi kosong
       walau foto utuh (tidak di-crop) ditampilkan di .login-visual. */
    .login-page::before {
        content: '';
        position: absolute;
        inset: -40px;
        background: url('{{ asset('img/login-bg.jpg') }}') center / cover no-repeat;
        filter: blur(28px) brightness(.5) saturate(1.1);
        pointer-events: none;
    }

    /* ── Foto perusahaan: tampil utuh, rasio asli dijaga ── */
    .login-visual {
        position: relative;
        z-index: 1;
        flex: 1 1 auto;
        min-width: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: fadeUp .55s ease-out both;
    }
    .login-visual img {
        display: block;
        width: auto;
        height: auto;
        max-width: 100%;
        max-height: calc(100vh - 64px);
        border-radius: 18px;
        box-shadow: 0 30px 70px rgba(4,12,26,0.5);
    }
    /* Desktop: foto penuh layar. Fokus ke kiri supaya panel teks perusahaan
       tidak terpotong di layar yang bukan 16:9; sisi kanan (di belakang kartu)
       yang dikorbankan. */
    @media (min-width: 992px) {
        .login-page { flex-direction: row; justify-content: flex-end; padding: 32px 7vw; }
        .login-page::before { inset: 0; filter: none; background-position: left center; }
        .login-page::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent 50%, rgba(8,14,26,.5) 100%);
            pointer-events: none;
        }
        .login-visual { display: none; }
        .login-card { flex: none; }
    }
    /* Layar HP: foto kecil jadi teksnya tak terbaca — cukup latar blur + kartu. */
    @media (max-width: 575.98px) {
        .login-visual { display: none; }
    }

    /* ── Hero / brand ── */
    .hero-brand {
        position: relative;
        z-index: 1;
        text-align: center;
        color: #fff;
        animation: fadeUp .55s ease-out both;
    }
    .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--pe-accent);
        background: rgba(232,160,32,0.12);
        border: 1px solid rgba(232,160,32,0.35);
        padding: 5px 14px;
        border-radius: 20px;
        margin-bottom: 16px;
    }
    .hero-title {
        font-size: clamp(2.5rem, 6vw, 3.4rem);
        font-weight: 900;
        letter-spacing: 4px;
        margin: 0 0 8px;
        background: linear-gradient(90deg, #fff, #ffe3ad);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .hero-tagline {
        font-size: .95rem;
        color: rgba(255,255,255,0.75);
        max-width: 380px;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* ── Card ── */
    .login-card {
        position: relative;
        z-index: 1;
        width: min(400px, 92vw);
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 25px 60px rgba(4,12,26,0.38), 0 2px 8px rgba(4,12,26,0.12);
        padding: 38px 34px 30px;
        animation: fadeUp .6s .1s ease-out both;
    }
    .card-logo {
        display: block;
        height: 56px;
        margin: 0 auto 20px;
        object-fit: contain;
    }
    .login-card h2 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--pe-dark);
        margin-bottom: 4px;
        text-align: center;
    }
    .login-card .subtitle {
        font-size: .82rem;
        color: #6b7280;
        margin-bottom: 26px;
        text-align: center;
    }
    .login-card .form-label {
        font-size: .8rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 5px;
        display: block;
    }
    .input-icon-wrap { position: relative; }
    .input-icon-wrap svg {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        pointer-events: none;
    }
    .login-card .form-control {
        border: 1.5px solid #d1d5db;
        border-radius: 8px;
        padding: 10px 14px 10px 38px;
        font-size: .92rem;
        width: 100%;
        transition: border-color .2s, box-shadow .2s;
    }
    .login-card .form-control:focus {
        border-color: var(--pe-blue);
        box-shadow: 0 0 0 3px rgba(26,74,138,0.12);
        outline: none;
    }
    .btn-signin {
        background: linear-gradient(135deg, var(--pe-blue), #235faa);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 12px;
        width: 100%;
        font-size: .95rem;
        font-weight: 700;
        cursor: pointer;
        transition: transform .15s, box-shadow .15s, background .2s;
        margin-top: 10px;
        box-shadow: 0 8px 20px rgba(26,74,138,0.28);
    }
    .btn-signin:hover { transform: translateY(-1px); box-shadow: 0 10px 24px rgba(26,74,138,0.36); }
    .btn-signin:active { transform: translateY(0); }

    /* ── Group companies ── */
    .company-group {
        position: relative;
        z-index: 1;
        text-align: center;
        animation: fadeUp .65s .18s ease-out both;
    }
    .company-group-label {
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: rgba(255,255,255,0.55);
        margin-bottom: 10px;
    }
    .company-chips {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
    }
    .chip {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: .8rem;
        font-weight: 600;
        color: #fff;
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.16);
        backdrop-filter: blur(6px);
        padding: 7px 15px;
        border-radius: 20px;
        transition: background .2s, transform .2s;
    }
    .chip:hover { background: rgba(255,255,255,0.14); transform: translateY(-1px); }
    .chip-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

    .login-footer {
        position: relative;
        z-index: 1;
        text-align: center;
        font-size: .75rem;
        color: rgba(255,255,255,0.4);
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @media (prefers-reduced-motion: reduce) {
        .hero-brand, .login-visual, .login-card, .company-group { animation: none; }
    }
</style>

<div class="login-page">
    <div class="login-visual">
        <img src="{{ asset('img/login-bg.jpg') }}" alt="PT Pro Trans Internasional dan anak perusahaan">
    </div>

    <div class="login-card">
        <img src="{{ asset('img/logo-pe.png') }}" class="card-logo" alt="ProPeople">
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
                <div class="input-icon-wrap">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v16H4z" opacity="0"/><path d="M22 6l-10 7L2 6"/><path d="M2 6h20v12H2z"/></svg>
                    <input id="email" type="email" name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}"
                           placeholder="nama@proenergi.co.id"
                           required autofocus>
                </div>
                @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="form-group mb-3">
                <div class="d-flex justify-content-between">
                    <label class="form-label" for="password">{{ __('common.login_password') }}</label>
                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                           style="font-size:0.76rem; color:#6b7280;">{{ __('common.login_forgot') }}</a>
                    @endif
                </div>
                <div class="input-icon-wrap">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input id="password" type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="••••••••"
                           required>
                </div>
                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="form-group mb-1">
                <label style="display:flex;align-items:center;gap:8px;font-size:0.82rem;color:#4b5563;cursor:pointer;">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    {{ __('common.login_remember') }}
                </label>
            </div>

            <button type="submit" class="btn-signin">{{ __('common.login_btn') }}</button>
        </form>
    </div>

</div>
@endsection
