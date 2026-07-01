<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formulir Pengaduan — PT. Pro Energi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:    #0f2a4a;
            --navy-2:  #1a3f6f;
            --accent:  #e8a020;
            --accent-2:#f5b942;
            --text:    #1e293b;
            --muted:   #64748b;
            --border:  #e2e8f0;
            --bg:      #f1f5f9;
            --white:   #ffffff;
            --danger:  #ef4444;
            --success: #22c55e;
            --radius:  12px;
            --shadow:  0 4px 24px rgba(0,0,0,.10);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* ── Hero ── */
        .hero {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
            padding: 48px 20px 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 100px;
            padding: 5px 14px;
            font-size: .75rem;
            font-weight: 600;
            color: rgba(255,255,255,.9);
            letter-spacing: .5px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .hero-badge svg { width: 14px; height: 14px; fill: var(--accent); }
        .hero h1 {
            font-size: clamp(1.6rem, 4vw, 2.4rem);
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
            margin-bottom: 10px;
        }
        .hero h1 span { color: var(--accent-2); }
        .hero p {
            font-size: .95rem;
            color: rgba(255,255,255,.7);
            max-width: 440px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* ── Trust bar ── */
        .trust-bar {
            display: flex;
            justify-content: center;
            gap: 24px;
            flex-wrap: wrap;
            margin-top: 32px;
        }
        .trust-item {
            display: flex;
            align-items: center;
            gap: 7px;
            color: rgba(255,255,255,.8);
            font-size: .8rem;
            font-weight: 500;
        }
        .trust-item svg { width: 16px; height: 16px; fill: var(--accent-2); flex-shrink: 0; }

        /* ── Main card ── */
        .card-wrap {
            max-width: 720px;
            margin: -44px auto 48px;
            padding: 0 16px;
            position: relative;
            z-index: 1;
        }
        .card {
            background: var(--white);
            border-radius: 20px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .card-inner { padding: 36px; }

        /* ── Section title ── */
        .section-label {
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--accent);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ── Category chips ── */
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-bottom: 4px;
        }
        .cat-option { position: relative; }
        .cat-option input[type=radio] { position: absolute; opacity: 0; width: 0; height: 0; }
        .cat-label {
            display: flex;
            align-items: center;
            gap: 10px;
            border: 2px solid var(--border);
            border-radius: 10px;
            padding: 12px 14px;
            cursor: pointer;
            transition: all .18s;
            font-size: .875rem;
            font-weight: 500;
            color: var(--muted);
            user-select: none;
        }
        .cat-label .cat-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
            transition: background .18s;
        }
        .cat-option input:checked + .cat-label {
            border-color: var(--navy-2);
            background: #eff6ff;
            color: var(--navy);
        }
        .cat-option input:checked + .cat-label .cat-icon {
            background: var(--navy-2);
        }
        .cat-label:hover { border-color: #94a3b8; color: var(--text); }

        /* ── Radio pill group ── */
        .radio-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .radio-pill { position: relative; }
        .radio-pill input[type=radio] { position: absolute; opacity: 0; width: 0; height: 0; }
        .radio-pill-label {
            display: inline-block;
            border: 2px solid var(--border);
            border-radius: 100px;
            padding: 8px 18px;
            cursor: pointer;
            font-size: .875rem;
            font-weight: 500;
            color: var(--muted);
            transition: all .18s;
            user-select: none;
        }
        .radio-pill input:checked + .radio-pill-label {
            border-color: var(--navy-2);
            background: #eff6ff;
            color: var(--navy);
            font-weight: 600;
        }
        .radio-pill-label:hover { border-color: #94a3b8; color: var(--text); }

        /* ── Form fields ── */
        .form-group { margin-bottom: 20px; }
        .form-label {
            display: block;
            font-size: .875rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 7px;
        }
        .form-label .req { color: var(--danger); margin-left: 2px; }
        .form-label .opt { color: var(--muted); font-weight: 400; font-size: .8rem; margin-left: 4px; }
        .form-control {
            width: 100%;
            padding: 11px 14px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: .9rem;
            font-family: inherit;
            color: var(--text);
            background: var(--white);
            transition: border-color .18s, box-shadow .18s;
            outline: none;
            -webkit-appearance: none;
        }
        .form-control:focus {
            border-color: var(--navy-2);
            box-shadow: 0 0 0 3px rgba(26,63,111,.1);
        }
        .form-control::placeholder { color: #a0aec0; }
        textarea.form-control { resize: vertical; min-height: 130px; line-height: 1.6; }
        .char-counter { font-size: .75rem; color: var(--muted); text-align: right; margin-top: 4px; }
        select.form-control { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2364748b'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; background-size: 20px; padding-right: 38px; cursor: pointer; }

        /* ── File upload ── */
        .file-drop {
            border: 2px dashed var(--border);
            border-radius: 10px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: all .18s;
            position: relative;
        }
        .file-drop:hover, .file-drop.dragover { border-color: var(--navy-2); background: #f0f6ff; }
        .file-drop input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
        .file-drop-icon { font-size: 2rem; margin-bottom: 8px; display: block; }
        .file-drop-text { font-size: .875rem; color: var(--muted); }
        .file-drop-text strong { color: var(--navy-2); }
        .file-preview {
            display: none;
            align-items: center;
            gap: 10px;
            background: #eff6ff;
            border: 2px solid #bfdbfe;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: .875rem;
            margin-top: 8px;
        }
        .file-preview svg { width: 20px; height: 20px; fill: var(--navy-2); flex-shrink: 0; }
        .file-preview-name { font-weight: 600; color: var(--navy); flex: 1; }
        .file-remove { background: none; border: none; cursor: pointer; color: var(--danger); font-size: 1.1rem; padding: 0 4px; }

        /* ── Anonymous toggle ── */
        .anon-toggle {
            display: flex;
            align-items: center;
            gap: 14px;
            background: #fefce8;
            border: 2px solid #fde68a;
            border-radius: 12px;
            padding: 16px 18px;
            cursor: pointer;
            user-select: none;
            transition: all .18s;
        }
        .anon-toggle:hover { border-color: #fbbf24; }
        .anon-toggle.active { background: #fff7ed; border-color: var(--accent); }
        .anon-toggle-text { flex: 1; }
        .anon-toggle-text strong { display: block; font-size: .9rem; color: var(--text); margin-bottom: 2px; }
        .anon-toggle-text span { font-size: .8rem; color: var(--muted); }
        .toggle-switch {
            width: 44px;
            height: 24px;
            background: #d1d5db;
            border-radius: 100px;
            position: relative;
            flex-shrink: 0;
            transition: background .2s;
        }
        .toggle-switch::after {
            content: '';
            position: absolute;
            top: 3px; left: 3px;
            width: 18px; height: 18px;
            background: #fff;
            border-radius: 50%;
            transition: transform .2s;
            box-shadow: 0 1px 3px rgba(0,0,0,.2);
        }
        .anon-toggle.active .toggle-switch { background: var(--accent); }
        .anon-toggle.active .toggle-switch::after { transform: translateX(20px); }

        /* ── Identity fields ── */
        .identity-fields { overflow: hidden; transition: max-height .3s ease, opacity .3s; }
        .identity-fields.collapsed { max-height: 0; opacity: 0; pointer-events: none; }
        .identity-fields.expanded { max-height: 600px; opacity: 1; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media (max-width: 520px) { .form-row { grid-template-columns: 1fr; } }

        /* ── Disclosure box ── */
        .disclosure-box {
            background: #f0fdf4;
            border: 2px solid #bbf7d0;
            border-radius: 12px;
            padding: 18px 20px;
        }
        .disclosure-box.is-invalid { border-color: var(--danger); background: #fef2f2; }
        .disclosure-check {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            cursor: pointer;
        }
        .disclosure-check input[type=checkbox] {
            width: 20px; height: 20px;
            margin-top: 2px;
            flex-shrink: 0;
            cursor: pointer;
            accent-color: var(--navy-2);
        }
        .disclosure-check span {
            font-size: .875rem;
            color: var(--text);
            line-height: 1.6;
        }

        /* ── Submit ── */
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all .2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            letter-spacing: .3px;
            box-shadow: 0 4px 14px rgba(15,42,74,.3);
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(15,42,74,.4);
        }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit svg { width: 20px; height: 20px; fill: currentColor; }
        .btn-submit:disabled { opacity: .7; cursor: not-allowed; transform: none; }
        .wb-spinner {
            display: inline-block; width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: wb-spin .7s linear infinite;
        }
        @keyframes wb-spin { to { transform: rotate(360deg); } }

        /* ── Divider ── */
        .divider { height: 1px; background: var(--border); margin: 28px 0; }

        /* ── Alert ── */
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 24px;
            font-size: .875rem;
            color: #b91c1c;
        }
        .alert-error ul { padding-left: 18px; margin: 0; }

        /* ── Field error ── */
        .field-error { font-size: .78rem; color: var(--danger); margin-top: 5px; }
        .form-control.is-invalid { border-color: var(--danger); }
        .form-control.is-invalid:focus { box-shadow: 0 0 0 3px rgba(239,68,68,.1); }

        /* ── Footer ── */
        .page-footer {
            text-align: center;
            padding: 0 16px 40px;
            font-size: .78rem;
            color: #94a3b8;
            line-height: 1.7;
        }

        /* ── Hint text ── */
        .hint { font-size: .78rem; color: var(--muted); margin-top: 5px; display: block; }

        /* ── Hidden ── */
        .d-none { display: none !important; }
    </style>
</head>
<body>

{{-- ── Hero ── --}}
<div class="hero">
    <div class="hero-badge">
        <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5L12 1zm0 4l5 2.18V11c0 3.5-2.33 6.79-5 7.93C9.33 17.79 7 14.5 7 11V7.18L12 5z"/></svg>
        Sistem Pengaduan Rahasia
    </div>
    <h1>Laporkan Pelanggaran<br><span>dengan Aman & Rahasia</span></h1>
    <p>Platform khusus bagi karyawan PT. Pro Energi untuk menyampaikan pengaduan secara profesional, aman, dan terlindungi.</p>
    <div class="trust-bar">
        <div class="trust-item">
            <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
            Identitas Terlindungi
        </div>
        <div class="trust-item">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            Ditangani Profesional
        </div>
        <div class="trust-item">
            <svg viewBox="0 0 24 24"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg>
            Nomor Tiket Tercatat
        </div>
    </div>
</div>

{{-- ── Main Card ── --}}
<div class="card-wrap">
    <div class="card">
        <div class="card-inner">

            @if ($errors->any())
                <div class="alert-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('whistleblower.store') }}" enctype="multipart/form-data" id="wb-form">
                @csrf

                {{-- ── Identitas Anonim ── --}}
                <div class="section-label">Identitas Pelapor</div>

                <div class="form-group">
                    <div class="anon-toggle" id="anon-toggle">
                        <div style="font-size:1.5rem;">🎭</div>
                        <div class="anon-toggle-text">
                            <strong>Apakah Anda ingin melaporkan secara anonim?</strong>
                            <span>Identitas Anda tidak akan disimpan sama sekali jika diaktifkan</span>
                        </div>
                        <div class="toggle-switch" id="toggle-switch"></div>
                        <input type="checkbox" name="is_anonymous" id="is_anonymous"
                            class="d-none" {{ old('is_anonymous') ? 'checked' : '' }}>
                    </div>
                </div>

                <div class="identity-fields {{ old('is_anonymous') ? 'collapsed' : 'expanded' }}" id="identity-fields">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="reporter_name" class="form-control @error('reporter_name') is-invalid @enderror"
                            value="{{ old('reporter_name') }}" placeholder="Nama Anda">
                        @error('reporter_name')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="reporter_email" class="form-control @error('reporter_email') is-invalid @enderror"
                                value="{{ old('reporter_email') }}" placeholder="email@proenergi.co.id">
                            @error('reporter_email')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">No. HP</label>
                            <input type="text" name="reporter_phone" class="form-control @error('reporter_phone') is-invalid @enderror"
                                value="{{ old('reporter_phone') }}" placeholder="08xx-xxxx-xxxx">
                            @error('reporter_phone')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <span class="hint" style="margin-bottom:8px;display:block;">Identitas hanya diketahui tim HRD dan dijaga kerahasiaannya.</span>
                </div>

                <div class="divider"></div>

                {{-- ── Lokasi Cabang ── --}}
                <div class="section-label">Lokasi & Hubungan</div>

                <div class="form-row" style="margin-bottom:20px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Lokasi Cabang yang Diinformasikan <span class="req">*</span></label>
                        <select name="branch_location" class="form-control @error('branch_location') is-invalid @enderror">
                            <option value="">— Pilih Cabang —</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch }}" {{ old('branch_location') === $branch ? 'selected' : '' }}>{{ $branch }}</option>
                            @endforeach
                        </select>
                        @error('branch_location')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Hubungan Anda dengan Perusahaan <span class="req">*</span></label>
                        <select name="reporter_relation" class="form-control @error('reporter_relation') is-invalid @enderror">
                            <option value="">— Pilih Hubungan —</option>
                            @foreach ($relations as $rel)
                                <option value="{{ $rel }}" {{ old('reporter_relation') === $rel ? 'selected' : '' }}>{{ $rel }}</option>
                            @endforeach
                        </select>
                        @error('reporter_relation')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="divider"></div>

                {{-- ── Kategori Pengaduan ── --}}
                <div class="section-label">Informasi yang Ingin Anda Laporkan</div>

                <div class="form-group">
                    <div class="category-grid">
                        @php
                        $catIcons = [
                            'Dugaan Gratifikasi'                             => ['icon' => '🎁', 'desc' => 'Suap & gratifikasi'],
                            'Penipuan'                                       => ['icon' => '🎭', 'desc' => 'Fraud & manipulasi'],
                            'Pelanggaran Hukum'                              => ['icon' => '⚖️', 'desc' => 'Hukum & regulasi'],
                            'Konflik Kepentingan'                            => ['icon' => '⚡', 'desc' => 'Benturan kepentingan'],
                            'Penyalahgunaan Wewenang'                        => ['icon' => '🔑', 'desc' => 'Abuse of authority'],
                            'Pelecehan atau Diskriminasi'                    => ['icon' => '🛡️', 'desc' => 'Kekerasan & intimidasi'],
                            'Kecurangan terkait Keuangan'                    => ['icon' => '💰', 'desc' => 'Penyelewengan dana'],
                            'Kebocoran Informasi / Rahasia Data Perusahaan'  => ['icon' => '🔒', 'desc' => 'Data & kerahasiaan'],
                            'Lainnya'                                        => ['icon' => '📋', 'desc' => 'Masalah lainnya'],
                        ];
                        @endphp
                        @foreach ($categories as $cat)
                            <label class="cat-option">
                                <input type="radio" name="category" value="{{ $cat }}"
                                    {{ old('category') === $cat ? 'checked' : '' }} required>
                                <span class="cat-label">
                                    <span class="cat-icon">{{ $catIcons[$cat]['icon'] ?? '📌' }}</span>
                                    <span>
                                        <strong style="display:block;font-size:.82rem;">{{ $cat }}</strong>
                                        <span style="font-size:.73rem;color:#94a3b8;">{{ $catIcons[$cat]['desc'] ?? '' }}</span>
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('category')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="divider"></div>

                {{-- ── Detail Kejadian ── --}}
                <div class="section-label">Detail Kejadian</div>

                <div class="form-group">
                    <label class="form-label">
                        Kapan dan di manakah kejadian ini terjadi? <span class="req">*</span>
                    </label>
                    <input type="text" name="incident_location_time"
                        class="form-control @error('incident_location_time') is-invalid @enderror"
                        value="{{ old('incident_location_time') }}"
                        placeholder="Contoh: Januari 2026, di Kantor Jakarta">
                    @error('incident_location_time')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Siapakah pihak yang Anda duga terlibat dalam kejadian ini? <span class="req">*</span>
                    </label>
                    <input type="text" name="suspected_parties"
                        class="form-control @error('suspected_parties') is-invalid @enderror"
                        value="{{ old('suspected_parties') }}"
                        placeholder="Nama atau jabatan pihak yang terlibat">
                    @error('suspected_parties')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Apakah ada saksi atau pihak lain yang mengetahui kejadian tersebut?
                        <span class="opt">(opsional)</span>
                    </label>
                    <input type="text" name="witnesses"
                        class="form-control @error('witnesses') is-invalid @enderror"
                        value="{{ old('witnesses') }}"
                        placeholder="Nama saksi atau keterangan lainnya">
                    @error('witnesses')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Jelaskan secara rinci kejadian yang Anda alami atau ketahui <span class="req">*</span>
                    </label>
                    <textarea name="description" id="description" rows="6"
                        class="form-control @error('description') is-invalid @enderror"
                        placeholder="Ceritakan secara jelas: apa yang terjadi, siapa yang terlibat, kapan dan di mana kejadian berlangsung...">{{ old('description') }}</textarea>
                    @error('description')<div class="field-error">{{ $message }}</div>@enderror
                    <div class="char-counter"><span id="char-count">0</span> karakter (min. 20)</div>
                </div>

                {{-- Upload --}}
                <div class="form-group">
                    <label class="form-label">
                        Lampiran Bukti <span class="opt">(opsional)</span>
                    </label>
                    <div class="file-drop" id="file-drop">
                        <input type="file" name="attachment" id="attachment-input"
                            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        <span class="file-drop-icon">📎</span>
                        <div class="file-drop-text">
                            <strong>Klik untuk pilih file</strong> atau seret ke sini
                        </div>
                        <div class="hint" style="margin-top:6px;">JPG, PNG, PDF, DOC/DOCX &bull; Maks 8 MB</div>
                    </div>
                    <div class="file-preview" id="file-preview">
                        <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm4 18H6V4h7v5h5v11z"/></svg>
                        <span class="file-preview-name" id="file-name">—</span>
                        <button type="button" class="file-remove" id="file-remove" title="Hapus">✕</button>
                    </div>
                    @error('attachment')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="divider"></div>

                {{-- ── Riwayat & Kesediaan ── --}}
                <div class="section-label">Informasi Tambahan</div>

                <div class="form-group">
                    <label class="form-label">
                        Apakah Anda pernah menyampaikan keluhan atau laporan terkait kejadian ini sebelumnya? <span class="req">*</span>
                    </label>
                    <div class="radio-group">
                        <label class="radio-pill">
                            <input type="radio" name="previously_reported" value="sudah"
                                {{ old('previously_reported') === 'sudah' ? 'checked' : '' }}>
                            <span class="radio-pill-label">Sudah Pernah</span>
                        </label>
                        <label class="radio-pill">
                            <input type="radio" name="previously_reported" value="belum"
                                {{ old('previously_reported') === 'belum' ? 'checked' : '' }}>
                            <span class="radio-pill-label">Belum Pernah</span>
                        </label>
                    </div>
                    @error('previously_reported')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Apakah Anda bersedia dihubungi untuk memberikan informasi tambahan apabila diperlukan? <span class="req">*</span>
                    </label>
                    <div class="radio-group">
                        <label class="radio-pill">
                            <input type="radio" name="willing_to_be_contacted" value="ya"
                                {{ old('willing_to_be_contacted') === 'ya' ? 'checked' : '' }}>
                            <span class="radio-pill-label">Ya</span>
                        </label>
                        <label class="radio-pill">
                            <input type="radio" name="willing_to_be_contacted" value="tidak"
                                {{ old('willing_to_be_contacted') === 'tidak' ? 'checked' : '' }}>
                            <span class="radio-pill-label">Tidak</span>
                        </label>
                    </div>
                    @error('willing_to_be_contacted')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="divider"></div>

                {{-- ── Disclosure ── --}}
                <div class="section-label">Disclosure</div>

                <div class="form-group">
                    <div class="disclosure-box @error('disclosure') is-invalid @enderror">
                        <label class="disclosure-check">
                            <input type="checkbox" name="disclosure" id="disclosure" value="1"
                                {{ old('disclosure') ? 'checked' : '' }}>
                            <span>
                                Saya menyatakan bahwa informasi yang saya sampaikan adalah sesuai dengan apa yang
                                telah saya alami dan disampaikan dengan itikad baik untuk perbaikan bersama.
                            </span>
                        </label>
                    </div>
                    @error('disclosure')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn-submit" id="wb-submit">
                    <span id="wb-submit-icon"><svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg></span>
                    <span id="wb-submit-text">Kirim Laporan Sekarang</span>
                </button>

            </form>
        </div>
    </div>
</div>

<div class="page-footer">
    &copy; {{ date('Y') }} PT. Pro Energi &mdash; Whistleblower System<br>
    Laporan diproses secara rahasia &bull; Keamanan data terjamin
</div>

<script>
(function(){
    // ── Char counter ──
    var desc = document.getElementById('description');
    var counter = document.getElementById('char-count');
    function updateCount(){ counter.textContent = desc.value.length; }
    desc.addEventListener('input', updateCount);
    updateCount();

    // ── Anonymous toggle ──
    var anonToggle  = document.getElementById('anon-toggle');
    var anonCheck   = document.getElementById('is_anonymous');
    var idFields    = document.getElementById('identity-fields');

    function setAnon(val){
        anonCheck.checked = val;
        if(val){
            anonToggle.classList.add('active');
            idFields.classList.remove('expanded');
            idFields.classList.add('collapsed');
        } else {
            anonToggle.classList.remove('active');
            idFields.classList.remove('collapsed');
            idFields.classList.add('expanded');
        }
    }

    setAnon(anonCheck.checked);

    anonToggle.addEventListener('click', function(){
        setAnon(!anonCheck.checked);
    });

    // ── File upload ──
    var fileInput   = document.getElementById('attachment-input');
    var fileDrop    = document.getElementById('file-drop');
    var filePreview = document.getElementById('file-preview');
    var fileName    = document.getElementById('file-name');
    var fileRemove  = document.getElementById('file-remove');

    function showFile(name){
        fileDrop.style.display  = 'none';
        filePreview.style.display = 'flex';
        fileName.textContent    = name;
    }
    function clearFile(){
        fileDrop.style.display  = '';
        filePreview.style.display = 'none';
        fileName.textContent    = '—';
        fileInput.value         = '';
    }

    fileInput.addEventListener('change', function(){
        if(this.files && this.files[0]) showFile(this.files[0].name);
    });
    fileRemove.addEventListener('click', clearFile);

    fileDrop.addEventListener('dragover',  function(e){ e.preventDefault(); this.classList.add('dragover'); });
    fileDrop.addEventListener('dragleave', function(){ this.classList.remove('dragover'); });
    fileDrop.addEventListener('drop', function(e){
        e.preventDefault();
        this.classList.remove('dragover');
        var f = e.dataTransfer.files[0];
        if(f){ fileInput.files = e.dataTransfer.files; showFile(f.name); }
    });

    // ── Submit loading state ──
    document.getElementById('wb-form').addEventListener('submit', function(){
        var btn  = document.getElementById('wb-submit');
        var icon = document.getElementById('wb-submit-icon');
        var text = document.getElementById('wb-submit-text');
        btn.disabled = true;
        icon.innerHTML = '<span class="wb-spinner"></span>';
        text.textContent = 'Mengirim...';
    });
})();
</script>
</body>
</html>
