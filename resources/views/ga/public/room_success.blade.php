<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Berhasil - {{ $room->name }}</title>
  <link rel="stylesheet" href="{{ asset('graindashboard/css/graindashboard.css') }}">
  <style>
    body { background:#f4f6fa; min-height:100vh; display:flex; align-items:center; justify-content:center; }
    .card-success { background:#fff; border-radius:12px; box-shadow:0 2px 16px rgba(0,0,0,.1); padding:2.5rem 2rem; text-align:center; max-width:400px; width:100%; margin:1rem; }
    .icon-wrap { width:72px; height:72px; border-radius:50%; background:#d4edda; display:flex; align-items:center; justify-content:center; margin:0 auto 1.25rem; }
  </style>
</head>
<body>
<div class="card-success">
  <div class="icon-wrap">
    <svg width="36" height="36" fill="none" stroke="#28a745" stroke-width="2.5" viewBox="0 0 24 24">
      <path d="M20 6L9 17l-5-5"/>
    </svg>
  </div>
  <h2 class="h4 font-weight-bold mb-2">Laporan Terkirim!</h2>
  <p class="text-muted mb-3">Laporan kebersihan <strong>{{ $room->name }}</strong> berhasil disimpan. Terima kasih!</p>
  <a href="{{ route('ga.room.scan', $room) }}" class="btn btn-outline-primary btn-sm">Laporan Baru</a>
</div>
</body>
</html>
