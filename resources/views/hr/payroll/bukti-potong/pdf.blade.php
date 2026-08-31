<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Bukti Potong PPh21 {{ $employee->name }} — {{ $year }}</title>
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
  .sign { margin-top: 30px; width: 260px; float: right; font-size: 10px; text-align: center; }
  .sign .sp { height: 55px; }
  .note { margin-top: 70px; font-size: 8.5px; color: #777; clear: both; }
</style>
</head>
<body>

<div style="padding:14px 24px 0;">
  @include('components.pdf-kop', ['company' => $company])
</div>

<div class="header">
  <h1>BUKTI POTONG PPh PASAL 21 — TAHUNAN</h1>
  <p>Tahun Pajak {{ $year }} &nbsp;|&nbsp; Dicetak {{ now()->translatedFormat('d F Y') }}</p>
</div>

<div class="wrap">
  <table class="kv">
    <tr><td class="l">Pemberi Kerja</td><td>: {{ strtoupper($company->name) }}</td></tr>
    <tr><td class="l">NPWP Pemberi Kerja</td><td>: {{ $company->npwp ?: '—' }}</td></tr>
    <tr><td class="l">Alamat</td><td>: {{ $company->address ?: '—' }}</td></tr>
  </table>

  <table class="kv">
    <tr><td class="l">Nama Karyawan</td><td>: {{ $employee->name }}</td></tr>
    <tr><td class="l">NIP</td><td>: {{ $employee->nip ?: '—' }}</td></tr>
    <tr><td class="l">NPWP</td><td>: {{ $employee->npwp_number ?: '— (tidak ber-NPWP)' }}</td></tr>
    <tr><td class="l">Jabatan</td><td>: {{ $employee->position?->name ?: '—' }}</td></tr>
    <tr><td class="l">Status PTKP</td><td>: {{ $data['ptkp_status'] }}</td></tr>
    <tr><td class="l">Masa Perolehan</td><td>: {{ \Carbon\Carbon::create()->month($data['month_from'])->translatedFormat('F') }} s/d {{ \Carbon\Carbon::create()->month($data['month_to'])->translatedFormat('F') }} {{ $year }} ({{ $data['period_count'] }} masa)</td></tr>
  </table>

  <table class="amt">
    <tr><th>Uraian</th><th class="r">Jumlah (Rp)</th></tr>
    <tr><td>Penghasilan Bruto Kena Pajak (setahun)</td><td class="r">{{ number_format($data['bruto_taxable'], 0, ',', '.') }}</td></tr>
    @if($data['bonus_tax'] > 0)
    <tr><td>PPh21 atas Bonus / Insentif</td><td class="r">{{ number_format($data['bonus_tax'], 0, ',', '.') }}</td></tr>
    @endif
    <tr class="total"><td>PPh Pasal 21 Yang Telah Dipotong (setahun)</td><td class="r">{{ number_format($data['pph21'], 0, ',', '.') }}</td></tr>
  </table>

  <div class="sign">
    <div>{{ $company->address ? '' : '' }}{{ now()->translatedFormat('d F Y') }}</div>
    <div>{{ strtoupper($company->name) }}</div>
    <div class="sp"></div>
    <div><strong>{{ $company->tax_signer_name ?: '(...................................)' }}</strong></div>
    @if($company->tax_signer_npwp)<div>NPWP: {{ $company->tax_signer_npwp }}</div>@endif
  </div>

  <div class="note">
    Dokumen ini adalah ringkasan internal untuk arsip &amp; referensi karyawan. Untuk pelaporan SPT Tahunan
    gunakan Bukti Potong 1721-A1 resmi yang diterbitkan lewat e-Bupot DJP. Digenerate oleh ProPeople.
  </div>
</div>
</body>
</html>
