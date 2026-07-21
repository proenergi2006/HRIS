<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Berangkas {{ $vault->name }}</title>
  <link rel="stylesheet" href="{{ asset('graindashboard/css/graindashboard.css') }}">
  <style>
    body { background: #f4f6fa; min-height: 100vh; }
    .hero { background: linear-gradient(135deg, #1a3c5e 0%, #2e6da4 100%); color:#fff; padding: 2rem 1rem 1.5rem; text-align:center; }
    .hero h1 { font-size: 1.3rem; font-weight: 700; margin-bottom: .25rem; }
    .hero .code { display:inline-block;background:#fff;color:#1a3c5e;border-radius:8px;padding:4px 16px;font-size:1rem;font-weight:800;letter-spacing:.1em;margin-top:6px }
    .list-wrap { max-width: 560px; margin: 1.5rem auto 2rem; padding: 0 1rem; }
    .doc-card { background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,.08); padding: 1rem 1.1rem; margin-bottom: .85rem; display:flex; justify-content:space-between; align-items:center; gap: .75rem; }
    .doc-card .code { font-size:.75rem; color:#6b7280; letter-spacing:.06em; margin-bottom:2px; }
    .doc-card .detail { font-size:.88rem; color:#212529; }
    .status-pill{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:999px;font-weight:700;font-size:11px;white-space:nowrap}
    .status-pill.available{background:#dcfce7;color:#166534}
    .status-pill.taken{background:#fef3c7;color:#92400e}
    .empty { text-align:center; color:#9ca3af; padding: 2.5rem 1rem; }
    footer { text-align:center; font-size:.8rem; color:#adb5bd; padding:1rem 0 2rem; }
  </style>
</head>
<body>

<div class="hero">
  <div style="display:inline-block;background:#fff;border-radius:10px;padding:7px 18px;margin-bottom:12px;">
    <img src="/img/logo-proenergi.png" alt="PT. Pro Energi" style="height:36px;object-fit:contain;display:block;">
  </div>
  <h1>{{ $vault->name }}</h1>
  <div class="code">{{ $vault->barcode }}</div>
</div>

<div class="list-wrap">
  @if($documents->isEmpty())
    <div class="empty">Belum ada dokumen terdaftar di berangkas ini.</div>
  @else
    @foreach($documents as $d)
      <a href="{{ route('ga.vault.document', [$vault, $d]) }}" class="doc-card" style="text-decoration:none;color:inherit">
        <div>
          <div class="code">{{ $d->barcode }}</div>
          <div class="detail">{{ \Illuminate\Support\Str::limit($d->detail, 70) }}</div>
        </div>
        @if($d->isOut())
          <span class="status-pill taken">Diambil</span>
        @else
          <span class="status-pill available">Di Brankas</span>
        @endif
      </a>
    @endforeach
  @endif
</div>

<footer>SIPRO &mdash; PT. Pro Energi</footer>
</body>
</html>
