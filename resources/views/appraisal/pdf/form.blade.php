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
  .header-logo {
    width: 60px;
    vertical-align: middle;
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

  /* ── ASPEK TABLE ── */
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
    font-size: 8.5pt;
    vertical-align: middle;
  }
  .aspects-table th {
    background-color: #d0d0d0;
    font-weight: bold;
    font-size: 8pt;
  }
  .aspects-table td.left { text-align: left; }
  .aspects-table td.score { font-weight: bold; }
  .aspects-table tfoot td { background-color: #e8e8e8; font-weight: bold; }

  .checkmark { font-size: 12pt; font-weight: bold; color: #000; font-family: DejaVu Sans, sans-serif; }

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
  .section-box table td.label { width: 160px; }
  .section-box table td.sep   { width: 12px; }
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
  .grade-box .lbl { font-size: 7.5pt; }

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
    width: 25%;
  }
  .sig-space { height: 0; display:none; }
  .sig-name  { border-top: 1px solid #000; padding-top: 2px; margin-top: 8px; font-weight: bold; font-size: 8pt; }
  .sig-role  { font-size: 7.5pt; color: #444; }

  .text-center { text-align: center; }
  .text-right  { text-align: right; }
  .font-bold   { font-weight: bold; }

  /* ── PERIOD BOX top-right ── */
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
        <div class="company-name">PT. PRO ENERGI</div>
        <div class="form-title">Formulir Penilaian Kinerja Karyawan</div>
        <div class="form-subtitle">Employee Performance Appraisal Form</div>
      </td>
      <td class="header-doc">
        <table>
          <tr><td>No. Dok</td><td>HR-PA-001</td></tr>
          <tr><td>Rev.</td><td>00</td></tr>
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
      <td class="val">{{ $appraisal->employee->position ?? '-' }}</td>
      <td></td>
      <td class="label">Level Jabatan</td>
      <td class="sep">:</td>
      <td class="val">{{ $appraisal->employee->level?->name ?? '-' }}</td>
    </tr>
    <tr>
      <td class="label">Departemen</td>
      <td class="sep">:</td>
      <td class="val">{{ $appraisal->employee->department ?? '-' }}</td>
      <td></td>
      <td class="label">LOB</td>
      <td class="sep">:</td>
      <td class="val">{{ $appraisal->employee->lob ?? '-' }}</td>
    </tr>
    <tr>
      <td class="label">Status Karyawan</td>
      <td class="sep">:</td>
      <td class="val">{{ $appraisal->employee->employment_status_label ?? '-' }}</td>
      <td></td>
      <td class="label">Tgl. Mulai Kerja</td>
      <td class="sep">:</td>
      <td class="val">{{ $appraisal->employee->start_date?->format('d/m/Y') ?? '-' }}</td>
    </tr>
  </table>

  @if($appraisal->template->isWeightedScale())
  {{-- ══════════════════════════════════════════════════
       WEIGHTED SCALE — Staff / Senior Staff
       ══════════════════════════════════════════════════ --}}
  @php
    $evalLabels  = ['self' => 'Diri Sendiri', 'atasan1' => 'Atasan I', 'atasan2' => 'Atasan II', 'ho' => 'Head Office'];
    $ratingLabels = [1=>'Kurang Sekali',2=>'Kurang',3=>'Cukup',4=>'Baik',5=>'Baik Sekali'];
  @endphp
  <table class="aspects-table">
    <thead>
      <tr>
        <th style="width:22px">No</th>
        <th class="left">Faktor Penilaian</th>
        <th style="width:38px">Bobot</th>
        <th style="width:62px">Diri Sendiri</th>
        <th style="width:55px">Atasan I</th>
        <th style="width:55px">Atasan II</th>
        <th style="width:55px">Head Office</th>
      </tr>
    </thead>
    <tbody>
    @foreach($appraisal->template->aspects as $aspect)
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td class="left">{{ $aspect->name }}</td>
        <td>{{ $aspect->weight_pct }}%</td>
        @foreach(array_keys($evalLabels) as $evalType)
          @php $item = $itemsByEvaluator->get($evalType)?->get($aspect->id); @endphp
          <td>
            @if($item?->rating)
              <strong>{{ $item->rating }}</strong>
              <br><span style="font-size:6.5pt;color:#555;">{{ $ratingLabels[(int)$item->rating] ?? '' }}</span>
            @else
              –
            @endif
          </td>
        @endforeach
      </tr>
    @endforeach
    </tbody>
    <tfoot>
      <tr>
        <td colspan="3" class="text-right">Skor (maks 500)</td>
        @foreach(array_keys($evalLabels) as $evalType)
          <td class="score">{{ number_format($appraisal->{'score_'.$evalType} ?? 0, 0) }}</td>
        @endforeach
      </tr>
      <tr>
        <td colspan="3" class="text-right">TOTAL SKOR (rata-rata penilai)</td>
        <td colspan="4" class="score">
          {{ number_format($appraisal->total_score ?? 0, 1) }}
          &nbsp; — &nbsp; {{ $appraisal->grade ?? '-' }}
        </td>
      </tr>
    </tfoot>
  </table>

  {{-- Kualitatif --}}
  @if($appraisal->strength_points || $appraisal->development_need || $appraisal->individual_development_plan)
  <div class="section-box">
    <h4>Penilaian Kualitatif</h4>
    <table>
      @if($appraisal->strength_points)
      <tr>
        <td class="label" style="width:130px;font-weight:bold;vertical-align:top;">Strength Point</td>
        <td class="sep">:</td>
        <td>{{ $appraisal->strength_points }}</td>
      </tr>
      @endif
      @if($appraisal->development_need)
      <tr>
        <td class="label" style="font-weight:bold;vertical-align:top;">Development Need</td>
        <td class="sep">:</td>
        <td>{{ $appraisal->development_need }}</td>
      </tr>
      @endif
      @if($appraisal->individual_development_plan)
      <tr>
        <td class="label" style="font-weight:bold;vertical-align:top;">IDP</td>
        <td class="sep">:</td>
        <td>{{ $appraisal->individual_development_plan }}</td>
      </tr>
      @endif
    </table>
  </div>
  @endif

  @if($appraisal->notes)
  <div class="section-box">
    <h4>Catatan Evaluator</h4>
    <span class="notes-line">{{ $appraisal->notes }}</span>
  </div>
  @endif

  @if($appraisal->decision)
  <div class="section-box">
    <h4>Keputusan</h4>
    <span class="notes-line">{{ $appraisal->decision }}</span>
  </div>
  @endif

  @else
  {{-- ══════════════════════════════════════════════════
       FIXED POINTS — SPV / Manager
       ══════════════════════════════════════════════════ --}}
  <table class="aspects-table">
    <thead>
      <tr>
        <th style="width:28px">No</th>
        <th class="left">Aspek Penilaian</th>
        <th style="width:70px">Baik Sekali<br><small>(BS)</small></th>
        <th style="width:60px">Baik<br><small>(B)</small></th>
        <th style="width:60px">Cukup<br><small>(C)</small></th>
        <th style="width:55px">Kurang<br><small>(K)</small></th>
        <th style="width:50px">Skor</th>
      </tr>
    </thead>
    <tbody>
    @foreach($appraisal->template->aspects as $aspect)
      @php
        $item = $itemsByAspect->get($aspect->id);
        $selected = $item?->rating;
        $weights = $aspect->weights->keyBy('rating');
      @endphp
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td class="left">
          {{ $aspect->name }}
          <br>
          <span style="font-size:7pt;color:#555;">
            BS={{ $weights->get('BS')?->score ?? 0 }} /
            B={{ $weights->get('B')?->score ?? 0 }} /
            C={{ $weights->get('C')?->score ?? 0 }} /
            K={{ $weights->get('K')?->score ?? 0 }}
          </span>
        </td>
        <td>@if($selected==='BS')<span class="checkmark">&#10003;</span>@endif</td>
        <td>@if($selected==='B') <span class="checkmark">&#10003;</span>@endif</td>
        <td>@if($selected==='C') <span class="checkmark">&#10003;</span>@endif</td>
        <td>@if($selected==='K') <span class="checkmark">&#10003;</span>@endif</td>
        <td class="score">{{ $item?->score ?? 0 }}</td>
      </tr>
    @endforeach
    </tbody>
    <tfoot>
      <tr>
        <td colspan="6" class="text-right">TOTAL SKOR</td>
        <td class="score">{{ $appraisal->total_score }}</td>
      </tr>
    </tfoot>
  </table>

  {{-- Grade + Absensi --}}
  <table class="grade-row">
    <tr>
      <td style="width:50%">
        <div class="section-box" style="margin:0;">
          <h4>Data Absensi</h4>
          <table>
            <tr>
              <td class="label">Rata-rata Keterlambatan</td>
              <td class="sep">:</td>
              <td><strong>{{ $appraisal->avg_late_per_month }}</strong> hari/bulan</td>
            </tr>
            <tr>
              <td class="label">Rata-rata Tidak Hadir</td>
              <td class="sep">:</td>
              <td><strong>{{ $appraisal->avg_leave_per_month }}</strong> hari/bulan</td>
            </tr>
          </table>
        </div>
      </td>
      <td style="width:5px"></td>
      <td style="width:50%; vertical-align:middle; text-align:center;">
        <div style="margin-bottom:4px; font-size:8.5pt; font-weight:bold;">GRADE PENILAIAN</div>
        <div class="grade-box">
          <div class="val">{{ $appraisal->grade ?? '-' }}</div>
        </div>
        <div style="margin-top:4px; font-size:8pt; color:#555;">
          Total Skor: <strong>{{ $appraisal->total_score }}</strong>
        </div>
      </td>
    </tr>
  </table>

  <div class="section-box">
    <h4>Usulan</h4>
    <table>
      <tr>
        <td class="label">Surat Teguran</td>
        <td class="sep">:</td>
        <td>{{ $appraisal->warning_letter ? 'Ya' : 'Tidak' }}</td>
        <td style="width:20px"></td>
        <td class="label">Surat Peringatan</td>
        <td class="sep">:</td>
        <td>{{ $appraisal->sp_level_label }}</td>
      </tr>
    </table>
  </div>

  <div class="section-box">
    <h4>Catatan / Rekomendasi</h4>
    <span class="notes-line">{{ $appraisal->notes ?? '' }}</span>
    @if(!$appraisal->notes)<span class="notes-line">&nbsp;</span>@endif
  </div>

  @if($appraisal->decision)
  <div class="section-box">
    <h4>Keputusan</h4>
    <span class="notes-line">{{ $appraisal->decision }}</span>
  </div>
  @endif

  @endif {{-- end isWeightedScale --}}

  {{-- ── TANDA TANGAN ── --}}
  @php
    $sm = new \App\Services\Appraisal\ApprovalStateMachine();
    $step1 = $sm->stepConfig($appraisal, 1);
    $step2 = $sm->stepConfig($appraisal, 2);

    $submitApproval   = $appraisal->approvals->firstWhere('action', 'submit');
    $approveApprovals = $appraisal->approvals->where('action', 'approve')->values();
    $approve1 = $approveApprovals->get(0);
    $approve2 = $approveApprovals->get(1);
  @endphp

  @if($appraisal->template->isWeightedScale())
  {{-- Weighted scale: Diisi Oleh (karyawan) | Dievaluasi Oleh (evaluator) | Disetujui (CEO) --}}
  <table class="sig-table" style="margin-top:10px;">
    <tr>
      <td style="width:33%;">
        <div style="font-weight:bold; font-size:8pt; margin-bottom:2px;">Diisi Oleh</div>
        <div style="font-size:7.5pt; color:#444; margin-bottom:2px;">Karyawan Yang Dinilai</div>
        <div class="sig-space"></div>
        <div class="sig-name">{{ $appraisal->employee->name }}</div>
        <div class="sig-role">{{ $appraisal->employee->position ?? '' }}</div>
        <div class="sig-role">Tgl: .............</div>
      </td>
      <td style="width:33%;">
        <div style="font-weight:bold; font-size:8pt; margin-bottom:2px;">Dievaluasi Oleh</div>
        <div style="font-size:7.5pt; color:#444; margin-bottom:2px;">Evaluator / Atasan Langsung</div>
        <div class="sig-space"></div>
        <div class="sig-name">{{ $submitApproval?->user?->name ?? '........................' }}</div>
        @if($submitApproval)
          <div class="sig-role">{{ $submitApproval->created_at->format('d/m/Y') }}</div>
        @else
          <div class="sig-role">Tgl: .............</div>
        @endif
      </td>
      <td style="width:33%;">
        <div style="font-weight:bold; font-size:8pt; margin-bottom:2px;">Disetujui Oleh</div>
        <div style="font-size:7.5pt; color:#444; margin-bottom:2px;">{{ $step2?->label ?? 'CEO' }}</div>
        <div class="sig-space"></div>
        <div class="sig-name">
          {{ $approve1?->user?->name ?? '........................' }}
        </div>
        @if($approve1)
          <div class="sig-role">{{ $approve1->created_at->format('d/m/Y') }}</div>
        @else
          <div class="sig-role">Tgl: .............</div>
        @endif
      </td>
    </tr>
  </table>

  @else
  {{-- Fixed points: Dinilai Oleh | Diketahui | Menyetujui | Karyawan --}}
  <table class="sig-table" style="margin-top:10px;">
    <tr>
      <td>
        <div style="font-weight:bold; font-size:8pt; margin-bottom:2px;">Dinilai Oleh</div>
        <div style="font-size:7.5pt; color:#444; margin-bottom:2px;">Evaluator</div>
        <div class="sig-space"></div>
        <div class="sig-name">{{ $appraisal->evaluator?->name ?? '........................' }}</div>
        @if($submitApproval)
          <div class="sig-role">{{ $submitApproval->created_at->format('d/m/Y') }}</div>
        @else
          <div class="sig-role">Tgl: .............</div>
        @endif
      </td>
      <td>
        <div style="font-weight:bold; font-size:8pt; margin-bottom:2px;">Diketahui</div>
        <div style="font-size:7.5pt; color:#444; margin-bottom:2px;">{{ $step1?->label ?? 'User II' }}</div>
        <div class="sig-space"></div>
        <div class="sig-name">{{ $approve1?->user?->name ?? '........................' }}</div>
        @if($approve1)
          <div class="sig-role">{{ $approve1->created_at->format('d/m/Y') }}</div>
        @else
          <div class="sig-role">Tgl: .............</div>
        @endif
      </td>
      <td>
        <div style="font-weight:bold; font-size:8pt; margin-bottom:2px;">Menyetujui</div>
        <div style="font-size:7.5pt; color:#444; margin-bottom:2px;">{{ $step2?->label ?? 'CFO' }}</div>
        <div class="sig-space"></div>
        <div class="sig-name">{{ $approve2?->user?->name ?? '........................' }}</div>
        @if($approve2)
          <div class="sig-role">{{ $approve2->created_at->format('d/m/Y') }}</div>
        @else
          <div class="sig-role">Tgl: .............</div>
        @endif
      </td>
      <td>
        <div style="font-weight:bold; font-size:8pt; margin-bottom:2px;">Karyawan</div>
        <div style="font-size:7.5pt; color:#444; margin-bottom:2px;">Yang Dinilai</div>
        <div class="sig-space"></div>
        <div class="sig-name">{{ $appraisal->employee->name }}</div>
        <div class="sig-role">Tgl: .............</div>
      </td>
    </tr>
  </table>
  @endif

  <div style="text-align:center; font-size:7pt; color:#888; margin-top:8px; border-top:1px solid #ddd; padding-top:4px;">
    Dicetak melalui SIPRO — Sistem Informasi Pro Energi &nbsp;|&nbsp; {{ now()->format('d/m/Y H:i') }}
  </div>

</div>
</body>
</html>
