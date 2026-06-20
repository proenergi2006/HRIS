<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><style>
  body{font-family:Arial,sans-serif;color:#333;font-size:14px;background:#f9f9f9}
  .wrap{max-width:560px;margin:32px auto;background:#fff;border-radius:8px;border:1px solid #e0e0e0;overflow:hidden}
  .header{background:#16a34a;color:#fff;padding:20px 28px}
  .header h2{margin:0;font-size:18px}
  .body{padding:24px 28px}
  .field{margin-bottom:10px}
  .label{color:#666;font-size:12px;text-transform:uppercase;letter-spacing:.05em}
  .value{font-weight:600;margin-top:2px}
  .total{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:12px 16px;margin:16px 0}
  .badge{display:inline-block;background:#16a34a;color:#fff;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600}
  .btn{display:inline-block;background:#1a3c5e;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:600;margin-top:8px}
  .footer{background:#f4f6fa;padding:14px 28px;font-size:12px;color:#888}
</style></head>
<body>
<div class="wrap">
  <div class="header">
    <h2>&#10003; Pengajuan Reimbursement Disetujui</h2>
  </div>
  <div class="body">
    <p>Halo <strong>{{ $reimbursement->user->name }}</strong>,</p>
    <p>Pengajuan medical reimbursement Anda telah <strong>disetujui</strong>. <span class="badge">Disetujui</span></p>
    <div class="field">
      <div class="label">Nomor Pengajuan</div>
      <div class="value">{{ $reimbursement->request_number }}</div>
    </div>
    <div class="field">
      <div class="label">Tanggal Pengajuan</div>
      <div class="value">{{ $reimbursement->request_date->format('d M Y') }}</div>
    </div>
    <div class="field">
      <div class="label">Disetujui Oleh</div>
      <div class="value">{{ $reimbursement->approver?->name ?? '-' }}</div>
    </div>
    <div class="field">
      <div class="label">Tanggal Persetujuan</div>
      <div class="value">{{ $reimbursement->approved_at?->format('d M Y, H:i') }}</div>
    </div>
    <div class="total">
      <div class="label">Total Klaim Disetujui</div>
      <div class="value" style="font-size:20px;color:#16a34a">Rp {{ number_format($reimbursement->total_claim, 0, ',', '.') }}</div>
    </div>
    <p style="color:#555;font-size:13px">Pembayaran akan diproses sesuai jadwal penggajian perusahaan. Hubungi HR jika ada pertanyaan.</p>
    <a href="{{ route('reimbursement.show', $reimbursement) }}" class="btn">Lihat Detail Pengajuan</a>
  </div>
  <div class="footer">SIPRO — PT. Pro Energi &nbsp;|&nbsp; Pesan ini dikirim otomatis, jangan dibalas.</div>
</div>
</body>
</html>
