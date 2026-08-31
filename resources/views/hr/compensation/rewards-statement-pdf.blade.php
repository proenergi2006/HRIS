<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Total Rewards Statement {{ $employee->name }} — {{ $year }}</title>
<style>
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 10.5px; color: #1a1a1a; margin: 0; padding: 0; }
  .header { text-align: center; border-bottom: 2px solid #0F2A4A; padding: 10px 0; margin-bottom: 4px; }
  .header h1 { margin: 0; font-size: 14px; letter-spacing: .5px; }
  .header p { margin: 2px 0 0; font-size: 9px; }
  .wrap { padding: 6px 26px 26px; }
  table.kv { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
  table.kv td { padding: 3px 4px; font-size: 10px; vertical-align: top; }
  table.kv td.l { width: 150px; color: #555; }
  table.amt { width: 100%; border-collapse: collapse; margin-top: 8px; }
  table.amt td, table.amt th { padding: 6px 8px; border: 1px solid #ccc; font-size: 10.5px; }
  table.amt th { background: #f1f3f6; text-align: left; }
  table.amt .r { text-align: right; }
  .total { font-weight: bold; background: #0F2A4A; color: #fff; }
  .note { margin-top: 24px; font-size: 8.5px; color: #777; }
  .sub { margin-top: 18px; font-size: 11px; font-weight: bold; color: #0F2A4A; }
</style>
</head>
<body>

<div style="padding:14px 24px 0;">
  @include('components.pdf-kop', ['company' => $employee->company])
</div>

<div class="header">
  <h1>TOTAL REWARDS STATEMENT</h1>
  <p>Tahun {{ $year }} &nbsp;|&nbsp; Dicetak {{ now()->translatedFormat('d F Y') }}</p>
</div>

<div class="wrap">
  <table class="kv">
    <tr><td class="l">Nama Karyawan</td><td>: {{ $employee->name }}</td></tr>
    <tr><td class="l">NIP</td><td>: {{ $employee->nip ?: '—' }}</td></tr>
    <tr><td class="l">Jabatan</td><td>: {{ $employee->position?->name ?: '—' }}</td></tr>
    <tr><td class="l">Departemen</td><td>: {{ $employee->department?->name ?: '—' }}</td></tr>
  </table>

  <table class="amt">
    <tr><th>Komponen Kompensasi Tunai</th><th class="r">Jumlah (Rp)</th></tr>
    <tr><td>Gaji &amp; Tunjangan Tetap ({{ $monthsCounted }} periode dibayarkan)</td><td class="r">{{ number_format($cashCompYtd,0,',','.') }}</td></tr>
    <tr><td>THR</td><td class="r">{{ number_format($thrYtd,0,',','.') }}</td></tr>
    <tr><td>Bonus / Insentif</td><td class="r">{{ number_format($bonusYtd,0,',','.') }}</td></tr>
    <tr class="total"><td>Total Kompensasi Tunai {{ $year }}</td><td class="r">{{ number_format($totalCash,0,',','.') }}</td></tr>
  </table>

  <div class="sub">Pengembangan Diri</div>
  <table class="amt">
    <tr><td>Program Training Diselesaikan Tahun {{ $year }}</td><td class="r">{{ $trainingCount }}</td></tr>
  </table>

  <div class="note">
    Seluruh angka dihitung dari data payroll/THR/bonus periode yang sudah ditutup (closed) hingga tanggal
    cetak — bukan proyeksi/estimasi setahun penuh. Dokumen ini bersifat informasi internal, bukan bukti
    potong pajak resmi. Digenerate oleh ProPeople.
  </div>
</div>
</body>
</html>
