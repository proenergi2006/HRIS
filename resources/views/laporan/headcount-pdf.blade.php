<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Headcount</title>
<style>
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a1a; margin: 0; padding: 0; }
  .header { background: #0F2A4A; color: #fff; padding: 16px 20px; margin-bottom: 16px; }
  .header h1 { margin: 0; font-size: 16px; font-weight: bold; }
  .header p  { margin: 4px 0 0; font-size: 10px; opacity: .85; }
  .section-title { font-size: 11px; font-weight: bold; border-bottom: 2px solid #0F2A4A; padding-bottom: 4px; margin: 16px 0 8px; color: #0F2A4A; }
  .card-row { display: table; width: 100%; margin-bottom: 16px; border-collapse: separate; border-spacing: 6px; }
  .card { display: table-cell; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 10px 12px; width: 25%; vertical-align: top; }
  .card .label { font-size: 9px; color: #6c757d; text-transform: uppercase; letter-spacing: .5px; }
  .card .value { font-size: 18px; font-weight: bold; margin: 4px 0 2px; }
  table.data { width: 48%; border-collapse: collapse; margin-bottom: 12px; float: left; margin-right: 4%; }
  table.data th { background: #0F2A4A; color: #fff; padding: 5px 8px; font-size: 9px; text-align: left; font-weight: bold; }
  table.data td { padding: 4px 8px; border-bottom: 1px solid #e9ecef; font-size: 9.5px; }
  table.data td.n { text-align: right; font-weight: bold; }
  .clear { clear: both; }
  .footer { text-align: center; font-size: 9px; color: #adb5bd; margin-top: 20px; border-top: 1px solid #dee2e6; padding-top: 8px; }
</style>
</head>
<body>
<div style="padding:14px 20px 0;">
  @include('components.pdf-kop', ['company' => $company])
</div>
<div class="header">
  <h1>Laporan Headcount</h1>
  <p>Per {{ now()->format('d F Y, H:i') }} WIB &nbsp;|&nbsp; Karyawan aktif</p>
</div>

<div class="card-row">
  <div class="card"><div class="label">{{ $company ? 'Headcount PT' : 'Headcount Grup' }}</div><div class="value">{{ $data['total'] }}</div></div>
  <div class="card"><div class="label">Total Grup</div><div class="value">{{ $data['group_total'] }}</div></div>
  <div class="card"><div class="label">Baru Bln Ini</div><div class="value">{{ $data['new_this_month'] }}</div></div>
  <div class="card"><div class="label">Kontrak Habis &le;2bln</div><div class="value">{{ $data['contract_ending'] }}</div></div>
</div>

@php
  $blocks = [
    'Status Kepegawaian' => $data['by_status'],
    'Tipe Karyawan'      => $data['by_type'],
    'Jenis Kelamin'      => $data['by_gender'],
    'Level Jabatan'      => $data['by_level'],
    'Departemen'         => $data['by_department'],
    'Divisi'             => $data['by_division'],
    'Section'            => $data['by_section'],
  ];
  if ($data['by_company']->isNotEmpty()) $blocks = ['Perusahaan' => $data['by_company']] + $blocks;
@endphp

@foreach($blocks as $title => $map)
  @continue($map->isEmpty())
  <table class="data">
    <thead><tr><th>{{ $title }}</th><th style="text-align:right">Headcount</th></tr></thead>
    <tbody>
      @foreach($map as $k => $v)<tr><td>{{ $k }}</td><td class="n">{{ $v }}</td></tr>@endforeach
    </tbody>
  </table>
@endforeach
<div class="clear"></div>

<div class="footer">ProPeople &mdash; PT. Pro Energi &mdash; Dokumen ini digenerate otomatis oleh sistem</div>
</body>
</html>
