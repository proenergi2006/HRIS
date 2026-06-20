<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><style>
  body{font-family:Arial,sans-serif;color:#333;font-size:14px;background:#f9f9f9}
  .wrap{max-width:560px;margin:32px auto;background:#fff;border-radius:8px;border:1px solid #e0e0e0;overflow:hidden}
  .header{background:#dc2626;color:#fff;padding:20px 28px}
  .header h2{margin:0;font-size:18px}
  .body{padding:24px 28px}
  .field{margin-bottom:10px}
  .label{color:#666;font-size:12px;text-transform:uppercase;letter-spacing:.05em}
  .value{font-weight:600;margin-top:2px}
  .reason{background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:12px 16px;margin:16px 0;color:#991b1b}
  .badge{display:inline-block;background:#dc2626;color:#fff;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600}
  .btn{display:inline-block;background:#1a3c5e;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:600;margin-top:8px}
  .footer{background:#f4f6fa;padding:14px 28px;font-size:12px;color:#888}
</style></head>
<body>
<div class="wrap">
  <div class="header">
    <h2>&#10007; Pengajuan Reimbursement Ditolak</h2>
  </div>
  <div class="body">
    <p>Halo <strong>{{ $reimbursement->user->name }}</strong>,</p>
    <p>Mohon maaf, pengajuan medical reimbursement Anda <strong>ditolak</strong>. <span class="badge">Ditolak</span></p>
    <div class="field">
      <div class="label">Nomor Pengajuan</div>
      <div class="value">{{ $reimbursement->request_number }}</div>
    </div>
    <div class="field">
      <div class="label">Tanggal Pengajuan</div>
      <div class="value">{{ $reimbursement->request_date->format('d M Y') }}</div>
    </div>
    <div class="field">
      <div class="label">Ditolak Oleh</div>
      <div class="value">{{ $reimbursement->approver?->name ?? '-' }}</div>
    </div>
    <div class="field">
      <div class="label">Total Klaim</div>
      <div class="value">Rp {{ number_format($reimbursement->total_claim, 0, ',', '.') }}</div>
    </div>
    @if($reimbursement->rejection_reason)
    <div class="reason">
      <div class="label" style="color:#991b1b">Alasan Penolakan</div>
      <div style="margin-top:4px">{{ $reimbursement->rejection_reason }}</div>
    </div>
    @else
    <div class="reason">
      <div class="label" style="color:#991b1b">Alasan Penolakan</div>
      <div style="margin-top:4px;font-style:italic;color:#b91c1c">Tidak ada alasan yang diberikan.</div>
    </div>
    @endif
    <p style="color:#555;font-size:13px">Silakan hubungi HR atau atasan Anda untuk informasi lebih lanjut. Anda dapat mengajukan kembali setelah melengkapi persyaratan.</p>
    <a href="{{ route('reimbursement.show', $reimbursement) }}" class="btn">Lihat Detail Pengajuan</a>
  </div>
  <div class="footer">SIPRO — PT. Pro Energi &nbsp;|&nbsp; Pesan ini dikirim otomatis, jangan dibalas.</div>
</div>
</body>
</html>
