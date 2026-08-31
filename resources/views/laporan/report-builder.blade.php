@extends('layouts.grain')
@section('title', 'Report Builder')

@section('content')
@include('components.notification')

<div class="h3 mb-0">Laporan & Rekap</div>

<ul class="nav nav-pills my-4">
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.index') }}">Rekap Umum</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.attendance-leave') }}">Absensi &amp; Cuti</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.payroll') }}">Payroll Summary</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.headcount') }}">Headcount</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('laporan.analytics') }}">HR Analytics</a></li>
  <li class="nav-item"><a class="nav-link active" href="{{ route('laporan.report-builder') }}">Report Builder</a></li>
</ul>

<div class="alert alert-info small mb-4">
  Pilih dataset, kolom yang ingin ditampilkan, dan filter — lalu <strong>Tampilkan</strong> untuk
  pratinjau (maks. 200 baris) atau langsung <strong>Ekspor Excel</strong> untuk data lengkap
  (dipakai untuk analisis lanjutan / dashboard BI eksternal).
</div>

<div class="card mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route('laporan.report-builder') }}" id="rb-form">
      <div class="form-row">
        <div class="form-group col-md-3">
          <label class="small font-weight-bold">Dataset</label>
          <select name="dataset" id="rb-dataset" class="form-control" onchange="this.form.submit()">
            <option value="">— pilih dataset —</option>
            @foreach($datasets as $key => $def)
              <option value="{{ $key }}" @selected($dataset === $key)>{{ $def['label'] }}</option>
            @endforeach
          </select>
        </div>

        @if($dataset)
          @php $needs = $datasets[$dataset]['needs']; @endphp
          @if(in_array('company', $needs))
          <div class="form-group col-md-2">
            <label class="small font-weight-bold">PT</label>
            <select name="company_id" class="form-control">
              <option value="">Semua</option>
              @foreach($companies as $c)<option value="{{ $c->id }}" @selected(request('company_id') == $c->id)>{{ $c->name }}</option>@endforeach
            </select>
          </div>
          @endif
          @if(in_array('date_range', $needs))
          <div class="form-group col-md-2">
            <label class="small font-weight-bold">Dari Tanggal</label>
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
          </div>
          <div class="form-group col-md-2">
            <label class="small font-weight-bold">Sampai Tanggal</label>
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
          </div>
          @endif
          @if(in_array('payroll_period', $needs))
          <div class="form-group col-md-3">
            <label class="small font-weight-bold">Periode Payroll</label>
            <select name="payroll_period_id" class="form-control">
              <option value="">— pilih periode —</option>
              @foreach($periods as $p)<option value="{{ $p->id }}" @selected(request('payroll_period_id') == $p->id)>{{ $p->period_label }} ({{ ucfirst($p->status) }})</option>@endforeach
            </select>
          </div>
          @endif
        @endif
      </div>

      @if($dataset)
        <div class="form-group">
          <label class="small font-weight-bold d-block">Kolom</label>
          @foreach($datasets[$dataset]['columns'] as $key => $label)
            <div class="custom-control custom-checkbox custom-control-inline">
              <input type="checkbox" class="custom-control-input" id="col-{{ $key }}" name="columns[]" value="{{ $key }}"
                     @checked(empty($_GET['columns']) || in_array($key, $_GET['columns'] ?? []))>
              <label class="custom-control-label small" for="col-{{ $key }}">{{ $label }}</label>
            </div>
          @endforeach
        </div>

        <div class="d-flex" style="gap:.5rem">
          <button type="submit" class="btn btn-primary btn-sm"><i class="gd-search mr-1"></i>Tampilkan</button>
          <button type="submit" formaction="{{ route('laporan.report-builder.export') }}" class="btn btn-outline-success btn-sm">
            <i class="gd-file mr-1"></i>Ekspor Excel
          </button>
        </div>
      @endif
    </form>
  </div>
</div>

@if($dataset)
<div class="card">
  <div class="card-header font-weight-bold">Pratinjau — {{ $datasets[$dataset]['label'] }} ({{ $rows->count() }}@if($rows->count() >= 200)+@endif baris ditampilkan)</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light">
          <tr>@foreach($selectedColumns as $label)<th>{{ $label }}</th>@endforeach</tr>
        </thead>
        <tbody>
        @forelse($rows as $row)
          <tr>@foreach($row as $val)<td>{{ $val }}</td>@endforeach</tr>
        @empty
          <tr><td colspan="{{ count($selectedColumns) }}" class="text-muted small p-3">Tidak ada data untuk filter ini.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif
@endsection
