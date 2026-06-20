<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; font-size: 9pt; color: #111; }
  .page { padding: 12mm 12mm 10mm; }
  .header { text-align: center; margin-bottom: 8px; border-bottom: 2px solid #000; padding-bottom: 6px; }
  .header h1 { font-size: 13pt; font-weight: bold; }
  .header h2 { font-size: 10pt; font-weight: normal; }
  .header .subtitle { font-size: 11pt; font-weight: bold; margin-top: 4px; text-transform: uppercase; letter-spacing: .05em; }
  .info-grid { display: table; width: 100%; margin-bottom: 8px; }
  .info-row  { display: table-row; }
  .info-label, .info-value { display: table-cell; padding: 2px 4px; font-size: 8.5pt; }
  .info-label { width: 140px; }
  .info-sep   { display: table-cell; width: 10px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
  th, td { border: 1px solid #555; padding: 3px 5px; font-size: 7.5pt; vertical-align: middle; }
  th { background: #e8e8e8; font-weight: bold; text-align: center; }
  td.num { text-align: right; }
  td.ctr { text-align: center; }
  .total-row td { font-weight: bold; background: #f0f0f0; }
  .sign-section { display: table; width: 100%; margin-top: 10px; }
  .sign-box { display: table-cell; text-align: center; width: 33%; padding: 0 8px; }
  .sign-line { border-top: 1px solid #000; margin-top: 40px; padding-top: 3px; font-size: 8pt; }
  .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-weight: bold; font-size: 8pt; }
  .badge-success  { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
  .badge-warning  { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
  .badge-secondary{ background: #e2e3e5; color: #383d41; border: 1px solid #d6d8db; }
  .badge-danger   { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
</style>
</head>
<body>
<div class="page">

  <div class="header">
    <h1>PT. PRO ENERGI</h1>
    <h2>Integrated Energy Solutions</h2>
    <div class="subtitle">Formulir Pengajuan Medical Reimbursement</div>
  </div>

  <div class="info-grid">
    <div class="info-row">
      <div class="info-label">No. Pengajuan</div><div class="info-sep">:</div>
      <div class="info-value"><strong>{{ $reimbursement->request_number }}</strong></div>
      <div class="info-label" style="padding-left:20px">Status</div><div class="info-sep">:</div>
      <div class="info-value">
        <span class="badge badge-{{ \App\Models\Reimbursement\ReimbursementRequest::$statusBadges[$reimbursement->status] }}">
          {{ \App\Models\Reimbursement\ReimbursementRequest::$statusLabels[$reimbursement->status] }}
        </span>
      </div>
    </div>
    <div class="info-row">
      <div class="info-label">Nama Karyawan</div><div class="info-sep">:</div>
      <div class="info-value"><strong>{{ $reimbursement->user->name }}</strong></div>
      <div class="info-label" style="padding-left:20px">Tanggal Pengajuan</div><div class="info-sep">:</div>
      <div class="info-value">{{ $reimbursement->request_date->format('d M Y') }}</div>
    </div>
    <div class="info-row">
      <div class="info-label">Pengobatan Untuk</div><div class="info-sep">:</div>
      <div class="info-value">{{ \App\Models\Reimbursement\ReimbursementRequest::$medicalForLabels[$reimbursement->medical_for] }}</div>
      <div class="info-label" style="padding-left:20px">Status Pernikahan</div><div class="info-sep">:</div>
      <div class="info-value">{{ $reimbursement->marital_status === 'married' ? 'Menikah' : 'Lajang' }}</div>
    </div>
    @if($reimbursement->notes)
    <div class="info-row">
      <div class="info-label">Catatan</div><div class="info-sep">:</div>
      <div class="info-value" colspan="4">{{ $reimbursement->notes }}</div>
    </div>
    @endif
  </div>

  @php $amtFields = \App\Models\Reimbursement\ReimbursementItem::AMOUNT_FIELDS; @endphp
  <table>
    <thead>
      <tr>
        <th style="width:20px">No</th>
        <th style="width:90px">Nama Pasien</th>
        <th style="width:65px">Tgl Berobat</th>
        <th style="width:100px">Faskes / RS</th>
        <th style="width:80px">Diagnosa</th>
        @foreach($amtFields as $lbl)
          <th style="min-width:52px">{{ $lbl }}</th>
        @endforeach
        <th style="min-width:65px">Total</th>
      </tr>
    </thead>
    <tbody>
    @foreach($reimbursement->items as $i => $item)
      <tr>
        <td class="ctr">{{ $i + 1 }}</td>
        <td>{{ $item->patient_name }}</td>
        <td class="ctr">{{ $item->treatment_date->format('d/m/Y') }}</td>
        <td>{{ $item->institution }}</td>
        <td>{{ $item->diagnose ?? '-' }}</td>
        @foreach(array_keys($amtFields) as $field)
          <td class="num">{{ $item->$field > 0 ? number_format($item->$field, 0, ',', '.') : '-' }}</td>
        @endforeach
        <td class="num"><strong>{{ number_format($item->total_claim, 0, ',', '.') }}</strong></td>
      </tr>
    @endforeach
    </tbody>
    <tfoot>
      <tr class="total-row">
        <td colspan="5" class="num">TOTAL</td>
        @foreach(array_keys($amtFields) as $field)
          @php $s = $reimbursement->items->sum($field) @endphp
          <td class="num">{{ $s > 0 ? number_format($s, 0, ',', '.') : '-' }}</td>
        @endforeach
        <td class="num">Rp {{ number_format($reimbursement->total_claim, 0, ',', '.') }}</td>
      </tr>
    </tfoot>
  </table>

  @if($reimbursement->isRejected() && $reimbursement->rejection_reason)
  <p style="font-size:8pt;color:#721c24;margin-bottom:8px">
    <strong>Alasan Penolakan:</strong> {{ $reimbursement->rejection_reason }}
  </p>
  @endif

  <div class="sign-section">
    <div class="sign-box">
      <div class="sign-line">
        Pemohon<br><strong>{{ $reimbursement->user->name }}</strong>
      </div>
    </div>
    <div class="sign-box">
      <div class="sign-line">
        Diperiksa oleh<br>&nbsp;
      </div>
    </div>
    <div class="sign-box">
      <div class="sign-line">
        Disetujui oleh<br>
        <strong>{{ $reimbursement->approver?->name ?? '____________________' }}</strong>
        @if($reimbursement->approved_at)
          <br><small>{{ $reimbursement->approved_at->format('d M Y') }}</small>
        @endif
      </div>
    </div>
  </div>

</div>
</body>
</html>
