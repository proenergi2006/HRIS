<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 9pt;
    color: #000;
    background: #fff;
  }

  .page {
    padding: 12mm 14mm 10mm 14mm;
  }

  /* ── HEADER ── */
  .header-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 6px;
  }
  .header-title {
    text-align: center;
    vertical-align: middle;
    padding: 0 10px;
  }
  .company-name {
    font-size: 13pt;
    font-weight: bold;
    letter-spacing: 1px;
  }
  .form-title {
    font-size: 11pt;
    font-weight: bold;
    margin-top: 3px;
    text-transform: uppercase;
  }
  .form-subtitle {
    font-size: 8.5pt;
    margin-top: 1px;
  }
  .header-doc {
    width: 110px;
    vertical-align: top;
    font-size: 7.5pt;
  }
  .header-doc table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #000;
  }
  .header-doc table td {
    border: 1px solid #000;
    padding: 2px 4px;
  }

  .divider {
    border-top: 2px solid #000;
    margin: 4px 0;
  }

  /* ── INFO KARYAWAN ── */
  .info-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 8px;
  }
  .info-table td {
    padding: 2px 4px;
    vertical-align: top;
    font-size: 8.5pt;
  }
  .info-table .label { width: 120px; font-weight: bold; }
  .info-table .sep   { width: 10px; }
  .info-table .val   { border-bottom: 1px solid #555; min-width: 140px; }

  /* ── KPI TABLE ── */
  .aspects-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 8px;
  }
  .aspects-table th,
  .aspects-table td {
    border: 1px solid #000;
    padding: 3px 5px;
    text-align: center;
    font-size: 8pt;
    vertical-align: middle;
  }
  .aspects-table th {
    background-color: #d0d0d0;
    font-weight: bold;
    font-size: 7.5pt;
  }
  .aspects-table td.left { text-align: left; }
  .aspects-table td.score { font-weight: bold; }
  .aspects-table tfoot td { background-color: #e8e8e8; font-weight: bold; }

  /* ── SUB-SECTIONS ── */
  .section-box {
    border: 1px solid #000;
    margin-bottom: 8px;
    padding: 5px 8px;
  }
  .section-box h4 {
    font-size: 8.5pt;
    font-weight: bold;
    border-bottom: 1px solid #ccc;
    padding-bottom: 3px;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .section-box table { width: 100%; border-collapse: collapse; }
  .section-box table td { padding: 2px 4px; font-size: 8.5pt; vertical-align: top; }
  .section-box .notes-line {
    border-bottom: 1px solid #888;
    min-height: 14px;
    display: block;
    margin-top: 2px;
  }

  /* ── GRADE BOX ── */
  .grade-row {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 8px;
  }
  .grade-row td { vertical-align: top; }
  .grade-box {
    border: 2px solid #000;
    display: inline-block;
    padding: 4px 12px;
    text-align: center;
    min-width: 60px;
  }
  .grade-box .val { font-size: 14pt; font-weight: bold; }

  /* ── SIGNATURE ── */
  .sig-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 6px;
  }
  .sig-table td {
    text-align: center;
    padding: 4px 8px;
    font-size: 8pt;
    border: 1px solid #000;
    vertical-align: top;
    width: 33%;
  }
  .sig-name  { border-top: 1px solid #000; padding-top: 2px; margin-top: 8px; font-weight: bold; font-size: 8pt; }
  .sig-role  { font-size: 7.5pt; color: #444; }

  .text-center { text-align: center; }
  .text-right  { text-align: right; }

  .period-label {
    font-size: 7.5pt;
    text-align: right;
    color: #555;
    margin-bottom: 4px;
  }
</style>
</head>
<body>
<div class="page">

  {{-- ── HEADER ── --}}
  <table class="header-table">
    <tr>
      <td class="header-title">
        @php $kopLogo = \App\Support\Branding::pdfLogo($appraisal->employee->company?->code ?? config('sipro.company.code')); @endphp
        @if($kopLogo)<img src="{{ $kopLogo }}" alt="" style="height:34px; margin-bottom:3px;">@endif
        <div class="company-name">{{ strtoupper($appraisal->employee->company?->name ?? config('sipro.company.name')) }}</div>
        <div class="form-title">Formulir Penilaian Kinerja Karyawan</div>
        <div class="form-subtitle">Employee Performance Appraisal Form (KPI-based)</div>
      </td>
      <td class="header-doc">
        <table>
          <tr><td>No. Dok</td><td>HR-PA-001</td></tr>
          <tr><td>Rev.</td><td>01</td></tr>
          <tr><td>Tgl.</td><td>{{ now()->format('d/m/Y') }}</td></tr>
          <tr><td>Hal.</td><td>1/1</td></tr>
        </table>
      </td>
    </tr>
  </table>
  <div class="divider"></div>

  <div class="period-label">
    Periode: <strong>{{ $appraisal->period->name }} ({{ $appraisal->period->year }})</strong>
  </div>

  {{-- ── INFO KARYAWAN ── --}}
  <table class="info-table">
    <tr>
      <td class="label">Nama Karyawan</td>
      <td class="sep">:</td>
      <td class="val">{{ $appraisal->employee->name }}</td>
      <td style="width:20px"></td>
      <td class="label">NIP</td>
      <td class="sep">:</td>
      <td class="val">{{ $appraisal->employee->nip ?? '-' }}</td>
    </tr>
    <tr>
      <td class="label">Jabatan / Posisi</td>
      <td class="sep">:</td>
      <td class="val">{{ $appraisal->employee->position?->name ?? '-' }}</td>
      <td></td>
      <td class="label">Level Jabatan</td>
      <td class="sep">:</td>
      <td class="val">{{ $appraisal->employee->level?->name ?? '-' }}</td>
    </tr>
    <tr>
      <td class="label">Departemen</td>
      <td class="sep">:</td>
      <td class="val">{{ $appraisal->employee->department?->name ?? '-' }}</td>
      <td></td>
      <td class="label">Evaluator</td>
      <td class="sep">:</td>
      <td class="val">{{ $appraisal->evaluator?->name ?? '-' }}</td>
    </tr>
  </table>

  {{-- ── TABEL KPI ── --}}
  <table class="aspects-table">
    <thead>
      <tr>
        <th style="width:20px">No</th>
        <th class="left">KPI / Objective</th>
        <th style="width:60px">Kategori</th>
        <th style="width:35px">Bobot</th>
        <th class="left">Target</th>
        <th class="left">Realisasi</th>
        <th style="width:45px">Capaian</th>
        <th style="width:40px">Skor</th>
      </tr>
    </thead>
    <tbody>
    @forelse($appraisal->objectives as $obj)
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td class="left">{{ $obj->title }}</td>
        <td>{{ $obj->category ?? '-' }}</td>
        <td>{{ $obj->weight_pct }}%</td>
        <td class="left">{{ $obj->target ?? '-' }}</td>
        <td class="left">{{ $obj->actual ?? '-' }}</td>
        <td>{{ $obj->achievement_pct !== null ? number_format($obj->achievement_pct, 1).'%' : '-' }}</td>
        <td class="score">{{ $obj->score ?? '-' }}</td>
      </tr>
    @empty
      <tr><td colspan="8">Belum ada KPI diisi.</td></tr>
    @endforelse
    </tbody>
    <tfoot>
      <tr>
        <td colspan="7" class="text-right">TOTAL SKOR</td>
        <td class="score">{{ number_format((float) $appraisal->total_score, 2) }}</td>
      </tr>
    </tfoot>
  </table>

  <table class="grade-row">
    <tr>
      <td style="width:70%">
        @if($appraisal->strengths)
        <div class="section-box" style="margin:0 0 6px;">
          <h4>Kekuatan</h4>
          <span class="notes-line">{{ $appraisal->strengths }}</span>
        </div>
        @endif
        @if($appraisal->development_notes)
        <div class="section-box" style="margin:0;">
          <h4>Area Pengembangan</h4>
          <span class="notes-line">{{ $appraisal->development_notes }}</span>
        </div>
        @endif
      </td>
      <td style="width:5px"></td>
      <td style="width:25%; vertical-align:middle; text-align:center;">
        <div style="margin-bottom:4px; font-size:8.5pt; font-weight:bold;">GRADE</div>
        <div class="grade-box">
          <div class="val">{{ $appraisal->grade ?? '-' }}</div>
        </div>
      </td>
    </tr>
  </table>

  @if($appraisal->notes)
  <div class="section-box">
    <h4>Catatan Evaluator</h4>
    <span class="notes-line">{{ $appraisal->notes }}</span>
  </div>
  @endif

  {{-- ── TANDA TANGAN ── --}}
  @php
    $steps = $appraisal->approvalRequest?->steps ?? collect();
    $step1 = $steps->firstWhere('step_order', 1);
    $step2 = $steps->firstWhere('step_order', 2);
  @endphp
  <table class="sig-table" style="margin-top:10px;">
    <tr>
      <td>
        <div style="font-weight:bold; font-size:8pt; margin-bottom:2px;">Dinilai Oleh</div>
        <div style="font-size:7.5pt; color:#444; margin-bottom:2px;">Evaluator</div>
        <div class="sig-name">{{ $appraisal->evaluator?->name ?? '........................' }}</div>
        <div class="sig-role">{{ $appraisal->submitted_at?->format('d/m/Y') ?? 'Tgl: .............' }}</div>
      </td>
      <td>
        <div style="font-weight:bold; font-size:8pt; margin-bottom:2px;">Disetujui</div>
        <div style="font-size:7.5pt; color:#444; margin-bottom:2px;">{{ $step1?->approver_label ?? 'HR Manager' }}</div>
        <div class="sig-name">{{ $step1?->status === 'approved' ? ($step1->approver?->name ?? '') : '........................' }}</div>
        <div class="sig-role">{{ $step1?->acted_at?->format('d/m/Y') ?? 'Tgl: .............' }}</div>
      </td>
      <td>
        <div style="font-weight:bold; font-size:8pt; margin-bottom:2px;">Disetujui Final</div>
        <div style="font-size:7.5pt; color:#444; margin-bottom:2px;">{{ $step2?->approver_label ?? 'CEO' }}</div>
        <div class="sig-name">{{ $step2?->status === 'approved' ? ($step2->approver?->name ?? '') : '........................' }}</div>
        <div class="sig-role">{{ $step2?->acted_at?->format('d/m/Y') ?? 'Tgl: .............' }}</div>
      </td>
    </tr>
  </table>

  <div style="text-align:center; font-size:7pt; color:#888; margin-top:8px; border-top:1px solid #ddd; padding-top:4px;">
    Dicetak melalui ProPeople — Sistem Informasi Pro Energi &nbsp;|&nbsp; {{ now()->format('d/m/Y H:i') }}
  </div>

</div>
</body>
</html>
