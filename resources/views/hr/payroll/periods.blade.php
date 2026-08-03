@extends('layouts.grain')
@section('title', 'Periode Penggajian')

@section('content')
@include('components.notification')

<nav class="d-none d-md-block" aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Penggajian</li>
  </ol>
</nav>

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Penggajian</div>
  <div class="d-flex" style="gap:.5rem">
    <a href="{{ route('hr.payroll.components', ['company_id' => $companyId]) }}" class="btn btn-outline-secondary btn-sm">Komponen Gaji</a>
    <a href="{{ route('hr.payroll.salary.index', ['company_id' => $companyId]) }}" class="btn btn-outline-secondary btn-sm">Struktur Gaji</a>
    <a href="{{ route('hr.payroll.create') }}" class="btn btn-primary btn-sm">
      <i class="gd-plus mr-1"></i> Buat Periode
    </a>
  </div>
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
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead class="thead-light">
        <tr>
          <th>Periode</th>
          <th>Perusahaan</th>
          <th class="text-center">Jumlah Slip</th>
          <th class="text-center">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($periods as $p)
        <tr>
          <td class="font-weight-bold">{{ $p->period_label }}</td>
          <td>{{ $p->company?->short_name }}</td>
          <td class="text-center">{{ $p->slips_count ?? $p->slips()->count() }}</td>
          <td class="text-center">
            <span class="badge badge-{{ $p->status === 'closed' ? 'success' : 'warning' }}">
              {{ $p->status === 'closed' ? 'Ditutup' : 'Terbuka' }}
            </span>
          </td>
          <td>
            <a href="{{ route('hr.payroll.show', $p) }}" class="btn btn-xs btn-outline-primary">
              <i class="gd-eye mr-1"></i> Kelola
            </a>
          </td>
        </tr>
        @empty
        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada periode penggajian.</td></tr>
        @endforelse
      </tbody>
    </table>
    <div class="px-3 py-2">{{ $periods->links() }}</div>
  </div>
</div>
@endsection
