@extends('layouts.grain')
@section('title', 'Grid 9-Kotak — Performa × Potensi')

@section('content')
@include('components.notification')

<div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:.5rem">
  <div class="h3 mb-0">Grid 9-Kotak — Performa &times; Potensi</div>
  <a href="{{ route('succession.positions') }}" class="btn btn-sm btn-outline-secondary">&larr; Succession Planning</a>
</div>

<form method="GET" class="form-inline mb-3">
  <select name="company_id" class="form-control form-control-sm" onchange="this.form.submit()">
    <option value="">— Konsolidasi Grup —</option>
    @foreach($companies as $c)
      <option value="{{ $c->id }}" @selected($companyId == $c->id)>{{ $c->name }}</option>
    @endforeach
  </select>
</form>

<div class="alert alert-info small mb-4">
  Sumbu <strong>Performa</strong> dari skor Penilaian Kinerja terakhir yang sudah Final (&lt;60
  Rendah, 60&ndash;80 Sedang, &gt;80 Tinggi). Sumbu <strong>Potensi</strong> dinilai manual HR
  di tabel bawah. Karyawan tanpa salah satu data tampil di daftar "Belum Lengkap" di bawah grid.
</div>

@php
  $cellStyle = [
    'high-low' => '#fef3c7', 'high-medium' => '#bbf7d0', 'high-high' => '#86efac',
    'medium-low' => '#fee2e2', 'medium-medium' => '#fef3c7', 'medium-high' => '#bbf7d0',
    'low-low' => '#fecaca', 'low-medium' => '#fee2e2', 'low-high' => '#fef3c7',
  ];
@endphp

<div class="card mb-4">
  <div class="card-body">
    <div style="display:grid;grid-template-columns:90px repeat(3,1fr);gap:6px;min-width:640px" class="overflow-auto">
      <div></div>
      @foreach(['low' => 'Performa Rendah', 'medium' => 'Performa Sedang', 'high' => 'Performa Tinggi'] as $pk => $pl)
        <div class="text-center small font-weight-bold text-muted">{{ $pl }}</div>
      @endforeach

      @foreach(['high' => 'Potensi Tinggi', 'medium' => 'Potensi Sedang', 'low' => 'Potensi Rendah'] as $potKey => $potLabel)
        <div class="d-flex align-items-center justify-content-center small font-weight-bold text-muted" style="writing-mode:vertical-rl;text-orientation:mixed">{{ $potLabel }}</div>
        @foreach(['low', 'medium', 'high'] as $perfKey)
          @php $cell = $grid[$potKey][$perfKey]; @endphp
          <div style="background:{{ $cellStyle[$potKey.'-'.$perfKey] }};border-radius:6px;padding:8px;min-height:110px">
            <div class="small font-weight-bold mb-1">{{ $cell->count() }} orang</div>
            @foreach($cell->take(6) as $r)
              <div class="small text-truncate" title="{{ $r['employee']->name }}">{{ $r['employee']->name }}</div>
            @endforeach
            @if($cell->count() > 6)<div class="small text-muted">+{{ $cell->count() - 6 }} lagi</div>@endif
          </div>
        @endforeach
      @endforeach
    </div>
  </div>
</div>

@if($unassessed->isNotEmpty())
<div class="card mb-4 border-warning">
  <div class="card-header font-weight-bold text-warning">Belum Lengkap Data ({{ $unassessed->count() }}) — nilai potensi di tabel bawah</div>
</div>
@endif

<div class="card">
  <div class="card-header font-weight-bold">Nilai Potensi per Karyawan</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light"><tr><th>Nama</th><th>Jabatan</th><th class="text-center">Skor Kinerja Terakhir</th><th style="width:160px">Potensi</th><th>Catatan</th><th style="width:70px"></th></tr></thead>
        <tbody>
        @foreach($rows->sortBy(fn($r)=>$r['employee']->name) as $r)
          @php $f = 'pot-form-' . $r['employee']->id; @endphp
          <tr>
            <td>{{ $r['employee']->name }}</td>
            <td class="small text-muted">{{ $r['employee']->position?->name ?? '—' }}</td>
            <td class="text-center">{{ $r['score'] !== null ? number_format($r['score'],1) : '— belum ada penilaian final' }}</td>
            <td>
              <select name="potential_rating" form="{{ $f }}" class="form-control form-control-sm" required>
                <option value="" disabled @selected(!$r['potential'])>— pilih —</option>
                <option value="low" @selected($r['potential']==='low')>Rendah</option>
                <option value="medium" @selected($r['potential']==='medium')>Sedang</option>
                <option value="high" @selected($r['potential']==='high')>Tinggi</option>
              </select>
            </td>
            <td><input type="text" name="potential_notes" form="{{ $f }}" value="{{ $r['employee']->potential_notes }}" class="form-control form-control-sm"></td>
            <td class="text-right text-nowrap">
              <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-primary">Simpan</button>
              <a href="{{ route('succession.potential.history', $r['employee']) }}" class="btn btn-xs btn-outline-secondary" title="Riwayat"><i class="gd-time"></i></a>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

@foreach($rows as $r)
  <form id="pot-form-{{ $r['employee']->id }}" method="POST" action="{{ route('succession.potential.update', $r['employee']) }}" class="d-none">@csrf @method('PUT')</form>
@endforeach
@endsection
