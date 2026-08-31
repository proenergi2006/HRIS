<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Analisis Gap Kompetensi</title>
<style>
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a1a; margin: 0; padding: 0; }
  .header { background: #0F2A4A; color: #fff; padding: 16px 20px; margin-bottom: 16px; }
  .header h1 { margin: 0; font-size: 16px; font-weight: bold; }
  .header p  { margin: 4px 0 0; font-size: 10px; opacity: .85; }
  .section-title { font-size: 11px; font-weight: bold; border-bottom: 2px solid #0F2A4A; padding-bottom: 4px; margin: 18px 0 8px; color: #0F2A4A; }
  .card-row { display: table; width: 100%; margin-bottom: 16px; border-collapse: separate; border-spacing: 6px; }
  .card { display: table-cell; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 10px 12px; width: 33%; vertical-align: top; }
  .card .label { font-size: 9px; color: #6c757d; text-transform: uppercase; letter-spacing: .5px; }
  .card .value { font-size: 16px; font-weight: bold; margin: 4px 0 2px; }
  table.data { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
  table.data th { background: #0F2A4A; color: #fff; padding: 5px 7px; font-size: 9px; text-align: left; font-weight: bold; }
  table.data td { padding: 4px 7px; border-bottom: 1px solid #e9ecef; font-size: 9px; }
  table.data tr.alt { background: #f8f9fa; }
  table.data tr.gap td { background: #fff6e5; }
  .text-center { text-align: center; }
  .footer { text-align: center; font-size: 9px; color: #adb5bd; margin-top: 24px; border-top: 1px solid #dee2e6; padding-top: 8px; }
</style>
</head>
<body>
<div style="padding:14px 20px 0;">
  @include('components.pdf-kop', ['company' => $company, 'groupLabel' => 'Semua Perusahaan'])
</div>
<div class="header">
  <h1>Analisis Gap Kompetensi</h1>
  <p>Dicetak: {{ now()->format('d F Y, H:i') }} WIB</p>
</div>

<div class="card-row">
  <div class="card"><div class="label">Karyawan Dianalisis</div><div class="value">{{ $data['employees_total'] }}</div></div>
  <div class="card"><div class="label">Karyawan dengan Gap</div><div class="value">{{ $data['employees_w_gap'] }}</div></div>
  <div class="card"><div class="label">Total Gap Kompetensi</div><div class="value">{{ $data['gap_count'] }}</div></div>
</div>

@if(!empty($data['per_competency']))
<div class="section-title">Kompetensi Paling Sering Gap</div>
<table class="data">
  <thead><tr><th>Kompetensi</th><th class="text-center">Jml Karyawan Gap</th></tr></thead>
  <tbody>
    @foreach($data['per_competency'] as $name => $count)
    <tr class="{{ $loop->even ? 'alt' : '' }}"><td>{{ $name }}</td><td class="text-center">{{ $count }}</td></tr>
    @endforeach
  </tbody>
</table>
@endif

<div class="section-title">Detail Gap per Karyawan</div>
@if(empty($data['rows']))
  <p>Tidak ada jabatan dengan profil kompetensi.</p>
@else
<table class="data">
  <thead><tr><th>#</th><th>Karyawan</th><th>Departemen</th><th>Kompetensi</th><th class="text-center">Wajib</th><th class="text-center">Aktual</th><th class="text-center">Gap</th><th>Rekomendasi Training</th></tr></thead>
  <tbody>
    @foreach($data['rows'] as $i => $r)
    <tr class="{{ $r['gap'] > 0 ? 'gap' : ($loop->even ? 'alt' : '') }}">
      <td>{{ $i + 1 }}</td>
      <td>{{ $r['employee'] }}</td>
      <td>{{ $r['department'] }}</td>
      <td>{{ $r['competency'] }}</td>
      <td class="text-center">{{ $r['required'] }}</td>
      <td class="text-center">{{ $r['actual'] ?? '—' }}</td>
      <td class="text-center">{{ $r['gap'] }}</td>
      <td>{{ $r['gap'] > 0 ? (implode(', ', array_slice($r['training'], 0, 3)) ?: '—') : 'Terpenuhi' }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif

<div class="footer">ProPeople &mdash; PT. Pro Energi &mdash; Dokumen ini digenerate otomatis oleh sistem</div>
</body>
</html>
