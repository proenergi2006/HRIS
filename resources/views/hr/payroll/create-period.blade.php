@extends('layouts.grain')
@section('title', 'Buat Periode Penggajian')
@section('content')
@include('components.notification')

<nav class="d-none d-md-block" aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('hr.payroll.index') }}">Penggajian</a></li>
    <li class="breadcrumb-item active">Buat Periode</li>
  </ol>
</nav>

<div class="mb-3"><div class="h3 mb-0">Buat Periode Penggajian</div></div>
<div class="card" style="max-width:480px">
  <div class="card-body">
    <form method="POST" action="{{ route('hr.payroll.store') }}">
      @csrf
      <div class="form-group">
        <label class="font-weight-bold">Perusahaan <span class="text-danger">*</span></label>
        <select name="company_id" class="form-control" required>
          @foreach($companies as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-row">
        <div class="form-group col">
          <label class="font-weight-bold">Bulan <span class="text-danger">*</span></label>
          <select name="month" class="form-control" required>
            @foreach(range(1,12) as $m)
              <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="form-group col">
          <label class="font-weight-bold">Tahun <span class="text-danger">*</span></label>
          <select name="year" class="form-control" required>
            @foreach(range(now()->year, now()->year - 2) as $y)
              <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="font-weight-bold">Catatan</label>
        <input type="text" name="notes" class="form-control" placeholder="Opsional">
      </div>
      <button type="submit" class="btn btn-primary">Buat Periode</button>
      <a href="{{ route('hr.payroll.index') }}" class="btn btn-outline-secondary ml-2">Batal</a>
    </form>
  </div>
</div>
@endsection
