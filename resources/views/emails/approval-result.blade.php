<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><style>
  body{font-family:Arial,sans-serif;color:#333;font-size:14px;background:#f9f9f9}
  .wrap{max-width:560px;margin:32px auto;background:#fff;border-radius:8px;border:1px solid #e0e0e0;overflow:hidden}
  .header{background:{{ $approved ? '#16a34a' : '#dc2626' }};color:#fff;padding:20px 28px}
  .header h2{margin:0;font-size:18px}
  .body{padding:24px 28px}
  .field{margin-bottom:10px}
  .label{color:#666;font-size:12px;text-transform:uppercase;letter-spacing:.05em}
  .value{font-weight:600;margin-top:2px}
  .summary{background:{{ $approved ? '#f0fdf4' : '#fef2f2' }};border:1px solid {{ $approved ? '#bbf7d0' : '#fecaca' }};border-radius:6px;padding:12px 16px;margin:16px 0}
  .badge{display:inline-block;background:{{ $approved ? '#16a34a' : '#dc2626' }};color:#fff;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600}
  .btn{display:inline-block;background:#1a3c5e;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:600;margin-top:8px}
  .footer{background:#f4f6fa;padding:14px 28px;font-size:12px;color:#888}
</style></head>
<body>
<div class="wrap">
  <div class="header">
    <h2>{{ $approved ? '✓ Pengajuan Disetujui' : '✕ Pengajuan Ditolak' }}</h2>
  </div>
  <div class="body">
    <p>Halo <strong>{{ $approvable->approvalRequester()?->name ?? '-' }}</strong>,</p>
    <p>
      Pengajuan Anda telah
      <strong>{{ $approved ? 'disetujui' : 'ditolak' }}</strong>.
      <span class="badge">{{ $approved ? 'Disetujui' : 'Ditolak' }}</span>
    </p>
    <div class="summary">
      <div class="label">Ringkasan Pengajuan</div>
      <div class="value">{{ $approvable->approvalSummary() }}</div>
    </div>
    @if(!$approved && $reason)
      <div class="field">
        <div class="label">Alasan Penolakan</div>
        <div class="value">{{ $reason }}</div>
      </div>
    @endif
    <a href="{{ route('dashboard') }}" class="btn">Buka Dashboard</a>
  </div>
  <div class="footer">ProPeople — PT. Pro Energi &nbsp;|&nbsp; Pesan ini dikirim otomatis, jangan dibalas.</div>
</div>
</body>
</html>
