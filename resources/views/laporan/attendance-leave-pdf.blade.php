<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Absensi &amp; Cuti {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}</title>
<style>
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a1a; margin: 0; padding: 0; }
  .header { background: #0F2A4A; color: #fff; padding: 16px 20px; margin-bottom: 16px; }
  .header h1 { margin: 0; font-size: 16px; font-weight: bold; }
  .header p  { margin: 4px 0 0; font-size: 10px; opacity: .85; }
  .section-title { font-size: 11px; font-weight: bold; border-bottom: 2px solid #0F2A4A;
                   padding-bottom: 4px; margin: 18px 0 8px; color: #0F2A4A; }
  table.data { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
  table.data th { background: #0F2A4A; color: #fff; padding: 5px 6px; font-size: 8.5px; text-align: left; font-weight: bold; }
  table.data td { padding: 4px 6px; border-bottom: 1px solid #e9ecef; font-size: 9px; }
  table.data tr.alt { background: #f8f9fa; }
  table.data tfoot td { font-weight: bold; background: #e9ecef; border-top: 2px solid #dee2e6; }
  .text-right { text-align: right; } .text-center { text-align: center; }
  .footer { text-align: center; font-size: 9px; color: #adb5bd; margin-top: 24px; border-top: 1px solid #dee2e6; padding-top: 8px; }
</style>
</head>
<body>
<div style="padding:14px 20px 0;">
  @include('components.pdf-kop', ['company' => $company, 'groupLabel' => 'Semua Perusahaan'])
</div>
<div class="header">
  <h1>Laporan Absensi &amp; Cuti</h1>
  <p>Periode: {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }} &nbsp;|&nbsp; Dicetak: {{ now()->format('d F Y, H:i') }} WIB</p>
</div>

<div class="section-title">Rekap Absensi per Karyawan ({{ $data['total_records'] }} record)</div>
@if($data['per_employee']->isEmpty())
  <p>Tidak ada data absensi.</p>
@else
<table class="data">
  <thead>
    <tr>
      <th>#</th><th>Karyawan</th>
      @foreach($data['statusLabels'] as $label)<th class="text-center">{{ $label }}</th>@endforeach
      <th class="text-right">Telat(mnt)</th><th class="text-right">Lembur(mnt)</th>
    </tr>
  </thead>
  <tbody>
    @foreach($data['per_employee'] as $i => $row)
    <tr class="{{ $loop->even ? 'alt' : '' }}">
      <td>{{ $i + 1 }}</td><td>{{ $row['name'] }}</td>
      @foreach($data['statusLabels'] as $key => $label)<td class="text-center">{{ $row['counts'][$key] ?? 0 }}</td>@endforeach
      <td class="text-right">{{ number_format($row['late_minutes'],0,',','.') }}</td>
      <td class="text-right">{{ number_format($row['overtime_minutes'],0,',','.') }}</td>
    </tr>
    @endforeach
  </tbody>
  <tfoot>
    <tr>
      <td colspan="2">Total</td>
      @foreach($data['statusLabels'] as $key => $label)<td class="text-center">{{ $data['totals'][$key] ?? 0 }}</td>@endforeach
      <td colspan="2"></td>
    </tr>
  </tfoot>
</table>
@endif

<div class="section-title">Rekap Cuti Disetujui per Tipe</div>
@if($data['leave_by_type']->isEmpty())
  <p>Tidak ada cuti disetujui.</p>
@else
<table class="data">
  <thead><tr><th>Tipe Cuti</th><th class="text-center">Pengajuan</th><th class="text-right">Total Hari</th></tr></thead>
  <tbody>
    @foreach($data['leave_by_type'] as $i => $row)
    <tr class="{{ $loop->even ? 'alt' : '' }}"><td>{{ $row['type'] }}</td><td class="text-center">{{ $row['count'] }}</td><td class="text-right">{{ $row['days'] }}</td></tr>
    @endforeach
  </tbody>
  <tfoot><tr><td>Total</td><td class="text-center">{{ $data['leave_total_count'] }}</td><td class="text-right">{{ $data['leave_total_days'] }}</td></tr></tfoot>
</table>
@endif

<div class="footer">ProPeople &mdash; PT. Pro Energi &mdash; Dokumen ini digenerate otomatis oleh sistem</div>
</body>
</html>
