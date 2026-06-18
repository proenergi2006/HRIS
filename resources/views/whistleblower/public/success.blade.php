<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Terkirim — PT. Pro Energi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:   #0f2a4a;
            --navy-2: #1a3f6f;
            --accent: #e8a020;
            --text:   #1e293b;
            --muted:  #64748b;
            --border: #e2e8f0;
            --bg:     #f1f5f9;
            --white:  #ffffff;
            --success:#16a34a;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .hero {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
            padding: 36px 20px 88px;
            text-align: center;
        }
        .hero p { color: rgba(255,255,255,.7); font-size: .9rem; margin-top: 6px; }
        .hero h1 { font-size: 1.3rem; font-weight: 700; color: #fff; }

        .card-wrap {
            max-width: 560px;
            margin: -56px auto 48px;
            padding: 0 16px;
            flex: 1;
        }

        .card {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 4px 24px rgba(0,0,0,.10);
            overflow: hidden;
            text-align: center;
        }

        /* animated checkmark */
        .success-top {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            padding: 48px 32px 36px;
            position: relative;
        }
        .check-circle {
            width: 80px;
            height: 80px;
            background: var(--success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 8px 24px rgba(22,163,74,.3);
            animation: popIn .4s cubic-bezier(.175,.885,.32,1.275) both;
        }
        @keyframes popIn {
            0%   { transform: scale(0); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .check-circle svg { width: 40px; height: 40px; fill: #fff; }

        .success-top h2 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #14532d;
            margin-bottom: 8px;
        }
        .success-top p { font-size: .9rem; color: #166534; line-height: 1.6; max-width: 340px; margin: 0 auto; }

        .card-body { padding: 32px; }

        /* Ticket */
        .ticket-label { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: var(--muted); margin-bottom: 10px; }
        .ticket-box {
            background: var(--bg);
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 20px 24px;
            display: inline-block;
            margin-bottom: 8px;
            min-width: 220px;
        }
        .ticket-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--navy);
            letter-spacing: 3px;
            font-variant-numeric: tabular-nums;
        }
        .ticket-hint { font-size: .8rem; color: var(--muted); margin-bottom: 28px; }

        /* Steps */
        .steps { text-align: left; background: #f8fafc; border-radius: 12px; padding: 20px; margin-bottom: 28px; }
        .steps-title { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: var(--muted); margin-bottom: 14px; }
        .step-item { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
        .step-item:last-child { margin-bottom: 0; }
        .step-num {
            width: 24px; height: 24px;
            background: var(--navy);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .7rem; font-weight: 700; color: #fff;
            flex-shrink: 0; margin-top: 1px;
        }
        .step-text { font-size: .85rem; color: var(--text); line-height: 1.5; }
        .step-text strong { display: block; margin-bottom: 1px; }

        /* Button */
        .btn-new {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 28px;
            background: var(--navy);
            color: #fff;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: .9rem;
            transition: all .18s;
            box-shadow: 0 4px 14px rgba(15,42,74,.25);
        }
        .btn-new:hover { background: var(--navy-2); transform: translateY(-1px); box-shadow: 0 6px 18px rgba(15,42,74,.3); }
        .btn-new svg { width: 16px; height: 16px; fill: currentColor; }

        .page-footer {
            text-align: center;
            padding: 0 16px 40px;
            font-size: .78rem;
            color: #94a3b8;
            line-height: 1.7;
        }
    </style>
</head>
<body>

<div class="hero">
    <h1>Saluran Pengaduan & Pelaporan</h1>
    <p>PT. Pro Energi &mdash; Whistleblower System</p>
</div>

<div class="card-wrap">
    <div class="card">
        <div class="success-top">
            <div class="check-circle">
                <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            </div>
            <h2>Laporan Berhasil Dikirim!</h2>
            <p>Terima kasih. Laporan Anda telah kami terima dan akan segera ditindaklanjuti secara profesional dan rahasia.</p>
        </div>

        <div class="card-body">
            <div class="ticket-label">Nomor Tiket Laporan Anda</div>
            <div class="ticket-box">
                <div class="ticket-number">{{ $ticket }}</div>
            </div>
            <div class="ticket-hint">Simpan nomor ini untuk keperluan tindak lanjut.</div>

            <div class="steps">
                <div class="steps-title">Proses Selanjutnya</div>
                <div class="step-item">
                    <div class="step-num">1</div>
                    <div class="step-text">
                        <strong>Verifikasi Laporan</strong>
                        Tim HRD akan memverifikasi kelengkapan laporan Anda.
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-num">2</div>
                    <div class="step-text">
                        <strong>Investigasi Internal</strong>
                        Laporan ditindaklanjuti secara rahasia oleh tim yang berwenang.
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-num">3</div>
                    <div class="step-text">
                        <strong>Penyelesaian</strong>
                        Tindakan korektif diambil sesuai kebijakan perusahaan.
                    </div>
                </div>
            </div>

            <a href="{{ route('whistleblower.form') }}" class="btn-new">
                <svg viewBox="0 0 24 24"><path d="M19 13H13v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                Buat Laporan Baru
            </a>
        </div>
    </div>
</div>

<div class="page-footer">
    &copy; {{ date('Y') }} PT. Pro Energi &mdash; Whistleblower System<br>
    Laporan diproses secara rahasia &bull; Keamanan data terjamin
</div>

</body>
</html>
