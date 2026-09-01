@extends('layouts.grain')
@section('title', 'Ringkasan Kesiapan Suksesi')

@section('content')
@include('components.notification')

<div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:.5rem">
  <div class="h3 mb-0">Ringkasan Kesiapan Suksesi</div>
  <div style="gap:.5rem" class="d-flex">
    <a href="{{ route('succession.nine-box') }}" class="btn btn-sm btn-outline-primary"><i class="gd-view-grid mr-1"></i>Grid 9-Kotak</a>
    <a href="{{ route('succession.positions') }}" class="btn btn-sm btn-outline-secondary">&larr; Daftar Jabatan</a>
  </div>
</div>

<form method="GET" class="form-inline mb-3">
  <select name="company_id" class="form-control form-control-sm" onchange="this.form.submit()">
    <option value="">— Konsolidasi Grup —</option>
    @foreach($companies as $c)
      <option value="{{ $c->id }}" @selected($companyId == $c->id)>{{ $c->name }}</option>
    @endforeach
  </select>
</form>

<div class="row mb-4">
  <div class="col-md-4 mb-3">
    <div class="card h-100"><div class="card-body">
      <div class="text-muted small">Jabatan Kritikal</div>
      <div class="h2 mb-0">{{ $totalCritical }}</div>
    </div></div>
  </div>
  <div class="col-md-4 mb-3">
    <div class="card h-100"><div class="card-body">
      <div class="text-muted small">Punya ≥1 Kandidat Pengganti</div>
      <div class="h2 mb-0">{{ $covered }} <span class="small text-muted">/ {{ $totalCritical }}</span></div>
    </div></div>
  </div>
  <div class="col-md-4 mb-3">
    <div class="card h-100"><div class="card-body">
      <div class="text-muted small">Siap Sekarang (Ready Now)</div>
      <div class="h2 mb-0">{{ $readyNow }} <span class="small text-muted">/ {{ $totalCritical }}</span></div>
    </div></div>
  </div>
</div>

@if($gaps->isNotEmpty())
<div class="card mb-4 border-danger">
  <div class="card-header font-weight-bold text-danger"><i class="gd-alert mr-1"></i>Gap — Jabatan Kritikal Tanpa Kandidat Pengganti ({{ $gaps->count() }})</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light"><tr><th>Jabatan</th><th>Departemen</th><th>Risiko</th><th></th></tr></thead>
        <tbody>
        @foreach($gaps as $p)
          <tr>
            <td class="font-weight-bold">{{ $p->name }}</td>
            <td class="small text-muted">{{ $p->department?->name ?? '—' }}</td>
            <td><span class="badge badge-{{ \App\Models\Position::$successionRiskBadges[$p->succession_risk] ?? 'secondary' }}">{{ \App\Models\Position::$successionRiskLabels[$p->succession_risk] ?? 'Belum diisi' }}</span></td>
            <td class="text-right"><a href="{{ route('succession.show', $p) }}" class="btn btn-xs btn-outline-primary">Tambah Kandidat</a></td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif

<div class="card">
  <div class="card-header font-weight-bold">Semua Jabatan Kritikal</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light"><tr><th>Jabatan</th><th>Departemen</th><th>Risiko</th><th class="text-center">Kandidat</th><th class="text-center">Siap Sekarang</th></tr></thead>
        <tbody>
        @forelse($critical as $p)
          <tr>
            <td class="font-weight-bold"><a href="{{ route('succession.show', $p) }}">{{ $p->name }}</a></td>
            <td class="small text-muted">{{ $p->department?->name ?? '—' }}</td>
            <td><span class="badge badge-{{ \App\Models\Position::$successionRiskBadges[$p->succession_risk] ?? 'secondary' }}">{{ \App\Models\Position::$successionRiskLabels[$p->succession_risk] ?? 'Belum diisi' }}</span></td>
            <td class="text-center">{{ $p->talentPool->count() }}</td>
            <td class="text-center">
              @if($p->talentPool->where('readiness','ready_now')->isNotEmpty())
                <i class="gd-check text-success"></i>
              @else <span class="text-muted">—</span> @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-muted small p-3">Belum ada jabatan yang ditandai kritikal.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
