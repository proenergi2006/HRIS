<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><style>
  body{font-family:Arial,sans-serif;color:#333;font-size:14px;background:#f9f9f9}
  .wrap{max-width:560px;margin:32px auto;background:#fff;border-radius:8px;border:1px solid #e0e0e0;overflow:hidden}
  .header{color:#fff;padding:20px 28px}
  .header h2{margin:0;font-size:18px}
  .body{padding:24px 28px}
  .field{margin-bottom:10px}
  .label{color:#666;font-size:12px;text-transform:uppercase;letter-spacing:.05em}
  .value{font-weight:600;margin-top:2px}
  .note{background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:12px 16px;margin:16px 0}
  .btn{display:inline-block;background:#1a1a2e;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:600;margin-top:8px}
  .footer{background:#f4f6fa;padding:14px 28px;font-size:12px;color:#888}
</style></head>
<body>
<div class="wrap">
  <div class="header" style="background:{{ $approved ? '#15803d' : '#b91c1c' }}">
    <h2>Permohonan Perjalanan Dinas {{ $approved ? 'Disetujui' : 'Ditolak' }}</h2>
  </div>
  <div class="body">
    <p>Yth. {{ $perdin->user->name }},</p>
    @if($approved)
      <p>Permohonan perjalanan dinas Anda telah <strong>disetujui sepenuhnya</strong>.
         Total anggaran telah dipotong dari saldo perjalanan dinas Anda.</p>
    @else
      <p>Mohon maaf, permohonan perjalanan dinas Anda <strong>ditolak</strong>.</p>
    @endif
    <div class="field">
      <div class="label">Nomor Advance</div>
      <div class="value">{{ $perdin->no_advance }}</div>
    </div>
    <div class="field">
      <div class="label">Tujuan</div>
      <div class="value">{{ $perdin->destination }}</div>
    </div>
    <div class="field">
      <div class="label">Total Anggaran</div>
      <div class="value">Rp {{ number_format($perdin->total_budget, 0, ',', '.') }}</div>
    </div>
    @if(!$approved && $perdin->notes_rejection)
    <div class="note">
      <div class="label">Alasan Penolakan</div>
      <div class="value">{{ $perdin->notes_rejection }}</div>
    </div>
    @endif
    <a href="{{ route('perdin.show', $perdin) }}" class="btn">Lihat Detail</a>
  </div>
  <div class="footer">SIPRO — PT. Pro Energi &nbsp;|&nbsp; Pesan ini dikirim otomatis, jangan dibalas.</div>
</div>
</body>
</html>
