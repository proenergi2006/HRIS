@extends('layouts.grain')
@section('title', 'Struktur Gaji Karyawan')
@section('content')
@include('components.notification')

<nav class="d-none d-md-block" aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('hr.payroll.index') }}">Penggajian</a></li>
    <li class="breadcrumb-item active">Struktur Gaji Karyawan</li>
  </ol>
</nav>

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Struktur Gaji Karyawan</div>
  <a href="{{ route('hr.payroll.index', ['company_id' => $companyId]) }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
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
          <th>Karyawan</th>
          @foreach($components as $c)<th class="text-right" style="min-width:100px;font-size:.8rem">{{ $c->name }}</th>@endforeach
          <th class="text-right">Total</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($employees as $emp)
        @php $empVals = $emp->salaryComponents->keyBy('salary_component_id'); @endphp
        <tr>
          <td>
            <div class="font-weight-bold" style="font-size:.88rem">{{ $emp->name }}</div>
            <small class="text-muted">{{ $emp->nip }}</small>
          </td>
          @php $totalGross = 0; @endphp
          @foreach($components as $c)
            @php $val = $empVals[$c->id]->amount ?? 0; if($c->type==='allowance') $totalGross += $val; @endphp
            <td class="text-right" style="font-size:.82rem">{{ $val ? number_format($val,0,',','.') : '-' }}</td>
          @endforeach
          <td class="text-right font-weight-bold">{{ $totalGross ? 'Rp '.number_format($totalGross,0,',','.') : '-' }}</td>
          <td><a href="{{ route('hr.payroll.salary.edit', $emp) }}" class="btn btn-xs btn-outline-primary">Edit</a></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
