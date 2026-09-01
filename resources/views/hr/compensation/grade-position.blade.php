@extends('layouts.grain')
@section('title', 'Posisi dalam Band Gaji')

@section('content')
@include('components.notification')

<div class="h3 mb-0">Kompensasi</div>
@include('hr.compensation._nav', ['active' => 'grade-position'])

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
  Posisi gaji aktual karyawan dalam <strong>band internal</strong>-nya: 0% = titik minimum band,
  100% = titik maksimum band. Karyawan di ≥95% band ("mentok band") butuh promosi/naik Level
  untuk bisa naik gaji lagi lewat merit increase — kenaikan gaji tanpa promosi hanya mungkin
  kalau masih ada ruang di bawah 100%.
</div>

@if($atCeiling->isNotEmpty())
<div class="card mb-4 border-warning">
  <div class="card-header font-weight-bold text-warning"><i class="gd-alert mr-1"></i>Mentok Band (&ge;95%) — {{ $atCeiling->count() }} karyawan</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light"><tr><th>Nama</th><th>Level</th><th>Jabatan</th><th class="text-right">Gaji Aktual</th><th class="text-right">Posisi dalam Band</th></tr></thead>
        <tbody>
        @foreach($atCeiling as $r)
          <tr>
            <td>{{ $r['employee']->name }}</td>
            <td>{{ $r['employee']->level?->name ?? '—' }}</td>
            <td>{{ $r['employee']->position?->name ?? '—' }}</td>
            <td class="text-right">Rp {{ number_format($r['current'],0,',','.') }}</td>
            <td class="text-right"><span class="badge badge-warning">{{ $r['position'] }}%</span></td>
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
        <thead class="thead-light"><tr><th>Nama</th><th>Level</th><th>Departemen</th><th class="text-right">Gaji Aktual</th><th>Band (Min–Maks)</th><th style="width:180px">Posisi dalam Band</th><th></th></tr></thead>
        <tbody>
        @foreach($rows->sortBy(fn($r)=>$r['employee']->name) as $r)
          <tr>
            <td>{{ $r['employee']->name }}</td>
            <td>{{ $r['employee']->level?->name ?? '—' }}</td>
            <td class="small text-muted">{{ $r['employee']->department?->name ?? '—' }}</td>
            <td class="text-right">{{ $r['current'] !== null ? 'Rp '.number_format($r['current'],0,',','.') : '— tanpa slip closed' }}</td>
            <td class="small">{{ $r['grade'] ? 'Rp '.number_format($r['grade']->grade_min,0,',','.').' – '.number_format($r['grade']->grade_max,0,',','.') : '— belum diisi' }}</td>
            <td>
              @if($r['position'] !== null)
                @php $pct = max(0, min(100, $r['position'])); @endphp
                <div class="progress" style="height:14px">
                  <div class="progress-bar {{ $r['at_ceiling'] ? 'bg-warning' : 'bg-primary' }}" style="width:{{ $pct }}%"></div>
                </div>
                <div class="small text-muted">{{ $r['position'] }}%</div>
              @else <span class="text-muted small">—</span> @endif
            </td>
            <td class="text-right">
              @can('hr-request.view')
                <a href="{{ route('approval.hr-request.create', ['salary-increase', 'employee_id' => $r['employee']->id]) }}" class="btn btn-xs btn-outline-primary">Ajukan Kenaikan</a>
              @endcan
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
