@extends('layouts.grain')
@section('title', 'Catat Kasbon / Pinjaman')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('hr.loans.index', ['company_id' => $companyId]) }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>
<div class="h3 mb-4">Catat Kasbon / Pinjaman</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Perusahaan</label>
        <select name="company_id" class="form-control form-control-sm" onchange="this.form.submit()">
          @foreach($companies as $c)
            <option value="{{ $c->id }}" @selected($companyId == $c->id)>{{ $c->short_name ?? $c->name }}</option>
          @endforeach
        </select>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('hr.loans.store') }}">
      @csrf
      <div class="form-row">
        <div class="form-group col-md-5">
          <label>Karyawan <span class="text-danger">*</span></label>
          <select name="employee_id" class="form-control @error('employee_id') is-invalid @enderror" required>
            <option value="">— pilih —</option>
            @foreach($employees as $e)<option value="{{ $e->id }}" @selected(old('employee_id') == $e->id)>{{ $e->name }} ({{ $e->nip }})</option>@endforeach
          </select>
          @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-3">
          <label>Jenis <span class="text-danger">*</span></label>
          <select name="loan_type" class="form-control @error('loan_type') is-invalid @enderror" required>
            <option value="kasbon" @selected(old('loan_type') === 'kasbon')>Kasbon</option>
            <option value="pinjaman" @selected(old('loan_type') === 'pinjaman')>Pinjaman</option>
          </select>
          @error('loan_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-4">
          <label>No. Referensi</label>
          <input type="text" name="reference_no" class="form-control" value="{{ old('reference_no') }}" placeholder="opsional">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group col-md-4">
          <label>Jumlah Pokok (Rp) <span class="text-danger">*</span></label>
          <input type="number" data-rupiah name="principal" min="1" class="form-control @error('principal') is-invalid @enderror" value="{{ old('principal') }}" required>
          @error('principal')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-3">
          <label>Jumlah Cicilan (bulan) <span class="text-danger">*</span></label>
          <input type="number" name="installment_count" min="1" max="60" class="form-control @error('installment_count') is-invalid @enderror" value="{{ old('installment_count', 1) }}" required>
          @error('installment_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-2">
          <label>Mulai Bulan <span class="text-danger">*</span></label>
          <select name="start_month" class="form-control @error('start_month') is-invalid @enderror" required>
            @foreach(range(1, 12) as $m)<option value="{{ $m }}" @selected(old('start_month', now()->addMonthNoOverflow()->month) == $m)>{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>@endforeach
          </select>
          @error('start_month')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-3">
          <label>Mulai Tahun <span class="text-danger">*</span></label>
          <input type="number" name="start_year" class="form-control @error('start_year') is-invalid @enderror" value="{{ old('start_year', now()->year) }}" required>
          @error('start_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>
      <div class="form-group">
        <label>Catatan</label>
        <textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
      </div>
      <div class="d-flex justify-content-between mt-3">
        <a href="{{ route('hr.loans.index', ['company_id' => $companyId]) }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Simpan & Buat Jadwal Cicilan</button>
      </div>
    </form>
  </div>
</div>
@endsection
