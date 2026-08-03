@extends('layouts.grain')
@section('title', 'Rekap Absensi')

@section('content')
@include('components.notification')

@php
  $monthNames = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  $dowLabel   = ['','Sen','Sel','Rab','Kam','Jum','Sab','Min'];

  $totalHadir = $totalTelat = $totalIzin = $totalAlpha = 0;
  foreach ($employees as $emp) {
    foreach ($records->get($emp->id, collect()) as $r) {
      if ($r->status === 'hadir')                          $totalHadir++;
      elseif ($r->status === 'telat')                    { $totalHadir++; $totalTelat++; }
      elseif (in_array($r->status, ['izin','sakit','cuti'])) $totalIzin++;
      elseif ($r->status === 'alpha')                      $totalAlpha++;
    }
  }

  // Warna diambil dari palet tema (.alert-* / .badge-* di graindashboard.css) agar seragam dgn seluruh app
  $cellBg  = [
    'hadir'  => '#cef8f1', 'telat' => '#fef7d8', 'izin'  => '#dadbfc',
    'sakit'  => '#d4dffc', 'cuti'  => '#cdf7fd', 'alpha' => '#fcdede',
    'libur'  => '#f2f2f2',
  ];
  $cellFg  = [
    'hadir'  => '#067260', 'telat' => '#836f1f', 'izin'  => '#25287c',
    'sakit'  => '#14307d', 'cuti'  => '#047080', 'alpha' => '#7c2f2f',
    'libur'  => '#adb5bd',
  ];
  $cellLbl = [
    'hadir'=>'H','telat'=>'T','izin'=>'I','sakit'=>'S','cuti'=>'C','alpha'=>'A','libur'=>'–',
  ];
@endphp

<nav class="d-none d-md-block" aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Rekap Absensi</li>
  </ol>
</nav>

{{-- Page header --}}
<div class="mb-3 d-flex justify-content-between align-items-start flex-wrap" style="gap:.5rem">
  <div>
    <div class="h3 mb-0">Rekap Absensi</div>
    <small class="text-muted">{{ $monthNames[$month] }} {{ $year }}</small>
  </div>
  <div class="d-flex" style="gap:.5rem">
    <a href="{{ route('hr.attendance.create', ['company_id' => $companyId]) }}" class="btn btn-primary btn-sm">
      <i class="gd-plus mr-1"></i> Input Absensi
    </a>
    <a href="{{ route('hr.attendance.import.form') }}" class="btn btn-outline-secondary btn-sm">
      <i class="gd-upload mr-1"></i> Import Mesin
    </a>
  </div>
</div>

{{-- Filter --}}
<div class="card mb-3 shadow-sm">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold text-muted">Perusahaan</label>
        <select name="company_id" class="form-control form-control-sm">
          @foreach($companies as $c)
            <option value="{{ $c->id }}" {{ $companyId == $c->id ? 'selected' : '' }}>{{ $c->short_name ?? $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-auto mb-0">
        <label class="small font-weight-bold text-muted">Bulan</label>
        <select name="month" class="form-control form-control-sm">
          @foreach(range(1,12) as $m)
            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $monthNames[$m] }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-auto mb-0">
        <label class="small font-weight-bold text-muted">Tahun</label>
        <select name="year" class="form-control form-control-sm">
          @foreach(range(now()->year + 1, now()->year - 3) as $y)
            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-auto mb-0">
        <button type="submit" class="btn btn-primary btn-sm px-4">Tampilkan</button>
      </div>
    </form>
  </div>
</div>

@if(!$employees->isEmpty())
{{-- Summary cards --}}
<div class="row mb-3">
  <div class="col-6 col-md-3 mb-2">
    <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #0cdcb9!important">
      <div class="card-body py-3 px-3">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="small text-muted mb-1">Total Hadir</div>
            <div class="h3 mb-0 font-weight-bold text-success">{{ $totalHadir }}</div>
          </div>
          <div style="width:42px;height:42px;background:#cef8f1;border-radius:50%;display:flex;align-items:center;justify-content:center">
            <i class="gd-check" style="color:#067260;font-size:1.1rem"></i>
          </div>
        </div>
        <div class="mt-1 small text-muted">pertemuan kehadiran</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3 mb-2">
    <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #fcd53b!important">
      <div class="card-body py-3 px-3">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="small text-muted mb-1">Terlambat</div>
            <div class="h3 mb-0 font-weight-bold text-warning">{{ $totalTelat }}</div>
          </div>
          <div style="width:42px;height:42px;background:#fef7d8;border-radius:50%;display:flex;align-items:center;justify-content:center">
            <i class="gd-time" style="color:#836f1f;font-size:1.1rem"></i>
          </div>
        </div>
        <div class="mt-1 small text-muted">pertemuan terlambat</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3 mb-2">
    <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #474dee!important">
      <div class="card-body py-3 px-3">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="small text-muted mb-1">Izin / Sakit / Cuti</div>
            <div class="h3 mb-0 font-weight-bold text-info">{{ $totalIzin }}</div>
          </div>
          <div style="width:42px;height:42px;background:#dadbfc;border-radius:50%;display:flex;align-items:center;justify-content:center">
            <i class="gd-user" style="color:#25287c;font-size:1.1rem"></i>
          </div>
        </div>
        <div class="mt-1 small text-muted">pertemuan tidak masuk</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3 mb-2">
    <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #ef5b5b!important">
      <div class="card-body py-3 px-3">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="small text-muted mb-1">Alpha</div>
            <div class="h3 mb-0 font-weight-bold text-danger">{{ $totalAlpha }}</div>
          </div>
          <div style="width:42px;height:42px;background:#fcdede;border-radius:50%;display:flex;align-items:center;justify-content:center">
            <i class="gd-close" style="color:#7c2f2f;font-size:1.1rem"></i>
          </div>
        </div>
        <div class="mt-1 small text-muted">pertemuan tanpa keterangan</div>
      </div>
    </div>
  </div>
</div>
@endif

{{-- Main attendance grid --}}
<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
    <span class="font-weight-bold" style="font-size:.95rem">
      <i class="gd-calendar mr-1"></i> {{ $monthNames[$month] }} {{ $year }}
    </span>
    <div>
      <span class="badge badge-success mr-1" style="font-size:.7rem">H = Hadir</span>
      <span class="badge badge-warning mr-1" style="font-size:.7rem">T = Terlambat</span>
      <span class="badge badge-info mr-1" style="font-size:.7rem">I = Izin</span>
      <span class="badge badge-primary mr-1" style="font-size:.7rem">S = Sakit</span>
      <span class="badge badge-secondary mr-1" style="font-size:.7rem">C = Cuti</span>
      <span class="badge badge-danger" style="font-size:.7rem">A = Alpha</span>
    </div>
  </div>

  @if($employees->isEmpty())
    <div class="card-body text-center text-muted py-5">
      <div style="font-size:3rem;opacity:.15">&#128100;</div>
      <div class="mt-2">Belum ada karyawan aktif untuk perusahaan ini.</div>
    </div>
  @else
  <div class="p-0" style="overflow-x:auto">
    <table style="border-collapse:collapse;font-size:.73rem;width:100%;min-width:900px">
      {{-- Header hari --}}
      <thead class="thead-light">
        <tr>
          <th style="min-width:150px;padding:6px 10px;text-align:left;border-right:1px solid #dee2e6;position:sticky;left:0;z-index:1;background:#f8f9fa">
            Karyawan
          </th>
          @for($d = 1; $d <= $daysInMonth; $d++)
            @php $dow = (int) date('N', mktime(0,0,0,$month,$d,$year)); @endphp
            <th style="padding:4px 2px;text-align:center;min-width:28px;color:{{ $dow >= 6 ? '#ef5b5b' : '#495057' }};border-right:1px solid #dee2e6;font-weight:{{ $dow >= 6 ? '700' : '500' }}">
              {{ $d }}<br><span style="font-size:.62rem;opacity:.75">{{ $dowLabel[$dow] }}</span>
            </th>
          @endfor
          <th style="padding:4px 6px;text-align:center;color:#067260;min-width:34px;border-left:2px solid #dee2e6">H</th>
          <th style="padding:4px 6px;text-align:center;color:#836f1f;min-width:34px">T</th>
          <th style="padding:4px 6px;text-align:center;color:#25287c;min-width:34px">I/S</th>
          <th style="padding:4px 6px;text-align:center;color:#7c2f2f;min-width:34px">A</th>
        </tr>
      </thead>
      <tbody>
        @foreach($employees as $idx => $emp)
        @php
          $empRecs = $records->get($emp->id, collect())->keyBy(fn($r) => $r->date->format('Y-m-d'));
          $hadir = $telat = $izin = $alpha = 0;
          $rowBg = $idx % 2 === 0 ? '#fff' : '#fafbfc';
        @endphp
        <tr style="background:{{ $rowBg }}">
          <td style="padding:6px 10px;border-right:1px solid #e5e9ef;border-bottom:1px solid #e5e9ef;position:sticky;left:0;z-index:1;background:{{ $rowBg }}">
            <div style="font-weight:600;color:#1a2e45;line-height:1.2">{{ $emp->name }}</div>
            <div style="font-size:.68rem;color:#8899aa">{{ $emp->nip ?? $emp->department?->name ?? '' }}</div>
          </td>
          @for($d = 1; $d <= $daysInMonth; $d++)
            @php
              $dateKey = sprintf('%04d-%02d-%02d', $year, $month, $d);
              $dow     = (int) date('N', strtotime($dateKey));
              $rec     = $empRecs[$dateKey] ?? null;
              $st      = $rec?->status ?? ($dow >= 6 ? 'libur' : null);
              if ($st === 'hadir')                             $hadir++;
              elseif ($st === 'telat')                       { $hadir++; $telat++; }
              elseif (in_array($st, ['izin','sakit','cuti'])) $izin++;
              elseif ($st === 'alpha')                         $alpha++;
              $bg  = $cellBg[$st]  ?? ($dow >= 6 ? '#f2f2f2' : 'transparent');
              $fg  = $cellFg[$st]  ?? '#ccc';
              $lbl = $cellLbl[$st] ?? '';
            @endphp
            <td style="text-align:center;padding:0;border-right:1px solid #e5e9ef;border-bottom:1px solid #e5e9ef;background:{{ $bg }}">
              <span style="display:block;padding:5px 2px;font-weight:700;color:{{ $fg }};font-size:.67rem;line-height:1">{{ $lbl }}</span>
            </td>
          @endfor
          {{-- Totals --}}
          <td style="text-align:center;border-left:2px solid #dee2e6;border-bottom:1px solid #e5e9ef;background:#cef8f1;font-weight:700;color:#067260;font-size:.78rem;padding:4px 2px">{{ $hadir }}</td>
          <td style="text-align:center;border-bottom:1px solid #e5e9ef;background:{{ $telat ? '#fef7d8' : 'transparent' }};color:{{ $telat ? '#836f1f' : '#ccc' }};font-size:.78rem;padding:4px 2px">{{ $telat ?: '–' }}</td>
          <td style="text-align:center;border-bottom:1px solid #e5e9ef;background:{{ $izin ? '#dadbfc' : 'transparent' }};color:{{ $izin ? '#25287c' : '#ccc' }};font-size:.78rem;padding:4px 2px">{{ $izin ?: '–' }}</td>
          <td style="text-align:center;border-bottom:1px solid #e5e9ef;background:{{ $alpha ? '#fcdede' : 'transparent' }};color:{{ $alpha ? '#7c2f2f' : '#ccc' }};font-weight:{{ $alpha ? '700' : '400' }};font-size:.78rem;padding:4px 2px">{{ $alpha ?: '–' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
@endsection
