{{--
  Kerangka slip landscape (A4) yang dipakai bersama Slip Gaji, THR, dan Bonus.
  Palet tenang: navy + abu-abu (tanpa warna-warni). Semua panel ber-header navy.

  Param @include:
    title        string  — "SLIP GAJI" / "SLIP THR" / "SLIP BONUS"
    periodBadge  string  — teks di pil kanan atas
    subMeta      string  — baris kecil di bawah pil (opsional)
    company      Company|null
    employee     Employee
    closedBy     string|null — nama penandatangan "Disahkan oleh"
    facts        array   — ['NIP' => 'IT-001', ...] utk panel kiri
    stats        array   — [['n' => 21, 'l' => 'Hari Kerja'], ...] kotak angka (maks 4, opsional)
    leftNote     string|null — 1 baris kecil di bawah stats
    blocks       array   — [['head'=>'Pendapatan', 'rows'=>[['l'=>,'v'=>,'neg'=>bool], ...],
                             'total'=>['l'=>,'v'=>] | null], ...]
    net          array   — ['l' => 'Gaji Bersih — Take Home Pay', 'amount' => int]
    footNote     string
--}}
@php
    use App\Support\Branding;
    use App\Support\Terbilang;

    $co   = $company ?? $employee->company;
    $logo = Branding::pdfLogo($co?->code ?? config('sipro.company.code'));

    $coName = $co?->name ?? config('sipro.company.name');
    $coSub  = trim(collect([$co?->address, $co?->phone ? 'Telp ' . $co->phone : null])->filter()->implode('  ·  '));
    if ($coSub === '' && ($co?->code ?? config('sipro.company.code')) === config('sipro.company.code')) {
        $coSub = config('sipro.company.address') . '  ·  ' . config('sipro.company.website');
    }

    $stats     = $stats ?? [];
    $leftNote  = $leftNote ?? null;
    $subMeta   = $subMeta ?? null;
    $closedBy  = $closedBy ?? null;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>{{ $title }} — {{ $employee->name }}</title>
<style>
  @page { margin: 0; }
  * { box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; color: #2b3648; font-size: 9px; margin: 0; }

  .sheet { padding: 24px 34px 20px; }

  /* header */
  .kop { width: 100%; border-collapse: collapse; }
  .kop td { vertical-align: middle; }
  .kop .logo img { height: 38px; }
  .kop .co .name { font-size: 13.5px; font-weight: bold; color: #0f2a4a; letter-spacing: .3px; }
  .kop .co .sub  { font-size: 7px; color: #8894a6; margin-top: 2px; }
  .kop .doc { text-align: right; }
  .kop .doc .title { font-size: 18px; font-weight: bold; color: #0f2a4a; letter-spacing: 5px; }
  .kop .doc .per   { display: inline-block; margin-top: 5px; border: 1px solid #0f2a4a; color: #0f2a4a;
                     font-size: 8px; font-weight: bold; letter-spacing: 1px; padding: 3px 11px; border-radius: 10px; }
  .kop .doc .meta  { font-size: 7px; color: #8894a6; margin-top: 5px; }

  .rule  { height: 3px; background: #0f2a4a; margin: 10px 0 0; }
  .rule2 { height: 1px; background: #c9a13b; margin: 0 0 16px; }

  /* 2-col body */
  .grid { width: 100%; border-collapse: collapse; }
  .grid > tbody > tr > td, .grid > tr > td { vertical-align: top; }
  .grid .gap { width: 14px; }

  .panel { border: 1px solid #dde3ec; border-radius: 7px; margin-bottom: 12px; }
  .panel:last-child { margin-bottom: 0; }
  .p-head { background: #0f2a4a; color: #fff; font-size: 7.5px; font-weight: bold; letter-spacing: 1.6px;
            text-transform: uppercase; padding: 7px 12px; border-radius: 6px 6px 0 0; }
  .p-body { padding: 11px 13px; }

  .emp-name { font-size: 14px; font-weight: bold; color: #0f2a4a; }
  .emp-role { font-size: 8.5px; color: #8894a6; margin: 2px 0 10px; }
  .fact { width: 100%; border-collapse: collapse; }
  .fact td { padding: 4px 0; font-size: 8.4px; vertical-align: top; border-bottom: 1px solid #f0f2f7; }
  .fact tr:last-child td { border-bottom: 0; }
  .fact .k { color: #8894a6; }
  .fact .v { color: #2b3648; font-weight: bold; text-align: right; }

  .stats { width: 100%; border-collapse: separate; border-spacing: 6px; margin-top: 10px; table-layout: fixed; }
  .stats td { background: #f3f6fb; border-radius: 6px; text-align: center; padding: 8px 1px; }
  .stats .n { font-size: 14px; font-weight: bold; color: #0f2a4a; }
  .stats .l { font-size: 6px; color: #8894a6; text-transform: uppercase; letter-spacing: .4px; margin-top: 2px; }
  .left-note { font-size: 7.5px; color: #8894a6; margin-top: 10px; text-align: center; }

  .lines { width: 100%; border-collapse: collapse; }
  .lines td { padding: 5.5px 0; font-size: 8.7px; border-bottom: 1px solid #eef1f6; }
  .lines td.a { text-align: right; font-weight: bold; color: #2b3648; white-space: nowrap; }
  .lines tr:last-of-type td { border-bottom: 0; }
  .lines tr.sub td { border-top: 1.5px solid #0f2a4a; border-bottom: 0;
                     padding-top: 8px; font-weight: bold; color: #0f2a4a; font-size: 9.5px; }

  .net-wrap { background: #0f2a4a; border-radius: 8px; margin-top: 14px; padding: 16px 24px; }
  .net { width: 100%; border-collapse: collapse; }
  .net td { background: transparent; vertical-align: middle; border: 0; padding: 0; }
  .net .lbl { font-size: 8px; letter-spacing: 2.5px; text-transform: uppercase; color: #9fc0e6; }
  .net .say { font-size: 7.5px; color: #c9d8ec; font-style: italic; margin-top: 4px; }
  .net .amt { font-size: 24px; font-weight: bold; text-align: right; letter-spacing: .5px;
              white-space: nowrap; color: #fff; }
  .net .amt .cur { font-size: 12px; font-weight: normal; color: #9fc0e6; }

  .sign { width: 100%; border-collapse: collapse; margin-top: 20px; }
  .sign td { width: 33.33%; text-align: center; font-size: 8px; color: #8894a6; padding: 0 30px; }
  .sign .role { font-weight: bold; color: #2b3648; letter-spacing: .3px; }
  .sign .sp   { height: 46px; }
  .sign .who  { border-top: 1px solid #9aa4b5; padding-top: 4px; color: #2b3648; }

  .foot { text-align: center; font-size: 6.5px; color: #aab2c0; margin-top: 14px;
          border-top: 1px solid #eceff4; padding-top: 8px; }
</style>
</head>
<body>
<div class="sheet">

  {{-- header --}}
  <table class="kop">
    <tr>
      <td style="width:58%">
        @if($logo)
          <table><tr>
            <td style="padding-right:12px"><span class="logo"><img src="{{ $logo }}" alt=""></span></td>
            <td class="co"><div class="name">{{ strtoupper($coName) }}</div>
              @if($coSub)<div class="sub">{{ $coSub }}</div>@endif</td>
          </tr></table>
        @else
          <div class="co"><div class="name">{{ strtoupper($coName) }}</div>
            @if($coSub)<div class="sub">{{ $coSub }}</div>@endif</div>
        @endif
      </td>
      <td class="doc">
        <div class="title">{{ $title }}</div>
        <div class="per">{{ strtoupper($periodBadge) }}</div>
        <div class="meta">{{ $subMeta ? $subMeta . ' · ' : '' }}Dicetak {{ now()->translatedFormat('d F Y, H:i') }} WIB · Dokumen Rahasia</div>
      </td>
    </tr>
  </table>
  <div class="rule"></div>
  <div class="rule2"></div>

  {{-- body --}}
  <table class="grid">
    <tr>
      <td style="width:37%"><div class="panel">
          <div class="p-head">Data Karyawan</div>
          <div class="p-body">
            <div class="emp-name">{{ $employee->name }}</div>
            <div class="emp-role">{{ $employee->position?->name ?? '—' }} &bull; {{ $employee->department?->name ?? '—' }}</div>
            <table class="fact">
              @foreach($facts as $k => $v)
                <tr><td class="k">{{ $k }}</td><td class="v">{{ $v }}</td></tr>
              @endforeach
            </table>
            @if(count($stats))
              <table class="stats">
                <tr>
                  @foreach($stats as $s)
                    <td><div class="n">{{ $s['n'] }}</div><div class="l">{{ $s['l'] }}</div></td>
                  @endforeach
                </tr>
              </table>
            @endif
            @if($leftNote)
              <div class="left-note">{{ $leftNote }}</div>
            @endif
          </div>
        </div>
      </td>

      <td class="gap"></td>

      <td>@foreach($blocks as $block)<div class="panel">
            <div class="p-head">{{ $block['head'] }}</div>
            <div class="p-body">
              <table class="lines">
                @foreach($block['rows'] as $row)
                  <tr>
                    <td>{{ $row['l'] }}</td>
                    <td class="a">{{ ($row['neg'] ?? false) ? '− ' : '' }}{{ $row['v'] }}</td>
                  </tr>
                @endforeach
                @if(!empty($block['total']))
                  <tr class="sub"><td>{{ $block['total']['l'] }}</td><td class="a">{{ $block['total']['v'] }}</td></tr>
                @endif
              </table>
            </div>
          </div>@endforeach</td>
    </tr>
  </table>

  {{-- net --}}
  <div class="net-wrap">
    <table class="net">
      <tr>
        <td style="width:54%">
          <div class="lbl">{{ $net['l'] }}</div>
          <div class="say">{{ Terbilang::rupiah($net['amount']) }}</div>
        </td>
        <td class="amt"><span class="cur">Rp</span> {{ number_format((float) $net['amount'], 0, ',', '.') }}</td>
      </tr>
    </table>
  </div>

  {{-- signatures --}}
  <table class="sign">
    <tr>
      <td><div class="role">Dibuat oleh</div><div class="sp"></div><div class="who">HRD / Payroll</div></td>
      <td><div class="role">Disahkan oleh</div><div class="sp"></div><div class="who">{{ $closedBy ?? 'Manajer HR' }}</div></td>
      <td><div class="role">Diterima oleh</div><div class="sp"></div><div class="who">{{ $employee->name }}</div></td>
    </tr>
  </table>

  <div class="foot">{{ $footNote }}</div>
</div>
</body>
</html>
