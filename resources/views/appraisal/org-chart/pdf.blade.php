<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Struktur Organisasi {{ $selectedCompany->name }}</title>
<style>
  {{-- DomPDF tidak mendukung flexbox/pseudo-element seperti versi web, jadi
       diagram kotak+garis di sini dibangun pakai <table> + border-collapse
       (didukung penuh oleh DomPDF). Hierarki diratakan jadi: grup (Cabang /
       Divisi / perusahaan) -> blok (Direksi opsional) -> baris tabel
       departemen (kolom = departemen, baris = level/jabatan). --}}
  @php
    $allBlocks = $pdfGroups->flatMap(fn ($g) => $g['blocks']);
    $maxDeptCount = max(1, $allBlocks->max(fn ($b) => $b['departments']->count()) ?: 1);
    $realDeptTotal = $allBlocks->flatMap(fn ($b) => $b['departments'])->filter(fn ($d) => $d['department']->id ?? null)->count();
    // Makin banyak departemen, makin kecil font supaya tetap muat & tidak terpotong.
    $baseFont = $maxDeptCount <= 3 ? 9 : ($maxDeptCount <= 5 ? 8.5 : ($maxDeptCount <= 8 ? 7.5 : 6.5));
    $deptTitleFont = $baseFont + 1;
  @endphp
  body { font-family: 'DejaVu Sans', sans-serif; font-size: {{ $baseFont }}px; color: #1a1a1a; margin: 0; padding: 0; }
  .header { background: #0F2A4A; color: #fff; padding: 16px 20px; margin-bottom: 18px; }
  .header h1 { margin: 0; font-size: 16px; font-weight: bold; }
  .header p  { margin: 4px 0 0; font-size: 10px; opacity: .85; }

  .root-wrap { width: 100%; text-align: center; margin-bottom: 0; }
  .root-box {
    display: inline-block; background: #0F2A4A; color: #fff; font-weight: bold;
    font-size: 13px; letter-spacing: .3px; padding: 10px 26px; border-radius: 6px;
  }
  .group-wrap { width: 100%; text-align: center; margin: 16px 0 0; }
  .group-box {
    display: inline-block; background: #166534; color: #fff; font-weight: bold;
    font-size: 11px; letter-spacing: .3px; padding: 6px 18px; border-radius: 5px;
  }
  .group-box .count { font-weight: normal; opacity: .85; }
  .direksi-wrap { width: 100%; text-align: center; margin: 12px 0 0; }
  .direksi-box {
    display: inline-block; background: #b45309; color: #fff; font-weight: bold;
    font-size: 10.5px; letter-spacing: .3px; padding: 5px 16px; border-radius: 5px;
  }
  .stub-table { width: 100%; border-collapse: collapse; }
  .stub-table td { border: 0; padding: 0; text-align: center; }
  .stub { display: inline-block; width: 0; height: 14px; border-left: 2px solid #94a3b8; }

  table.dept-row { border-collapse: collapse; table-layout: fixed; margin: 0 auto 4px; }
  table.dept-row > tr > td {
    border-top: 2px solid #94a3b8; vertical-align: top; padding: 12px 5px 0 5px;
  }

  .dept-box { border: 1px solid #cfe0f5; border-radius: 5px; overflow: hidden; }
  .dept-title {
    background: #eef3fb; color: #0F2A4A; font-weight: bold; font-size: {{ $deptTitleFont }}px;
    padding: 5px 7px; text-align: center; border-bottom: 1px solid #cfe0f5;
  }
  .dept-title .count { display: block; font-weight: normal; color: #6b7280; font-size: {{ $baseFont - 1 }}px; }

  .level-title {
    background: #fff7e6; color: #92600a; font-weight: bold; text-transform: uppercase;
    letter-spacing: .3px; font-size: {{ $baseFont - 1 }}px; padding: 3px 7px;
  }
  table.pos-table { width: 100%; border-collapse: collapse; }
  table.pos-table td { padding: 5px 6px; border-bottom: 1px solid #f0f2f5; vertical-align: top; font-size: {{ $baseFont }}px; }
  table.pos-table tr:last-child td { border-bottom: 0; }
  .pos-title { font-weight: bold; color: #1e293b; margin-bottom: 3px; }
  .emp-chip {
    display: inline-block; background: #f8f9fa; border: 1px solid #dee2e6;
    border-radius: 4px; padding: 2px 7px; font-size: {{ $baseFont - 0.5 }}px; margin: 1px 3px 1px 0; font-weight: bold;
  }
  .emp-chip.vacant { border-style: dashed; color: #9ca3af; font-weight: normal; font-style: italic; }
  .emp-reports { margin: 3px 0 2px 12px; padding-left: 8px; border-left: 2px solid #cbd5e1; }

  .footer { text-align: center; font-size: 9px; color: #adb5bd; margin-top: 20px; border-top: 1px solid #dee2e6; padding-top: 8px; }
</style>
</head>
<body>

<div style="padding:14px 20px 0;">
  @include('components.pdf-kop', ['company' => $selectedCompany])
</div>

<div class="header">
  <h1>Struktur Organisasi</h1>
  @php $grandTotal = $pdfGroups->sum('total'); @endphp
  <p>
    @if($showBranchTier){{ $pdfGroups->count() }} cabang &bull; @endif
    {{ $realDeptTotal }} departemen &bull; {{ $grandTotal }} karyawan aktif
    &nbsp;|&nbsp; Dicetak: {{ now()->format('d F Y, H:i') }} WIB
  </p>
</div>

<div class="root-wrap"><div class="root-box">{{ $selectedCompany->name }}</div></div>
<table class="stub-table"><tr><td><div class="stub"></div></td></tr></table>

@foreach($pdfGroups as $group)
  @if($group['label'])
    <div class="group-wrap">
      <div class="group-box">{{ $group['label'] }} <span class="count">&bull; {{ $group['total'] }} karyawan</span></div>
    </div>
    <table class="stub-table"><tr><td><div class="stub"></div></td></tr></table>
  @endif

  @foreach($group['blocks'] as $block)
    @if($block['direksi'])
      <div class="direksi-wrap"><div class="direksi-box">{{ $block['direksi'] }}</div></div>
      <table class="stub-table"><tr><td><div class="stub"></div></td></tr></table>
    @endif

    @php
      $deptCount = max(1, $block['departments']->count());
      // Blok sempit (1-3 kolom) jangan direntang selebar halaman — beri lebar
      // proporsional & ditengahkan supaya tidak banyak ruang kosong.
      $rowWidth = min(100, max(34, $deptCount * 26));
    @endphp
    <table class="dept-row" style="width: {{ $rowWidth }}%;">
      <tr>
        @foreach($block['departments'] as $dept)
          <td style="width: {{ number_format(100 / $deptCount, 3) }}%;">
            <div class="dept-box">
              <div class="dept-title">
                {{ $dept['department']->name ?? 'Tanpa Departemen' }}
                <span class="count">{{ $dept['total'] }} karyawan</span>
              </div>
              @foreach($dept['levels'] as $lvl)
                <div class="level-title">{{ $lvl['label'] }}</div>
                <table class="pos-table">
                  @foreach($lvl['positions'] as $pos)
                    <tr>
                      <td>
                        <div class="pos-title">{{ $pos['position']->name ?? 'Tanpa Jabatan' }}</div>
                        @forelse($pos['employees'] as $emp)
                          @include('appraisal.org-chart._employee-pdf', ['employee' => $emp, 'reportsByManager' => $reportsByManager])
                        @empty
                          <span class="emp-chip vacant">&mdash; belum terisi &mdash;</span>
                        @endforelse
                      </td>
                    </tr>
                  @endforeach
                </table>
              @endforeach
            </div>
          </td>
        @endforeach
      </tr>
    </table>
  @endforeach
@endforeach

<div class="footer">ProPeople &mdash; {{ $selectedCompany->name }} &mdash; Dokumen ini digenerate otomatis oleh sistem</div>
</body>
</html>
