@extends('layouts.grain')
@section('title', 'Periode Bonus / Insentif Baru')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('hr.bonus.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>
<div class="h3 mb-4">Periode Bonus / Insentif Baru</div>

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('hr.bonus.store') }}">
      @csrf
      <div class="form-row">
        <div class="form-group col-md-4">
          <label>Perusahaan <span class="text-danger">*</span></label>
          <select name="company_id" class="form-control @error('company_id') is-invalid @enderror" required>
            @foreach($companies as $c)<option value="{{ $c->id }}">{{ $c->short_name ?? $c->name }}</option>@endforeach
          </select>
          @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-5">
          <label>Nama <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="mis. Bonus Kinerja 2026" value="{{ old('name') }}" required>
          @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-3">
          <label>Jenis <span class="text-danger">*</span></label>
          <select name="bonus_type" class="form-control" required>
            @foreach(\App\Models\HR\BonusPeriod::$typeLabels as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group col-md-3">
          <label>Tanggal Pembayaran <span class="text-danger">*</span></label>
          <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror" value="{{ old('payment_date') }}" required>
          @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-4 align-self-center">
          <div class="custom-control custom-checkbox mt-3">
            <input type="checkbox" class="custom-control-input" id="is_taxable" name="is_taxable" value="1" checked>
            <label class="custom-control-label" for="is_taxable">Kena PPh21 (hitung pajak otomatis)</label>
          </div>
        </div>
      </div>
      <div class="d-flex justify-content-between mt-3">
        <a href="{{ route('hr.bonus.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Buat Periode</button>
      </div>
    </form>
  </div>
</div>
@endsection
