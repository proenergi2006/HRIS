@extends('layouts.grain')
@section('title', 'Input Lembur')

@section('content')
@include('components.notification')

<nav class="d-none d-md-block" aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('hr.overtime.index', ['company_id' => $companyId]) }}">Rekap Lembur</a></li>
    <li class="breadcrumb-item active">Input</li>
  </ol>
</nav>

<div class="mb-3">
  <div class="h3 mb-0">Input Lembur</div>
</div>

<div class="card mb-3">
  <div class="card-body">
    <form method="GET" class="form-row align-items-end mb-0" id="filterForm">
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Perusahaan</label>
        <select name="company_id" class="form-control form-control-sm" onchange="this.form.submit()">
          @foreach($companies as $c)
            <option value="{{ $c->id }}" {{ $companyId == $c->id ? 'selected' : '' }}>{{ $c->short_name ?? $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-auto mb-0">
        <label class="small font-weight-bold">Tanggal</label>
        <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()">
      </div>
    </form>
  </div>
</div>

@if($employees->isEmpty())
  <div class="alert alert-warning">Belum ada karyawan aktif untuk perusahaan ini.</div>
@else
<form method="POST" action="{{ route('hr.overtime.store') }}">
  @csrf
  <input type="hidden" name="company_id" value="{{ $companyId }}">
  <input type="hidden" name="date" value="{{ $date }}">

  <div class="card">
    <div class="card-header font-weight-bold">
      Lembur — {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light">
            <tr>
              <th>Nama</th>
              <th style="width:160px">Jam Lembur</th>
            </tr>
          </thead>
          <tbody>
            @foreach($employees as $emp)
            <tr>
              <td class="align-middle">
                <div class="font-weight-bold" style="font-size:.88rem">{{ $emp->name }}</div>
                <small class="text-muted">{{ $emp->nip }}</small>
              </td>
              <td>
                <div class="input-group input-group-sm">
                  <input type="number" name="overtime[{{ $emp->id }}]" class="form-control"
                         value="{{ isset($existingMinutes[$emp->id]) ? round($existingMinutes[$emp->id] / 60, 2) : '' }}"
                         min="0" step="0.5" placeholder="0">
                  <div class="input-group-append"><span class="input-group-text">jam</span></div>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
      <small class="text-muted">{{ $employees->count() }} karyawan — kosongkan/isi 0 jika tidak lembur</small>
      <button type="submit" class="btn btn-primary">Simpan Lembur</button>
    </div>
  </div>
</form>
@endif
@endsection
