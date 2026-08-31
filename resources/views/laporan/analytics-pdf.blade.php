<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>HR Analytics {{ $year }}</title>
<style>
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 10.5px; color: #1a1a1a; margin: 0; padding: 20px 26px; }
  h1 { font-size: 15px; margin: 0 0 4px; }
  .sub { color: #666; font-size: 9.5px; margin-bottom: 14px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
  th, td { padding: 5px 8px; border: 1px solid #ddd; font-size: 9.5px; }
  th { background: #f1f3f6; text-align: left; }
  .r { text-align: right; }
  .c { text-align: center; }
  h2 { font-size: 12px; margin: 14px 0 6px; }
</style>
</head>
<body>
@include('components.pdf-kop', ['company' => $company, 'groupLabel' => 'Konsolidasi Grup'])
<h1>HR ANALYTICS</h1>
<div class="sub">Tahun {{ $year }} &nbsp;|&nbsp; Dicetak {{ now()->format('d F Y') }}</div>

<h2>Ringkasan</h2>
<table>
  <tr><th>Metrik</th><th class="r">Nilai</th></tr>
  <tr><td>Turnover Rate</td><td class="r">{{ $data['turnover']['rate'] !== null ? $data['turnover']['rate'] . '%' : '—' }} ({{ $data['turnover']['total'] }} keluar / {{ $data['turnover']['avg_headcount'] }} headcount)</td></tr>
  <tr><td>Absenteeism Rate</td><td class="r">{{ $data['absenteeism']['rate'] !== null ? $data['absenteeism']['rate'] . '%' : '—' }}</td></tr>
  <tr><td>Cost per Hire</td><td class="r">{{ $data['cost_per_hire']['cost_per_hire'] !== null ? 'Rp' . number_format($data['cost_per_hire']['cost_per_hire'],0,',','.') : '—' }} ({{ $data['cost_per_hire']['hires'] }} hire)</td></tr>
  <tr><td>Efektivitas Training</td><td class="r">{{ $data['training']['rate'] !== null ? $data['training']['rate'] . '%' : '—' }} ({{ $data['training']['completed'] }}/{{ $data['training']['total'] }})</td></tr>
</table>

<h2>Turnover per Departemen</h2>
<table>
  <tr><th>Departemen</th><th class="r">Jumlah Keluar</th></tr>
  @forelse($data['turnover']['by_department'] as $dept => $count)
    <tr><td>{{ $dept }}</td><td class="r">{{ $count }}</td></tr>
  @empty
    <tr><td colspan="2" class="c">Tidak ada data.</td></tr>
  @endforelse
</table>

<h2>Efektivitas Training per Program</h2>
<table>
  <tr><th>Program</th><th class="c">Peserta</th><th class="c">Selesai</th></tr>
  @forelse($data['training']['by_program'] as $prog => $row)
    <tr><td>{{ $prog }}</td><td class="c">{{ $row['total'] }}</td><td class="c">{{ $row['completed'] }}</td></tr>
  @empty
    <tr><td colspan="3" class="c">Tidak ada data.</td></tr>
  @endforelse
</table>
</body>
</html>
