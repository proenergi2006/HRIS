<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') — {{ config('app.name', 'SIPRO') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',sans-serif;background:#f1f5f9;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
        .card{background:#fff;border-radius:20px;box-shadow:0 8px 32px rgba(0,0,0,.1);padding:56px 48px;text-align:center;max-width:480px;width:100%}
        .code{font-size:6rem;font-weight:800;line-height:1;letter-spacing:-4px;background:linear-gradient(135deg,#0f2a4a,#1a3f6f);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:16px}
        .icon{font-size:3.5rem;margin-bottom:12px}
        h1{font-size:1.4rem;font-weight:700;color:#1e293b;margin-bottom:10px}
        p{font-size:.9rem;color:#64748b;line-height:1.6;margin-bottom:28px}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:11px 24px;background:#0f2a4a;color:#fff;border-radius:10px;text-decoration:none;font-weight:600;font-size:.875rem;transition:all .18s}
        .btn:hover{background:#1a3f6f;transform:translateY(-1px)}
        .btn-ghost{background:none;border:2px solid #e2e8f0;color:#64748b;margin-left:10px}
        .btn-ghost:hover{background:#f8fafc;transform:none}
        .divider{height:1px;background:#e2e8f0;margin:28px 0}
        .meta{font-size:.75rem;color:#94a3b8}
    </style>
</head>
<body>
<div class="card">
    <div class="code">@yield('code')</div>
    <div class="icon">@yield('icon')</div>
    <h1>@yield('title')</h1>
    <p>@yield('description')</p>
    @auth
        <a href="{{ route('dashboard') }}" class="btn">← Kembali ke Dashboard</a>
    @else
        <a href="{{ url('/') }}" class="btn">← Kembali ke Beranda</a>
        <a href="{{ route('login') }}" class="btn btn-ghost">Login</a>
    @endauth
    <div class="divider"></div>
    <div class="meta">{{ config('app.name', 'SIPRO') }} &bull; PT. Pro Energi</div>
</div>
</body>
</html>
