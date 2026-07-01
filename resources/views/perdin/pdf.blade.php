<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
@php
  $primary = '#14488f';   // corporate blue
  $accent  = '#3f86d4';   // lighter blue accent
  $cats    = \App\Models\Perdin\PerdinRequest::$categoryLabels;

  $company = config('sipro.company');

  $byRole     = $perdin->approvals->where('action', 'approve')->keyBy('role');
  $sigManager = $byRole->get('direct_manager');
  $sigHr      = $byRole->get('hr_manager');
  $sigCeo     = $byRole->get('ceo');

  $fmtDate = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d M Y') : '';

  $statusMap = [
    'draft'            => ['Draft', '#6c757d'],
    'submitted'        => ['Menunggu Atasan', '#b8860b'],
    'reviewed_manager' => ['Menunggu HR & GA', '#0d6efd'],
    'reviewed_hr'      => ['Menunggu Direktur', '#6f42c1'],
    'approved'         => ['DISETUJUI', '#198754'],
    'rejected'         => ['DITOLAK', '#dc3545'],
  ];
  [$statusText, $statusColor] = $statusMap[$perdin->status] ?? ['—', '#6c757d'];

  $byGaTotal = $perdin->budgetItems->where('handled_by', 'ga')->sum('total_cost');
@endphp
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  /* DomPDF mengabaikan @page margin di setup ini — pakai margin pada body. */
  @page { margin: 0; }
  body { font-family: 'Helvetica', Arial, sans-serif; font-size: 9.5pt; color: #2b2b2b;
         line-height: 1.4; margin: 14mm 18mm 16mm 18mm; }

  /* ---------- Letterhead ---------- */
  .lh { width: 100%; border-collapse: collapse; }
  .lh td { vertical-align: top; }
  .lh .company { font-size: 17pt; font-weight: bold; color: {{ $primary }}; letter-spacing: .5px; }
  .lh .tagline { font-size: 8pt; color: #888; letter-spacing: 2px; text-transform: uppercase; }
  .lh .addr { font-size: 7.5pt; color: #999; margin-top: 2px; }

  .refbox { border: 1px solid #d7d7e0; border-radius: 4px; width: 210px; }
  .refbox table { width: 100%; border-collapse: collapse; }
  .refbox td { padding: 3px 8px; font-size: 8pt; }
  .refbox td.k { color: #888; width: 42%; }
  .refbox td.v { color: {{ $primary }}; font-weight: bold; text-align: right; }
  .refbox .top { background: {{ $primary }}; color: #fff; font-weight: bold; font-size: 8pt;
                 padding: 4px 8px; text-align: center; letter-spacing: 1px; }
  .status-pill { display: inline-block; color: #fff; font-weight: bold; font-size: 7.5pt;
                 padding: 2px 8px; border-radius: 10px; }

  .accent { height: 3px; background: {{ $accent }}; margin: 6px 0 0; }
  .rule   { height: 2px; background: {{ $primary }}; margin: 2px 0 14px; }

  .title-bar { background: {{ $primary }}; color: #fff; text-align: center; font-size: 12pt;
               font-weight: bold; letter-spacing: 2px; text-transform: uppercase;
               padding: 7px 0; border-radius: 3px; margin-bottom: 14px; }

  /* ---------- Sections ---------- */
  .sec { font-size: 9.5pt; font-weight: bold; color: {{ $primary }}; text-transform: uppercase;
         letter-spacing: .5px; background: #f1f1f6; border-left: 4px solid {{ $primary }};
         padding: 5px 10px; margin: 14px 0 7px; }

  table.info { width: 100%; border-collapse: collapse; }
  table.info td { padding: 3px 6px; font-size: 9pt; vertical-align: top; }
  table.info td.lbl { width: 24%; color: #777; }
  table.info td.sep { width: 10px; color: #bbb; }

  .panel { border: 1px solid #e0e0e8; border-radius: 4px; padding: 10px 14px; }
  .panel.tint { background: #f7f7fb; }
  .panel.purpose { background: #fcfcff; min-height: 40px; line-height: 1.55; }

  .kv-amount { font-size: 11pt; font-weight: bold; color: {{ $primary }}; }

  /* ---------- Data tables ---------- */
  table.data { width: 100%; border-collapse: collapse; margin-top: 6px; border: 1px solid {{ $primary }}; }
  table.data thead th { background: {{ $primary }}; color: #fff; font-size: 8.5pt; padding: 7px 7px;
                        text-align: left; text-transform: uppercase; letter-spacing: .3px; }
  table.data td { border-bottom: 1px solid #e6e6ee; padding: 5px 7px; font-size: 9pt; }
  table.data td.num, table.data th.num { text-align: right; }
  table.data td.ctr, table.data th.ctr { text-align: center; }
  table.data tr.cat td { background: #e9e9f2; font-weight: bold; color: {{ $primary }};
                         font-size: 8.5pt; text-transform: uppercase; letter-spacing: .3px; }
  table.data tr.alt td { background: #f8f8fc; }
  table.data tr.total td { background: {{ $primary }}; color: #fff; font-weight: bold; font-size: 10pt; border: none; }

  .badge-ga { background: #e7efff; color: #1d4ed8; border: 1px solid #b9cdfb; border-radius: 3px;
              padding: 1px 7px; font-size: 7.5pt; font-weight: bold; }
  .badge-self { color: #888; font-size: 8pt; }

  /* summary cards */
  .sumrow { width: 100%; border-collapse: separate; border-spacing: 8px 0; margin-top: 12px; }
  .sumrow td { width: 33.33%; border: 1px solid #e0e0e8; border-radius: 4px; padding: 8px 10px; text-align: center; }
  .sumrow .s-lbl { font-size: 7.5pt; color: #888; text-transform: uppercase; letter-spacing: .5px; }
  .sumrow .s-val { font-size: 11pt; font-weight: bold; margin-top: 3px; }

  .route-sub { font-size: 9pt; color: #555; margin: 4px 0 2px; }
  .route-sub b { color: {{ $primary }}; }

  /* ---------- Signatures ---------- */
  .signs { width: 100%; border-collapse: collapse; margin-top: 24px; }
  .signs td { border: 1px solid #d9d9e3; vertical-align: top; padding: 0; }
  .sg-head { background: {{ $primary }}; color: #fff; font-weight: bold; font-size: 8pt;
             text-align: center; padding: 4px 2px; letter-spacing: .3px; }
  .sg-cap  { color: #999; font-size: 7pt; text-align: center; padding: 3px 2px 0; }
  .sg-space{ height: 50px; text-align: center; vertical-align: middle; }
  .sg-name { text-align: center; font-weight: bold; font-size: 8.5pt; padding: 2px; }
  .sg-name .nm { border-top: 1px solid #555; padding-top: 3px; display: inline-block; min-width: 80%; }
  .sg-date { color: #999; font-size: 7pt; }

  .stamp { display: inline-block; border: 2px solid {{ $statusColor }}; color: {{ $statusColor }};
           font-weight: bold; font-size: 9pt; letter-spacing: 1px; padding: 3px 10px;
           border-radius: 4px; opacity: .85; }

  .note { font-size: 7.5pt; color: #999; margin-top: 7px; line-height: 1.5; }

  /* ---------- Footer ---------- */
  .footer { position: fixed; bottom: 5mm; left: 18mm; right: 18mm; height: 9mm;
            border-top: 1px solid {{ $accent }}; padding-top: 4px; }
  .footer td { font-size: 7pt; color: #aaa; }
  .footer .pagenum:after { content: counter(page); }

  .page-break { page-break-before: always; }
</style>
</head>
<body>

<div class="footer">
  <table style="width:100%; border-collapse:collapse;">
    <tr>
      <td style="text-align:left;"><strong style="color:{{ $primary }};">{{ $company['name'] }}</strong> &nbsp;·&nbsp; {{ $company['tagline'] }}</td>
      <td style="text-align:center;">Dicetak {{ now()->format('d M Y, H:i') }}</td>
      <td style="text-align:right;">Hal. <span class="pagenum"></span></td>
    </tr>
  </table>
</div>

{{-- ═══════════ HALAMAN 1 — FORM ═══════════ --}}
@php
  $refTitle = 'FORM PERJALANAN DINAS';
@endphp
<table class="lh">
  <tr>
    <td>
      <div class="company">{{ $company['name'] }}</div>
      <div class="tagline">{{ $company['tagline'] }}</div>
      <div class="addr">{{ $company['address'] }} &middot; {{ $company['website'] }}</div>
    </td>
    <td style="width:215px; text-align:right;">
      <div class="refbox" style="display:inline-block; text-align:left;">
        <div class="top">DOKUMEN PERJALANAN DINAS</div>
        <table>
          <tr><td class="k">No. Advance</td><td class="v">{{ $perdin->no_advance }}</td></tr>
          <tr><td class="k">Tanggal</td><td class="v">{{ $fmtDate($perdin->created_at) }}</td></tr>
          <tr><td class="k">Status</td><td class="v" style="text-align:right;">
            <span class="status-pill" style="background:{{ $statusColor }};">{{ $statusText }}</span>
          </td></tr>
        </table>
      </div>
    </td>
  </tr>
</table>
<div class="accent"></div>
<div class="rule"></div>

<div class="title-bar">Form Perjalanan Dinas</div>

<div class="sec">Data Karyawan</div>
<table class="info">
  <tr>
    <td class="lbl">Nama</td><td class="sep">:</td><td><strong>{{ $perdin->user->name }}</strong></td>
    <td class="lbl">Departemen</td><td class="sep">:</td><td>{{ $perdin->department ?? '-' }}</td>
  </tr>
  <tr>
    <td class="lbl">Kota Tujuan</td><td class="sep">:</td><td>{{ $perdin->destination }}</td>
    <td class="lbl">Lama Perjalanan</td><td class="sep">:</td><td>{{ $perdin->departure_date->diffInDays($perdin->return_date) + 1 }} hari</td>
  </tr>
</table>

<div class="sec">Periode Perjalanan</div>
<table class="info" style="margin-bottom:2px;">
  <tr>
    <td class="lbl">Keberangkatan</td><td class="sep">:</td>
    <td><strong>{{ $perdin->departure_date->locale('id')->isoFormat('dddd, D MMMM Y') }}</strong>
        {{ $perdin->departure_time ? ' · ' . substr($perdin->departure_time,0,5) . ' WIB' : '' }}</td>
  </tr>
  <tr>
    <td class="lbl">Kepulangan</td><td class="sep">:</td>
    <td><strong>{{ $perdin->return_date->locale('id')->isoFormat('dddd, D MMMM Y') }}</strong>
        {{ $perdin->return_time ? ' · ' . substr($perdin->return_time,0,5) . ' WIB' : '' }}</td>
  </tr>
</table>

<div class="sec">Maksud / Tujuan Perjalanan</div>
<div class="panel purpose">{{ $perdin->purpose ?: '-' }}</div>

<table class="sumrow">
  <tr>
    <td>
      <div class="s-lbl">Total Anggaran</div>
      <div class="s-val" style="color:{{ $primary }};">Rp {{ number_format($perdin->total_budget, 0, ',', '.') }}</div>
    </td>
    <td>
      <div class="s-lbl">Ditanggung Sendiri</div>
      <div class="s-val" style="color:#198754;">Rp {{ number_format($perdin->total_budget_self, 0, ',', '.') }}</div>
    </td>
    <td>
      <div class="s-lbl">Ditanggung GA</div>
      <div class="s-val" style="color:#1d4ed8;">Rp {{ number_format($byGaTotal, 0, ',', '.') }}</div>
    </td>
  </tr>
</table>

<div style="margin-top:20px; font-size:9pt; color:#555; line-height:1.5;">
  Demikian formulir perjalanan dinas ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya.
</div>

<table class="signs">
  <tr>
    <td style="width:25%;"><div class="sg-head">DIBUAT OLEH</div><div class="sg-cap">Karyawan</div></td>
    <td style="width:25%;"><div class="sg-head">DIKETAHUI</div><div class="sg-cap">Atasan Langsung</div></td>
    <td style="width:25%;"><div class="sg-head">DIKETAHUI</div><div class="sg-cap">Manager HR &amp; GA</div></td>
    <td style="width:25%;"><div class="sg-head">DISETUJUI</div><div class="sg-cap">Direktur Utama</div></td>
  </tr>
  <tr>
    <td class="sg-space"></td>
    <td class="sg-space">@if($sigManager)<span class="stamp">TTD</span>@endif</td>
    <td class="sg-space">@if($sigHr)<span class="stamp">TTD</span>@endif</td>
    <td class="sg-space">@if($sigCeo)<span class="stamp">TTD</span>@endif</td>
  </tr>
  <tr>
    <td class="sg-name"><span class="nm">{{ $perdin->user->name }}</span><div class="sg-date">{{ $fmtDate($perdin->created_at) }}</div></td>
    <td class="sg-name"><span class="nm">{{ $sigManager?->approver?->name ?? ($managerUser->name ?? ' ') }}</span><div class="sg-date">{{ $sigManager ? $fmtDate($sigManager->acted_at) : '—' }}</div></td>
    <td class="sg-name"><span class="nm">{{ $sigHr?->approver?->name ?? ' ' }}</span><div class="sg-date">{{ $sigHr ? $fmtDate($sigHr->acted_at) : '—' }}</div></td>
    <td class="sg-name"><span class="nm">{{ $sigCeo?->approver?->name ?? ' ' }}</span><div class="sg-date">{{ $sigCeo ? $fmtDate($sigCeo->acted_at) : '—' }}</div></td>
  </tr>
</table>

{{-- ═══════════ HALAMAN 2 — ANGGARAN ═══════════ --}}
<div class="page-break"></div>
<table class="lh">
  <tr>
    <td><div class="company" style="font-size:14pt;">{{ $company['name'] }}</div>
        <div class="tagline">{{ $company['tagline'] }}</div></td>
    <td style="text-align:right; vertical-align:bottom;">
      <div style="font-size:8pt; color:#888;">No. Advance</div>
      <div style="font-size:10pt; font-weight:bold; color:{{ $primary }};">{{ $perdin->no_advance }}</div>
    </td>
  </tr>
</table>
<div class="accent"></div>
<div class="rule"></div>
<div class="title-bar">Rincian Anggaran</div>

<table class="data">
  <thead>
    <tr>
      <th style="width:44%">Item</th>
      <th class="ctr" style="width:12%">Penanggung</th>
      <th class="num" style="width:8%">Qty</th>
      <th class="num" style="width:18%">Biaya Satuan</th>
      <th class="num" style="width:18%">Total</th>
    </tr>
  </thead>
  <tbody>
    @foreach($cats as $catKey => $catLabel)
      @php $rows = $perdin->budgetItems->where('category', $catKey)->values(); @endphp
      @if($rows->isNotEmpty())
        <tr class="cat"><td colspan="5">{{ $catLabel }}</td></tr>
        @foreach($rows as $idx => $item)
          <tr class="{{ $idx % 2 ? 'alt' : '' }}">
            <td style="padding-left:16px;">{{ $item->item_name }}</td>
            <td class="ctr">@if($item->isByGa())<span class="badge-ga">By GA</span>@else<span class="badge-self">Sendiri</span>@endif</td>
            <td class="num">{{ $item->qty }}</td>
            <td class="num">{{ number_format($item->unit_cost, 0, ',', '.') }}</td>
            <td class="num">{{ number_format($item->total_cost, 0, ',', '.') }}</td>
          </tr>
        @endforeach
      @endif
    @endforeach
    <tr class="total">
      <td colspan="4" style="text-align:right;">TOTAL ANGGARAN</td>
      <td class="num">Rp {{ number_format($perdin->total_budget, 0, ',', '.') }}</td>
    </tr>
  </tbody>
</table>

<table class="sumrow">
  <tr>
    <td><div class="s-lbl">Ditanggung Sendiri</div><div class="s-val" style="color:#198754;">Rp {{ number_format($perdin->total_budget_self, 0, ',', '.') }}</div></td>
    <td><div class="s-lbl">Ditanggung GA</div><div class="s-val" style="color:#1d4ed8;">Rp {{ number_format($byGaTotal, 0, ',', '.') }}</div></td>
    <td><div class="s-lbl">Total Anggaran</div><div class="s-val" style="color:{{ $primary }};">Rp {{ number_format($perdin->total_budget, 0, ',', '.') }}</div></td>
  </tr>
</table>

<div class="note">
  * Item dengan penanggung <strong>By GA</strong> diatur dan dibayarkan langsung oleh bagian General Affairs.
</div>

<table class="signs">
  <tr>
    <td style="width:33.33%;"><div class="sg-head">DIBUAT OLEH</div><div class="sg-cap">Karyawan</div></td>
    <td style="width:33.33%;"><div class="sg-head">DIKETAHUI</div><div class="sg-cap">Manager HR &amp; GA</div></td>
    <td style="width:33.33%;"><div class="sg-head">DISETUJUI</div><div class="sg-cap">Direktur Utama</div></td>
  </tr>
  <tr>
    <td class="sg-space"></td>
    <td class="sg-space">@if($sigHr)<span class="stamp">TTD</span>@endif</td>
    <td class="sg-space">@if($sigCeo)<span class="stamp">TTD</span>@endif</td>
  </tr>
  <tr>
    <td class="sg-name"><span class="nm">{{ $perdin->user->name }}</span></td>
    <td class="sg-name"><span class="nm">{{ $sigHr?->approver?->name ?? ' ' }}</span></td>
    <td class="sg-name"><span class="nm">{{ $sigCeo?->approver?->name ?? ' ' }}</span></td>
  </tr>
</table>

{{-- ═══════════ HALAMAN 3 — ITINERARY ═══════════ --}}
@if($perdin->itineraries->isNotEmpty())
<div class="page-break"></div>
<table class="lh">
  <tr>
    <td><div class="company" style="font-size:14pt;">{{ $company['name'] }}</div>
        <div class="tagline">{{ $company['tagline'] }}</div></td>
    <td style="text-align:right; vertical-align:bottom;">
      <div style="font-size:8pt; color:#888;">No. Advance</div>
      <div style="font-size:10pt; font-weight:bold; color:{{ $primary }};">{{ $perdin->no_advance }}</div>
    </td>
  </tr>
</table>
<div class="accent"></div>
<div class="rule"></div>
<div class="title-bar">Itinerary Perjalanan</div>

<div class="route-sub">Rute Perjalanan: <b>{{ $perdin->routeLabel() }}</b></div>

<table class="data">
  <thead>
    <tr>
      <th class="ctr" style="width:6%">No</th>
      <th style="width:28%">Hari &amp; Tanggal</th>
      <th class="ctr" style="width:16%">Jam</th>
      <th class="ctr" style="width:10%">Zona</th>
      <th style="width:40%">Keterangan</th>
    </tr>
  </thead>
  <tbody>
    @foreach($perdin->itineraries as $idx => $it)
    <tr class="{{ $idx % 2 ? 'alt' : '' }}">
      <td class="ctr">{{ $it->no }}</td>
      <td>{{ $it->travel_date->locale('id')->isoFormat('dddd, D MMM Y') }}</td>
      <td class="ctr">{{ substr($it->time_start ?? '', 0, 5) }}@if($it->time_end) – {{ substr($it->time_end, 0, 5) }}@endif</td>
      <td class="ctr">{{ $it->timezone }}</td>
      <td>{{ $it->description }}</td>
    </tr>
    @endforeach
  </tbody>
</table>

<table class="signs" style="margin-top:30px;">
  <tr>
    <td style="width:34%;"><div class="sg-head">DIBUAT OLEH</div><div class="sg-cap">Karyawan</div></td>
    <td style="width:66%; border:none;"></td>
  </tr>
  <tr>
    <td class="sg-space"></td>
    <td style="border:none;"></td>
  </tr>
  <tr>
    <td class="sg-name"><span class="nm">{{ $perdin->user->name }}</span><div class="sg-date">{{ $fmtDate($perdin->created_at) }}</div></td>
    <td style="border:none;"></td>
  </tr>
</table>
@endif

</body>
</html>
