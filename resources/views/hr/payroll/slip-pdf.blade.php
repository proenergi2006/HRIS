<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Slip Gaji {{ $slip->employee->name }} — {{ $period->period_label }}</title>
<style>
  body  { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a1a; margin: 0; padding: 0; }
  .header { background: #0F2A4A; color: #fff; padding: 12px 16px; }
  .header h1 { margin: 0; font-size: 14px; }
  .header p  { margin: 3px 0 0; font-size: 9px; opacity: .85; }
  .body { padding: 14px 16px; }
  .info-grid { display: table; width: 100%; border-collapse: collapse; margin-bottom: 4px; }
  .info-grid:last-of-type { margin-bottom: 12px; }
  .info-row  { display: table-row; }
  .info-label, .info-val { display: table-cell; padding: 3px 6px; font-size: 9.5px; }
  .info-label { font-weight: bold; width: 85px; color: #555; }
  .section-title { background: #0F2A4A; color: #fff; font-weight: bold;
                   font-size: 9px; padding: 4px 8px; margin: 10px 0 0; }
  table.comp { width: 100%; border-collapse: collapse; }
  table.comp td, table.comp th { padding: 4px 8px; font-size: 9.5px; border-bottom: 1px solid #e9ecef; }
  table.comp th { font-weight: bold; background: #f8f9fa; }
  table.comp .text-right { text-align: right; }
  .total-row { font-weight: bold; background: #f0f0f0; }
  .net-row { font-weight: bold; background: #0F2A4A; color: #fff; font-size: 11px; }
  .footer { border-top: 1px solid #dee2e6; padding-top: 10px; margin-top: 12px;
            display: table; width: 100%; }
  .sign-col { display: table-cell; width: 33%; text-align: center; padding-top: 8px; }
  .sign-line { border-top: 1px solid #333; width: 80%; margin: 40px auto 4px; }
</style>
</head>
<body>

<div class="header">
  <h1>SLIP GAJI — {{ strtoupper($period->company?->name) }}</h1>
  <p>Periode: {{ $period->period_label }} &nbsp;|&nbsp; Dicetak: {{ now()->format('d F Y') }}</p>
</div>

<div class="body">
  <div class="info-grid">
    <div class="info-row">
      <div class="info-label">NIP</div>
      <div class="info-val">{{ $slip->employee->nip ?? '-' }}</div>
      <div class="info-label">Cabang</div>
      <div class="info-val">{{ $slip->employee->branch ?? '-' }}</div>
    </div>
    <div class="info-row">
      <div class="info-label">Nama</div>
      <div class="info-val">{{ $slip->employee->name }}</div>
      <div class="info-label">Kelompok</div>
      <div class="info-val">{{ strtoupper($slip->employee->company?->short_name ?? $slip->employee->company?->name ?? '-') }}</div>
    </div>
    <div class="info-row">
      <div class="info-label">Jabatan</div>
      <div class="info-val">{{ $slip->employee->position?->name ?? '-' }}</div>
      <div class="info-label">Departemen</div>
      <div class="info-val">{{ $slip->employee->department?->name ?? '-' }}</div>
    </div>
    <div class="info-row">
      <div class="info-label">Join Date</div>
      <div class="info-val">{{ $slip->employee->start_date?->format('d/m/Y') ?? '-' }}</div>
      <div class="info-label">Grade</div>
      <div class="info-val">{{ strtoupper($slip->employee->level?->name ?? '-') }}</div>
    </div>
  </div>
  <div class="info-grid" style="margin-top:4px">
    <div class="info-row">
      <div class="info-label">Kehadiran</div>
      <div class="info-val">{{ $slip->attendance_days }} hari &nbsp;(Hari Kerja: {{ $slip->working_days }}, Alpha: {{ $slip->alpha_days }}, Cuti/Izin: {{ $slip->leave_days }})</div>
    </div>
  </div>

  {{-- Tunjangan --}}
  <div class="section-title">PENDAPATAN</div>
  <table class="comp">
    <tr>
      @foreach($slip->details->where('type','allowance') as $d)
      <tr>
        <td>{{ $d->component_name }}</td>
        <td class="text-right">Rp {{ number_format($d->amount,0,',','.') }}</td>
      </tr>
      @endforeach
      <tr class="total-row">
        <td>Total Pendapatan</td>
        <td class="text-right">Rp {{ number_format($slip->total_allowances,0,',','.') }}</td>
      </tr>
    </tr>
  </table>

  {{-- Potongan --}}
  @if($slip->details->where('type','deduction')->isNotEmpty())
  <div class="section-title">POTONGAN</div>
  <table class="comp">
    @foreach($slip->details->where('type','deduction') as $d)
    <tr>
      <td>{{ $d->component_name }}</td>
      <td class="text-right">Rp {{ number_format($d->amount,0,',','.') }}</td>
    </tr>
    @endforeach
    <tr class="total-row">
      <td>Total Potongan</td>
      <td class="text-right">Rp {{ number_format($slip->total_deductions,0,',','.') }}</td>
    </tr>
  </table>
  @endif

  {{-- Gaji Bersih --}}
  <table class="comp" style="margin-top:6px">
    <tr class="net-row">
      <td>GAJI BERSIH (TAKE HOME PAY)</td>
      <td class="text-right">Rp {{ number_format($slip->net_salary,0,',','.') }}</td>
    </tr>
  </table>

  {{-- Tanda Tangan --}}
  <div class="footer">
    <div class="sign-col" style="width:50%">
      <div>Prepared By,</div>
      <div class="sign-line"></div>
      <div>( _________________ )</div>
    </div>
    <div class="sign-col" style="width:50%">
      <div>Received,</div>
      <div class="sign-line"></div>
      <div>{{ $slip->employee->name }}</div>
    </div>
  </div>

  <p style="font-size:8px;color:#adb5bd;text-align:center;margin-top:10px">
    Dokumen ini digenerate oleh SIPRO. Apabila ada pertanyaan hubungi HRD.
  </p>
</div>
</body>
</html>
