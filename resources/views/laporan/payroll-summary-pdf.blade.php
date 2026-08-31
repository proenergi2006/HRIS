<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Payroll {{ $period->period_label }}</title>
<style>
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a1a; margin: 0; padding: 0; }
  .header { background: #0F2A4A; color: #fff; padding: 16px 20px; margin-bottom: 16px; }
  .header h1 { margin: 0; font-size: 16px; font-weight: bold; }
  .header p  { margin: 4px 0 0; font-size: 10px; opacity: .85; }
  .section-title { font-size: 11px; font-weight: bold; border-bottom: 2px solid #0F2A4A; padding-bottom: 4px; margin: 18px 0 8px; color: #0F2A4A; }
  .card-row { display: table; width: 100%; margin-bottom: 16px; border-collapse: separate; border-spacing: 6px; }
  .card { display: table-cell; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 10px 12px; width: 25%; vertical-align: top; }
  .card .label { font-size: 9px; color: #6c757d; text-transform: uppercase; letter-spacing: .5px; }
  .card .value { font-size: 13px; font-weight: bold; margin: 4px 0 2px; }
  table.data { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
  table.data th { background: #0F2A4A; color: #fff; padding: 5px 8px; font-size: 9px; text-align: left; font-weight: bold; }
  table.data td { padding: 4px 8px; border-bottom: 1px solid #e9ecef; font-size: 9.5px; }
  table.data tr.alt { background: #f8f9fa; }
  table.data tfoot td { font-weight: bold; background: #e9ecef; border-top: 2px solid #dee2e6; }
  .text-right { text-align: right; }
  .footer { text-align: center; font-size: 9px; color: #adb5bd; margin-top: 24px; border-top: 1px solid #dee2e6; padding-top: 8px; }
</style>
</head>
<body>
<div style="padding:14px 20px 0;">
  @include('components.pdf-kop', ['company' => $period->company])
</div>
<div class="header">
  <h1>Laporan Payroll Summary</h1>
  <p>Periode: {{ $period->period_label }} ({{ ucfirst($period->status) }}) &nbsp;|&nbsp; Dicetak: {{ now()->format('d F Y, H:i') }} WIB</p>
</div>

<div class="card-row">
  <div class="card"><div class="label">Jumlah Slip</div><div class="value">{{ $data['slip_count'] }}</div></div>
  <div class="card"><div class="label">Total Gross</div><div class="value">Rp {{ number_format($data['total_gross'],0,',','.') }}</div></div>
  <div class="card"><div class="label">Total Potongan</div><div class="value">Rp {{ number_format($data['total_deductions'],0,',','.') }}</div></div>
  <div class="card"><div class="label">Total Net</div><div class="value">Rp {{ number_format($data['total_net'],0,',','.') }}</div></div>
</div>

<div class="section-title">Rincian Tunjangan (Allowance)</div>
<table class="data">
  <thead><tr><th>Komponen</th><th class="text-right">Total (Rp)</th></tr></thead>
  <tbody>
    @forelse($data['allowances'] as $i => $row)
    <tr class="{{ $loop->even ? 'alt' : '' }}"><td>{{ $row->component_name }}</td><td class="text-right">{{ number_format($row->total,0,',','.') }}</td></tr>
    @empty
    <tr><td colspan="2">Tidak ada data.</td></tr>
    @endforelse
  </tbody>
  <tfoot><tr><td>Total Tunjangan</td><td class="text-right">{{ number_format($data['total_allowances'],0,',','.') }}</td></tr></tfoot>
</table>

<div class="section-title">Rincian Potongan (Deduction)</div>
<table class="data">
  <thead><tr><th>Komponen</th><th class="text-right">Total (Rp)</th></tr></thead>
  <tbody>
    @forelse($data['deductions'] as $i => $row)
    <tr class="{{ $loop->even ? 'alt' : '' }}"><td>{{ $row->component_name }}</td><td class="text-right">{{ number_format($row->total,0,',','.') }}</td></tr>
    @empty
    <tr><td colspan="2">Tidak ada data.</td></tr>
    @endforelse
  </tbody>
  <tfoot><tr><td>Total Potongan</td><td class="text-right">{{ number_format($data['total_deductions'],0,',','.') }}</td></tr></tfoot>
</table>

<div class="section-title">Slip per Karyawan</div>
<table class="data">
  <thead><tr><th>#</th><th>Karyawan</th><th class="text-right">Gross</th><th class="text-right">Tunjangan</th><th class="text-right">Potongan</th><th class="text-right">Net</th></tr></thead>
  <tbody>
    @foreach($data['slips'] as $i => $slip)
    <tr class="{{ $loop->even ? 'alt' : '' }}">
      <td>{{ $i + 1 }}</td>
      <td>{{ $slip->employee?->name ?? '-' }}</td>
      <td class="text-right">{{ number_format($slip->gross_salary,0,',','.') }}</td>
      <td class="text-right">{{ number_format($slip->total_allowances,0,',','.') }}</td>
      <td class="text-right">{{ number_format($slip->total_deductions,0,',','.') }}</td>
      <td class="text-right">{{ number_format($slip->net_salary,0,',','.') }}</td>
    </tr>
    @endforeach
  </tbody>
  <tfoot>
    <tr>
      <td colspan="2">Total</td>
      <td class="text-right">{{ number_format($data['total_gross'],0,',','.') }}</td>
      <td class="text-right">{{ number_format($data['total_allowances'],0,',','.') }}</td>
      <td class="text-right">{{ number_format($data['total_deductions'],0,',','.') }}</td>
      <td class="text-right">{{ number_format($data['total_net'],0,',','.') }}</td>
    </tr>
  </tfoot>
</table>

<div class="footer">ProPeople &mdash; PT. Pro Energi &mdash; Dokumen ini digenerate otomatis oleh sistem</div>
</body>
</html>
