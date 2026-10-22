<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Profil Karyawan — {{ $employee->name }}</title>
<style>
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a1a; margin: 28px 32px; }
  .header { background: #0F2A4A; color: #fff; padding: 12px 16px; margin: 8px 0 12px; }
  .header h1 { margin: 0; font-size: 15px; }
  .header p  { margin: 3px 0 0; font-size: 10px; opacity: .85; }
  .section-title { font-size: 10.5px; font-weight: bold; color: #0F2A4A; border-bottom: 2px solid #0F2A4A; padding-bottom: 3px; margin: 14px 0 4px; text-transform: uppercase; }
  table.rows { width: 100%; border-collapse: collapse; }
  table.rows td { padding: 4px 6px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
  table.rows td.l { width: 30%; color: #6b7280; }
  .footer { text-align: center; font-size: 8.5px; color: #9ca3af; margin-top: 18px; border-top: 1px solid #e5e7eb; padding-top: 6px; }
</style>
</head>
<body>
@include('components.pdf-kop', ['company' => $company])

<div class="header">
  <h1>{{ $employee->name }}</h1>
  <p>{{ $employee->nip ?: '—' }} &nbsp;|&nbsp; {{ $employee->position?->name ?: '—' }}</p>
</div>

@foreach($sections as $title => $rows)
  <div class="section-title">{{ $title }}</div>
  <table class="rows">
    @foreach($rows as $label => $value)
      <tr><td class="l">{{ $label }}</td><td>{{ filled($value) ? $value : '—' }}</td></tr>
    @endforeach
  </table>
@endforeach

<div class="footer">Dicetak {{ now()->format('d/m/Y H:i') }} WIB &nbsp;|&nbsp; Dokumen rahasia — hanya untuk keperluan internal HRD</div>
</body>
</html>
