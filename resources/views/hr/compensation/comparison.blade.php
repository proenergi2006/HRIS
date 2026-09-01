@extends('layouts.grain')
@section('title', 'Perbandingan Kompensasi')

@php
  $bandLabel = ['below' => 'Di Bawah Pasar', 'within' => 'Sesuai Pasar', 'above' => 'Di Atas Pasar'];
  $bandBadge = ['below' => 'danger', 'within' => 'success', 'above' => 'info'];
@endphp

@section('content')
@include('components.notification')

<div class="h3 mb-0">Kompensasi</div>
@include('hr.compensation._nav', ['active' => 'comparison'])

<form method="GET" class="form-inline mb-3">
  <label class="mr-2 small font-weight-bold">PT</label>
  <select name="company_id" class="form-control form-control-sm" onchange="this.form.submit()">
    <option value="">— Konsolidasi Grup —</option>
    @foreach($companies as $c)
      <option value="{{ $c->id }}" @selected($companyId == $c->id)>{{ $c->name }}</option>
    @endforeach
  </select>
</form>

<div class="alert alert-info small">
  Gaji aktual diambil dari <strong>slip gaji periode payroll terakhir yang sudah ditutup (closed)</strong>
  per karyawan — bukan hitung ulang komponen. Rasio Kompa = gaji aktual ÷ titik tengah benchmark × 100%.
  Di bawah 90% = di bawah pasar, di atas 110% = di atas pasar.
</div>

<div class="card mb-4">
  <div class="card-header font-weight-bold">Ringkasan per Level</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light">
          <tr>
            <th>Level</th><th class="text-right">Karyawan</th><th class="text-right">Ada Data Payroll</th>
            <th class="text-right">Rata-rata Gaji Aktual</th><th class="text-right">Benchmark Tengah</th>
            <th class="text-right">Rata-rata Kompa</th>
          </tr>
        </thead>
        <tbody>
        @forelse($byLevel as $levelName => $s)
          <tr>
            <td class="font-weight-bold">{{ $levelName }}</td>
            <td class="text-right">{{ $s['count'] }}</td>
            <td class="text-right">{{ $s['with_data'] }}</td>
            <td class="text-right">{{ $s['avg_current'] !== null ? 'Rp '.number_format($s['avg_current'],0,',','.') : '—' }}</td>
            <td class="text-right">{{ $s['benchmark'] ? 'Rp '.number_format($s['benchmark']->market_mid,0,',','.') : '— belum diisi' }}</td>
            <td class="text-right">
              @if($s['avg_compa'] !== null)
                <span class="badge badge-{{ $bandBadge[$s['avg_compa'] < 90 ? 'below' : ($s['avg_compa'] > 110 ? 'above' : 'within')] }}">{{ $s['avg_compa'] }}%</span>
              @else <span class="text-muted">—</span> @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-muted small p-3">Tidak ada data karyawan.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@if($belowMarket->isNotEmpty())
<div class="card mb-4 border-danger">
  <div class="card-header font-weight-bold text-danger">
    <i class="gd-alert mr-1"></i>Risiko Retensi — Kompa &lt; 90% ({{ $belowMarket->count() }} karyawan)
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light"><tr><th>Nama</th><th>Level</th><th>Jabatan</th><th class="text-right">Gaji Aktual</th><th class="text-right">Kompa</th></tr></thead>
        <tbody>
        @foreach($belowMarket as $r)
          <tr>
            <td>{{ $r['employee']->name }}</td>
            <td>{{ $r['employee']->level?->name ?? '—' }}</td>
            <td>{{ $r['employee']->position?->name ?? '—' }}</td>
            <td class="text-right">Rp {{ number_format($r['current'],0,',','.') }}</td>
            <td class="text-right"><span class="badge badge-danger">{{ $r['compa'] }}%</span></td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif

<div class="card">
  <div class="card-header font-weight-bold">Detail per Karyawan ({{ $rows->count() }})</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light">
          <tr><th>Nama</th><th>Level</th><th>Departemen</th><th class="text-right">Gaji Aktual</th><th class="text-right">Benchmark Tengah</th><th class="text-right">Kompa</th><th></th></tr>
        </thead>
        <tbody>
        @foreach($rows->sortBy(fn($r)=>$r['employee']->name) as $r)
          <tr>
            <td>{{ $r['employee']->name }}</td>
            <td>{{ $r['employee']->level?->name ?? '—' }}</td>
            <td class="small text-muted">{{ $r['employee']->department?->name ?? '—' }}</td>
            <td class="text-right">{{ $r['current'] !== null ? 'Rp '.number_format($r['current'],0,',','.') : '— tanpa slip closed' }}</td>
            <td class="text-right">{{ $r['benchmark'] ? 'Rp '.number_format($r['benchmark']->market_mid,0,',','.') : '—' }}</td>
            <td class="text-right">
              @if($r['compa'] !== null)
                <span class="badge badge-{{ $bandBadge[$r['band']] }}">{{ $r['compa'] }}%</span>
              @else <span class="text-muted">—</span> @endif
            </td>
            <td class="text-right"><a href="{{ route('hr.compensation.rewards', $r['employee']) }}" class="btn btn-xs btn-outline-secondary">Total Rewards</a></td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
