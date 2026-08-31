@extends('layouts.grain')
@section('title', 'OKR — Sasaran Perusahaan')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">OKR — Sasaran Perusahaan</div>
  <a href="{{ route('appraisal.okr.create', ['company_id' => $companyId, 'year' => $year]) }}" class="btn btn-primary btn-sm">
    <i class="gd-plus mr-1"></i> Sasaran Baru
  </a>
</div>
<p class="text-muted">Kaskade sasaran perusahaan &rarr; departemen &rarr; (nanti) KPI individu. Progres dihitung dari rata-rata capaian KPI yang ditautkan.</p>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Perusahaan</label>
        <select name="company_id" class="form-control form-control-sm" onchange="this.form.submit()">
          @foreach($companies as $c)<option value="{{ $c->id }}" @selected($companyId == $c->id)>{{ $c->short_name ?? $c->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-group col-md-2 mb-0">
        <label class="small font-weight-bold">Tahun</label>
        <input type="number" name="year" value="{{ $year }}" class="form-control form-control-sm" onchange="this.form.submit()">
      </div>
    </form>
  </div>
</div>

@if($objectives->isEmpty())
  <div class="card"><div class="card-body text-muted small">Belum ada sasaran untuk perusahaan &amp; tahun ini.</div></div>
@else
  @foreach($objectives as $o)
    @include('appraisal.okr._node', ['o' => $o, 'depth' => 0])
  @endforeach
@endif
@endsection
