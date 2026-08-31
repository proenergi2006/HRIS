@extends('layouts.grain')
@section('title', 'Periode THR Baru')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('hr.thr.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="h3 mb-4">Periode THR Baru</div>

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('hr.thr.store') }}">
      @csrf
      <div class="form-row">
        <div class="form-group col-md-4">
          <label>Perusahaan <span class="text-danger">*</span></label>
          <select name="company_id" class="form-control @error('company_id') is-invalid @enderror" required>
            @foreach($companies as $c)<option value="{{ $c->id }}">{{ $c->short_name ?? $c->name }}</option>@endforeach
          </select>
          @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-2">
          <label>Tahun <span class="text-danger">*</span></label>
          <input type="number" name="year" class="form-control @error('year') is-invalid @enderror" value="{{ old('year', now()->year) }}" required>
          @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-3">
          <label>Hari Raya <span class="text-danger">*</span></label>
          <input type="text" name="holiday_name" class="form-control @error('holiday_name') is-invalid @enderror" placeholder="mis. Idul Fitri 1447H" value="{{ old('holiday_name') }}" required>
          @error('holiday_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-3">
          <label>Tanggal Pembayaran <span class="text-danger">*</span></label>
          <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror" value="{{ old('payment_date') }}" required>
          @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="d-flex justify-content-between mt-3">
        <a href="{{ route('hr.thr.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Buat Periode</button>
      </div>
    </form>
  </div>
</div>
@endsection
