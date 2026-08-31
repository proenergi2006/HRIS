@extends('layouts.grain')
@section('title', 'Manpower Planning')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Manpower Planning</div>
  @can('manpower-plan.create')
  <a href="{{ route('manpower.plans.create') }}" class="btn btn-primary btn-sm">
    <i class="gd-plus mr-1"></i> Rencana Baru
  </a>
  @endcan
</div>

<div class="row mb-3">
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-body py-3">
        <div class="text-muted small">Total Grup (semua company, {{ $year }})</div>
        <div class="h4 mb-0">
          {{ $groupActual }} aktual
          <span class="text-muted small font-weight-normal">/ {{ $groupPlanned }} rencana disetujui</span>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Perusahaan</label>
        <select name="company_id" class="form-control form-control-sm" onchange="this.form.submit()">
          @foreach($companies as $c)
            <option value="{{ $c->id }}" @selected($companyId == $c->id)>{{ $c->short_name ?? $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-2 mb-0">
        <label class="small font-weight-bold">Tahun</label>
        <input type="number" name="year" value="{{ $year }}" class="form-control form-control-sm" onchange="this.form.submit()">
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body">
    @if($plans->isEmpty())
      <p class="text-muted small mb-0">Belum ada rencana manpower untuk perusahaan &amp; tahun ini.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light">
            <tr>
              <th>Unit / Jabatan</th><th>Periode</th><th class="text-center">Rencana</th>
              <th class="text-center">Aktual</th><th class="text-center">Selisih</th>
              <th class="text-center">Direkrut</th><th class="text-center">Sisa Kuota</th>
              <th>Status</th><th>Diajukan Oleh</th><th></th>
            </tr>
          </thead>
          <tbody>
          @foreach($plans as $p)
            @php
              $actual = $p->actualHeadcount();
              $variance = $actual - $p->planned_headcount;
              $committed = $p->isApproved() ? $p->committedHeadcount() : 0;
              $remaining = $p->isApproved() ? $p->planned_headcount - $actual - $committed : null;
            @endphp
            <tr>
              <td>{{ $p->scopeLabel() }}</td>
              <td>{{ $p->periodLabel() }}</td>
              <td class="text-center">{{ $p->planned_headcount }}</td>
              <td class="text-center">{{ $actual }}</td>
              <td class="text-center {{ $variance < 0 ? 'text-danger' : ($variance > 0 ? 'text-warning' : 'text-success') }}">
                {{ $variance > 0 ? '+' : '' }}{{ $variance }}
              </td>
              <td class="text-center text-muted">{{ $p->isApproved() ? $committed : '—' }}</td>
              <td class="text-center {{ $remaining === null ? 'text-muted' : ($remaining < 0 ? 'text-danger font-weight-bold' : ($remaining === 0 ? 'text-warning' : 'text-success')) }}">
                {{ $remaining === null ? '—' : $remaining }}
              </td>
              <td><span class="badge badge-{{ \App\Models\ManpowerPlan::$statusBadges[$p->status] ?? 'secondary' }}">{{ \App\Models\ManpowerPlan::$statusLabels[$p->status] ?? $p->status }}</span></td>
              <td class="small text-muted">{{ $p->requestedBy?->name ?? '—' }}</td>
              <td><a href="{{ route('manpower.plans.show', $p) }}" class="btn btn-xs btn-outline-secondary">Detail</a></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
