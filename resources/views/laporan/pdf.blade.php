<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Rekap {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}</title>
<style>
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a1a; margin: 0; padding: 0; }
  .header { background: #0F2A4A; color: #fff; padding: 16px 20px; margin-bottom: 16px; }
  .header h1 { margin: 0; font-size: 16px; font-weight: bold; }
  .header p  { margin: 4px 0 0; font-size: 10px; opacity: .85; }
  .section-title { font-size: 11px; font-weight: bold; border-bottom: 2px solid #0F2A4A;
                   padding-bottom: 4px; margin: 18px 0 8px; color: #0F2A4A; }
  .card-row { display: table; width: 100%; margin-bottom: 16px; border-collapse: separate; border-spacing: 6px; }
  .card { display: table-cell; background: #f8f9fa; border: 1px solid #dee2e6;
          border-radius: 6px; padding: 10px 12px; width: 30%; vertical-align: top; }
  .card .label { font-size: 9px; color: #6c757d; text-transform: uppercase; letter-spacing: .5px; }
  .card .value { font-size: 16px; font-weight: bold; margin: 4px 0 2px; }
  .card .sub   { font-size: 9px; color: #6c757d; }
  .card .sub.green  { color: #28a745; }
  .card .sub.orange { color: #e8a020; }
  .card .sub.red    { color: #dc3545; }
  table.data { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
  table.data th { background: #0F2A4A; color: #fff; padding: 5px 8px; font-size: 9px;
                  text-align: left; font-weight: bold; }
  table.data td { padding: 4px 8px; border-bottom: 1px solid #e9ecef; font-size: 9.5px; }
  table.data tr.alt { background: #f8f9fa; }
  table.data tfoot td { font-weight: bold; background: #e9ecef; border-top: 2px solid #dee2e6; }
  .text-right { text-align: right; }
  .text-center { text-align: center; }
  .badge-danger  { background:#dc3545; color:#fff; padding:2px 6px; border-radius:4px; font-size:8px; }
  .badge-warning { background:#ffc107; color:#000; padding:2px 6px; border-radius:4px; font-size:8px; }
  .footer { text-align: center; font-size: 9px; color: #adb5bd; margin-top: 24px; border-top: 1px solid #dee2e6; padding-top: 8px; }
</style>
</head>
<body>

<div class="header">
  <h1>Laporan Rekap Bulanan &mdash; PT. Pro Energi</h1>
  <p>Periode: {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }} &nbsp;|&nbsp;
     Dicetak: {{ now()->format('d F Y, H:i') }} WIB</p>
</div>

{{-- Summary Cards --}}
<div class="card-row">
  <div class="card">
    <div class="label">Reimbursement</div>
    <div class="value">{{ $stats['reimb']['total'] }}</div>
    <div class="sub">Total pengajuan</div>
    <div class="sub green">{{ $stats['reimb']['approved'] }} disetujui — Rp {{ number_format($stats['reimb']['amount'],0,',','.') }}</div>
    <div class="sub orange">{{ $stats['reimb']['pending'] }} menunggu</div>
    <div class="sub red">{{ $stats['reimb']['rejected'] }} ditolak</div>
  </div>
  <div class="card">
    <div class="label">Perjalanan Dinas</div>
    <div class="value">{{ $stats['perdin']['total'] }}</div>
    <div class="sub">Total pengajuan</div>
    <div class="sub green">{{ $stats['perdin']['approved'] }} disetujui — Rp {{ number_format($stats['perdin']['amount'],0,',','.') }}</div>
    <div class="sub orange">{{ $stats['perdin']['pending'] }} menunggu</div>
    <div class="sub red">{{ $stats['perdin']['rejected'] }} ditolak</div>
  </div>
  <div class="card">
    <div class="label">Pengaduan (Whistleblower)</div>
    <div class="value">{{ $stats['wb']['total'] }}</div>
    <div class="sub">Total laporan masuk</div>
    <div class="sub red">{{ $stats['wb']['new'] }} belum ditangani</div>
    <div class="sub green">{{ $stats['wb']['resolved'] }} selesai</div>
  </div>
</div>

{{-- Rekap Reimbursement per Karyawan --}}
@if($stats['reimb']['per_user']->isNotEmpty())
<div class="section-title">Reimbursement per Karyawan (Disetujui)</div>
<table class="data">
  <thead>
    <tr>
      <th>#</th>
      <th>Nama Karyawan</th>
      <th class="text-center">Jml Pengajuan</th>
      <th class="text-right">Total Klaim (Rp)</th>
    </tr>
  </thead>
  <tbody>
    @foreach($stats['reimb']['per_user'] as $i => $row)
    <tr class="{{ $loop->even ? 'alt' : '' }}">
      <td>{{ $i + 1 }}</td>
      <td>{{ $row['name'] }}</td>
      <td class="text-center">{{ $row['count'] }}</td>
      <td class="text-right">{{ number_format($row['total'],0,',','.') }}</td>
    </tr>
    @endforeach
  </tbody>
  <tfoot>
    <tr>
      <td colspan="2">Total</td>
      <td class="text-center">{{ $stats['reimb']['approved'] }}</td>
      <td class="text-right">{{ number_format($stats['reimb']['amount'],0,',','.') }}</td>
    </tr>
  </tfoot>
</table>
@endif

{{-- Rekap Perdin per Karyawan --}}
@if($stats['perdin']['per_user']->isNotEmpty())
<div class="section-title">Perjalanan Dinas per Karyawan (Disetujui)</div>
<table class="data">
  <thead>
    <tr>
      <th>#</th>
      <th>Nama Karyawan</th>
      <th class="text-center">Jml Pengajuan</th>
      <th class="text-right">Total Budget (Rp)</th>
    </tr>
  </thead>
  <tbody>
    @foreach($stats['perdin']['per_user'] as $i => $row)
    <tr class="{{ $loop->even ? 'alt' : '' }}">
      <td>{{ $i + 1 }}</td>
      <td>{{ $row['name'] }}</td>
      <td class="text-center">{{ $row['count'] }}</td>
      <td class="text-right">{{ number_format($row['total'],0,',','.') }}</td>
    </tr>
    @endforeach
  </tbody>
  <tfoot>
    <tr>
      <td colspan="2">Total</td>
      <td class="text-center">{{ $stats['perdin']['approved'] }}</td>
      <td class="text-right">{{ number_format($stats['perdin']['amount'],0,',','.') }}</td>
    </tr>
  </tfoot>
</table>
@endif

{{-- Kontrak Karyawan --}}
@if($stats['karyawan']['expired']->isNotEmpty() || $stats['karyawan']['expiring']->isNotEmpty())
<div class="section-title">Status Kontrak Karyawan</div>
<table class="data">
  <thead>
    <tr>
      <th>Nama Karyawan</th>
      <th>Departemen</th>
      <th>Tgl Kontrak Berakhir</th>
      <th class="text-center">Status</th>
    </tr>
  </thead>
  <tbody>
    @foreach($stats['karyawan']['expired'] as $e)
    <tr>
      <td>{{ $e->name }}</td>
      <td>{{ $e->department ?? '-' }}</td>
      <td>{{ $e->contract_end_date->format('d M Y') }}</td>
      <td class="text-center"><span class="badge-danger">Sudah Berakhir</span></td>
    </tr>
    @endforeach
    @foreach($stats['karyawan']['expiring'] as $e)
    <tr class="alt">
      <td>{{ $e->name }}</td>
      <td>{{ $e->department ?? '-' }}</td>
      <td>{{ $e->contract_end_date->format('d M Y') }}</td>
      <td class="text-center"><span class="badge-warning">{{ now()->diffInDays($e->contract_end_date) }} hari lagi</span></td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif

<div class="footer">SIPRO &mdash; PT. Pro Energi &mdash; Dokumen ini digenerate otomatis oleh sistem</div>
</body>
</html>
