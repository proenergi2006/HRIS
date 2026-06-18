<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIPRO — Sistem Informasi HR & GA | PT. Pro Energi</title>
    <link href="{{ asset('graindashboard/css/graindashboard.css') }}" rel="stylesheet">
    <style>
        :root {
            --pe-dark:   #0d2137;
            --pe-blue:   #1a4a8a;
            --pe-accent: #e8a020;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f5f7fa; }

        /* ── NAVBAR ── */
        .top-bar {
            background: var(--pe-dark);
            padding: 14px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .top-bar .brand { color: #fff; font-size: 1.1rem; font-weight: 700; letter-spacing: 1px; }
        .top-bar .brand span { color: var(--pe-accent); }
        .top-bar .nav-login {
            background: var(--pe-accent);
            color: #fff;
            padding: 8px 22px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: opacity .2s;
        }
        .top-bar .nav-login:hover { opacity: .85; color: #fff; text-decoration: none; }

        /* ── HERO ── */
        .hero {
            background: linear-gradient(135deg, var(--pe-dark) 0%, var(--pe-blue) 60%, #265fa8 100%);
            padding: 80px 40px 90px;
            text-align: center;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            width: 600px; height: 600px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
            top: -200px; right: -150px;
        }
        .hero::after {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: rgba(255,255,255,0.03);
            bottom: -150px; left: -100px;
        }
        .hero-eyebrow {
            display: inline-block;
            background: rgba(232,160,32,0.2);
            border: 1px solid var(--pe-accent);
            color: var(--pe-accent);
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .hero h1 {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 12px;
            line-height: 1.2;
        }
        .hero h1 span { color: var(--pe-accent); }
        .hero p {
            font-size: 1.05rem;
            opacity: 0.85;
            max-width: 600px;
            margin: 0 auto 36px;
            line-height: 1.6;
        }
        .hero-btn {
            display: inline-block;
            background: var(--pe-accent);
            color: #fff;
            padding: 14px 40px;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 4px 20px rgba(232,160,32,0.4);
            transition: transform .2s, box-shadow .2s;
        }
        .hero-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 28px rgba(232,160,32,0.5);
            color: #fff;
            text-decoration: none;
        }
        .hero-sub-link {
            display: block;
            margin-top: 14px;
            color: rgba(255,255,255,0.6);
            font-size: 0.85rem;
            text-decoration: none;
        }
        .hero-sub-link:hover { color: #fff; }

        /* ── MODULES BAR ── */
        .modules-bar {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 20px 40px;
        }
        .modules-bar .inner {
            max-width: 960px;
            margin: 0 auto;
            display: flex;
            justify-content: space-around;
            text-align: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        .module-item .icon { font-size: 1.6rem; margin-bottom: 6px; }
        .module-item .lbl { font-size: 0.78rem; color: #374151; font-weight: 600; }
        .module-item .status {
            display: inline-block;
            margin-top: 3px;
            font-size: 0.68rem;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: 600;
        }
        .status-live { background: #d1fae5; color: #065f46; }
        .status-soon { background: #fef3c7; color: #92400e; }

        /* ── FEATURES ── */
        .features {
            padding: 70px 40px;
            max-width: 1000px;
            margin: 0 auto;
        }
        .features h2 {
            text-align: center;
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--pe-dark);
            margin-bottom: 8px;
        }
        .features .sub {
            text-align: center;
            color: #6b7280;
            margin-bottom: 50px;
            font-size: 0.95rem;
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }
        @media (max-width: 768px) {
            .feature-grid { grid-template-columns: 1fr; }
            .hero h1 { font-size: 1.9rem; }
            .top-bar { padding: 12px 20px; }
        }
        .feature-card {
            background: #fff;
            border-radius: 10px;
            padding: 28px 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border-top: 3px solid var(--pe-blue);
            transition: transform .2s, box-shadow .2s;
        }
        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 24px rgba(0,0,0,0.1);
        }
        .feature-icon {
            width: 48px; height: 48px;
            background: #eef3fb;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            font-size: 1.4rem;
            color: var(--pe-blue);
        }
        .feature-card h4 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--pe-dark);
            margin-bottom: 8px;
        }
        .feature-card p { font-size: 0.875rem; color: #6b7280; line-height: 1.6; margin: 0; }

        /* ── FOOTER ── */
        .footer {
            background: var(--pe-dark);
            color: rgba(255,255,255,0.5);
            text-align: center;
            padding: 24px 40px;
            font-size: 0.8rem;
        }
        .footer strong { color: rgba(255,255,255,0.8); }
    </style>
</head>
<body>

    <!-- Navbar -->
    <div class="top-bar">
        <div class="brand">PT. PRO ENERGI &nbsp;<span>|</span>&nbsp; SIPRO</div>
        @auth
            <a href="{{ url('/dashboard') }}" class="nav-login">Dashboard &rarr;</a>
        @else
            <a href="{{ route('login') }}" class="nav-login">Masuk &rarr;</a>
        @endauth
    </div>

    <!-- Hero -->
    <div class="hero">
        <div class="hero-eyebrow">PT. Pro Energi — Internal System</div>
        <h1>Sistem Informasi<br><span>HR & GA</span></h1>
        <p>
            Platform digital terpadu untuk kebutuhan administrasi Human Resources & General Affairs
            PT. Pro Energi — mencakup HRIS, pengelolaan karyawan, dan layanan administrasi HR & GA.
        </p>
        @auth
            <a href="{{ url('/dashboard') }}" class="hero-btn">Buka Dashboard &rarr;</a>
        @else
            <a href="{{ route('login') }}" class="hero-btn">Masuk ke SIPRO &rarr;</a>
            <a href="{{ route('login') }}" class="hero-sub-link">Hubungi Admin HR & GA untuk akun</a>
        @endauth
    </div>

    <!-- Modules Bar -->
    <div class="modules-bar">
        <div class="inner">
            <div class="module-item">
                <div class="icon">&#128203;</div>
                <div class="lbl">Kinerja Karyawan</div>
                <div class="status status-live">Aktif</div>
            </div>
            <div class="module-item">
                <div class="icon">&#128100;</div>
                <div class="lbl">Data Personalia</div>
                <div class="status status-soon">Segera</div>
            </div>
            <div class="module-item">
                <div class="icon">&#128197;</div>
                <div class="lbl">Absensi & Cuti</div>
                <div class="status status-soon">Segera</div>
            </div>
            <div class="module-item">
                <div class="icon">&#128181;</div>
                <div class="lbl">Penggajian</div>
                <div class="status status-soon">Segera</div>
            </div>
            <div class="module-item">
                <div class="icon">&#128196;</div>
                <div class="lbl">Administrasi HR</div>
                <div class="status status-soon">Segera</div>
            </div>
        </div>
    </div>

    <!-- Features -->
    <div class="features">
        <h2>Fitur yang Tersedia</h2>
        <p class="sub">Modul kinerja karyawan — bagian pertama dari ekosistem HRIS PT. Pro Energi</p>

        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon">&#128101;</div>
                <h4>Manajemen Karyawan</h4>
                <p>Data karyawan terpusat per departemen, level jabatan, dan status kepegawaian — siap jadi fondasi HRIS.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">&#10003;</div>
                <h4>Alur Persetujuan Dinamis</h4>
                <p>Konfigurasi approver per departemen. Setiap departemen bisa memiliki alur persetujuan yang berbeda.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">&#128196;</div>
                <h4>Dokumen Resmi Digital</h4>
                <p>Hasilkan dokumen HR resmi PT. Pro Energi dalam format PDF dengan satu klik, lengkap dengan riwayat persetujuan.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">&#128202;</div>
                <h4>Dashboard per Peran</h4>
                <p>Tampilan khusus untuk Admin HR, Evaluator, dan Approver — setiap pengguna melihat tugas yang relevan.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">&#128274;</div>
                <h4>Kontrol Akses</h4>
                <p>Manajemen hak akses berbasis peran dan departemen. Data terlindungi sesuai wewenang masing-masing.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">&#127760;</div>
                <h4>Multi Bahasa</h4>
                <p>Tersedia dalam Bahasa Indonesia dan Bahasa Inggris. Pilih sesuai preferensi pengguna.</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        &copy; {{ date('Y') }} <strong>PT. Pro Energi</strong> &nbsp;&mdash;&nbsp;
        SIPRO v1.0 &nbsp;&mdash;&nbsp; Sistem Informasi HR & GA
    </div>

    <script src="{{ asset('graindashboard/js/graindashboard.vendor.js') }}"></script>
</body>
</html>
