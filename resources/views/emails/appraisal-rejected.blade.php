<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><style>
  body{font-family:Arial,sans-serif;color:#333;font-size:14px;background:#f9f9f9}
  .wrap{max-width:560px;margin:32px auto;background:#fff;border-radius:8px;border:1px solid #e0e0e0;overflow:hidden}
  .header{background:#9f1239;color:#fff;padding:20px 28px}
  .header h2{margin:0;font-size:18px}
  .body{padding:24px 28px}
  .field{margin-bottom:10px}
  .label{color:#666;font-size:12px;text-transform:uppercase;letter-spacing:.05em}
  .value{font-weight:600;margin-top:2px}
  .reason{background:#fff1f2;border:1px solid #fecdd3;border-radius:6px;padding:12px 16px;margin:16px 0}
  .btn{display:inline-block;background:#9f1239;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:600;margin-top:8px}
  .footer{background:#f4f6fa;padding:14px 28px;font-size:12px;color:#888}
</style></head>
<body>
<div class="wrap">
  <div class="header">
    <h2>Penilaian Dikembalikan untuk Diperbaiki</h2>
  </div>
  <div class="body">
    <p>Penilaian kinerja berikut dikembalikan oleh approver dan perlu diperbaiki sebelum disubmit ulang.</p>
    <div class="field">
      <div class="label">Karyawan</div>
      <div class="value">{{ $appraisal->employee?->name ?? '-' }}</div>
    </div>
    <div class="field">
      <div class="label">Periode</div>
      <div class="value">{{ $appraisal->period?->name ?? '-' }}</div>
    </div>
    @if($notes)
    <div class="reason">
      <div class="label">Catatan dari Approver</div>
      <div style="margin-top:4px;white-space:pre-wrap">{{ $notes }}</div>
    </div>
    @endif
    <a href="{{ route('appraisal.appraisals.edit', $appraisal) }}" class="btn">Perbaiki Penilaian</a>
  </div>
  <div class="footer">SIPRO — PT. Pro Energi &nbsp;|&nbsp; Pesan ini dikirim otomatis, jangan dibalas.</div>
</div>
</body>
</html>
