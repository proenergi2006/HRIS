<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><style>
  body{font-family:Arial,sans-serif;color:#333;font-size:14px;background:#f9f9f9}
  .wrap{max-width:560px;margin:32px auto;background:#fff;border-radius:8px;border:1px solid #e0e0e0;overflow:hidden}
  .header{background:#1a3c5e;color:#fff;padding:20px 28px}
  .header h2{margin:0;font-size:18px}
  .body{padding:24px 28px}
  .field{margin-bottom:10px}
  .label{color:#666;font-size:12px;text-transform:uppercase;letter-spacing:.05em}
  .value{font-weight:600;margin-top:2px}
  .total{background:#f0f9ff;border:1px solid #bae6fd;border-radius:6px;padding:12px 16px;margin:16px 0}
  .btn{display:inline-block;background:#1a3c5e;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:600;margin-top:8px}
  .footer{background:#f4f6fa;padding:14px 28px;font-size:12px;color:#888}
</style></head>
<body>
<div class="wrap">
  <div class="header">
    <h2>Pengajuan Reimbursement Baru</h2>
  </div>
  <div class="body">
    <p>Ada pengajuan medical reimbursement baru yang menunggu persetujuan Anda.</p>
    <div class="field">
      <div class="label">Nomor Pengajuan</div>
      <div class="value">{{ $reimbursement->request_number }}</div>
    </div>
    <div class="field">
      <div class="label">Pemohon</div>
      <div class="value">{{ $reimbursement->user->name }}</div>
    </div>
    <div class="field">
      <div class="label">Tanggal Pengajuan</div>
      <div class="value">{{ $reimbursement->request_date->format('d M Y') }}</div>
    </div>
    <div class="field">
      <div class="label">Untuk</div>
      <div class="value">{{ \App\Models\Reimbursement\ReimbursementRequest::$medicalForLabels[$reimbursement->medical_for] }}</div>
    </div>
    <div class="total">
      <div class="label">Total Klaim</div>
      <div class="value" style="font-size:18px;color:#1a3c5e">Rp {{ number_format($reimbursement->total_claim, 0, ',', '.') }}</div>
    </div>
    <a href="{{ route('reimbursement.admin.show', $reimbursement) }}" class="btn">Lihat & Proses Pengajuan</a>
  </div>
  <div class="footer">SIPRO — PT. Pro Energi &nbsp;|&nbsp; Pesan ini dikirim otomatis, jangan dibalas.</div>
</div>
</body>
</html>
