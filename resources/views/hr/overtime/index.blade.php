@extends('layouts.grain')
@section('title', 'Rekap Lembur')

@section('content')
@include('components.notification')

<nav class="d-none d-md-block" aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Rekap Lembur</li>
  </ol>
</nav>

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Rekap Lembur</div>
  <a href="{{ route('hr.overtime.create', ['company_id' => $companyId]) }}" class="btn btn-primary btn-sm">
    <i class="gd-plus mr-1"></i> Input Lembur
  </a>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Perusahaan</label>
        <select name="company_id" class="form-control form-control-sm" onchange="this.form.submit()">
          @foreach($companies as $c)
            <option value="{{ $c->id }}" {{ $companyId == $c->id ? 'selected' : '' }}>{{ $c->short_name ?? $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-2 mb-0">
        <label class="small font-weight-bold">Bulan</label>
        <select name="month" class="form-control form-control-sm" onchange="this.form.submit()">
          @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $m)
            <option value="{{ $i+1 }}" {{ $month == $i+1 ? 'selected' : '' }}>{{ $m }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-2 mb-0">
        <label class="small font-weight-bold">Tahun</label>
        <input type="number" name="year" class="form-control form-control-sm" value="{{ $year }}" onchange="this.form.submit()">
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="thead-light">
          <tr>
            <th>Tanggal</th>
            <th>Karyawan</th>
            <th class="text-right">Jam Lembur</th>
          </tr>
        </thead>
        <tbody>
          @forelse($records as $r)
            <tr>
              <td>{{ \Carbon\Carbon::parse($r->date)->translatedFormat('d F Y') }}</td>
              <td>
                <div class="font-weight-bold" style="font-size:.88rem">{{ $r->employee?->name ?? '-' }}</div>
                <small class="text-muted">{{ $r->employee?->nip }}</small>
              </td>
              <td class="text-right font-weight-bold">{{ number_format($r->overtime_minutes / 60, 1) }} jam</td>
            </tr>
          @empty
            <tr><td colspan="3" class="text-center text-muted py-4">Belum ada data lembur bulan ini.</td></tr>
          @endforelse
        </tbody>
        @if($records->isNotEmpty())
        <tfoot class="table-light font-weight-bold">
          <tr>
            <td colspan="2">Total</td>
            <td class="text-right">{{ number_format($records->sum('overtime_minutes') / 60, 1) }} jam</td>
          </tr>
        </tfoot>
        @endif
      </table>
    </div>
  </div>
</div>
@endsection
