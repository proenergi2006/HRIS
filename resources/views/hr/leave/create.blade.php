@extends('layouts.grain')
@section('title', 'Buat Pengajuan Cuti')

@section('content')
@include('components.notification')

<nav class="d-none d-md-block" aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('hr.leave.index') }}">Manajemen Cuti</a></li>
    <li class="breadcrumb-item active">Buat Pengajuan</li>
  </ol>
</nav>

<div class="mb-3">
  <div class="h3 mb-0">Buat Pengajuan Cuti</div>
</div>

<div class="card" style="max-width:640px">
  <div class="card-body">
    <form method="POST" action="{{ route('hr.leave.store') }}" enctype="multipart/form-data">
      @csrf

      <div class="form-group">
        <label class="font-weight-bold">Perusahaan <span class="text-danger">*</span></label>
        <select name="company_id" id="companySelect" class="form-control" required
                onchange="window.location.href='{{ route('hr.leave.create') }}?company_id='+this.value">
          @foreach($companies as $c)
            <option value="{{ $c->id }}" {{ $companyId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
          @endforeach
        </select>
        <small class="form-text text-muted">Pilih perusahaan untuk memuat daftar karyawan.</small>
      </div>

      <div class="form-group">
        <label class="font-weight-bold">Karyawan <span class="text-danger">*</span></label>
        <select name="employee_id" id="employeeSelect" class="form-control @error('employee_id') is-invalid @enderror" required>
          <option value="">-- Pilih karyawan --</option>
          @foreach($employees as $e)
            <option value="{{ $e->id }}" {{ old('employee_id') == $e->id ? 'selected' : '' }}>
              {{ $e->name }} ({{ $e->nip }})
            </option>
          @endforeach
        </select>
        @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="font-weight-bold">Jenis Cuti <span class="text-danger">*</span></label>
        <select name="leave_type_id" class="form-control @error('leave_type_id') is-invalid @enderror" required>
          <option value="">-- Pilih jenis --</option>
          @foreach($leaveTypes as $lt)
            <option value="{{ $lt->id }}" {{ old('leave_type_id') == $lt->id ? 'selected' : '' }}>
              {{ $lt->name }} @if($lt->days_per_year) ({{ $lt->days_per_year }} hari/tahun) @endif
            </option>
          @endforeach
        </select>
        @error('leave_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="form-row">
        <div class="form-group col">
          <label class="font-weight-bold">Dari Tanggal <span class="text-danger">*</span></label>
          <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                 value="{{ old('start_date') }}" required>
          @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col">
          <label class="font-weight-bold">Sampai Tanggal <span class="text-danger">*</span></label>
          <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                 value="{{ old('end_date') }}" required>
          @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="form-group">
        <label class="font-weight-bold">Alasan</label>
        <textarea name="reason" class="form-control" rows="3" placeholder="Opsional">{{ old('reason') }}</textarea>
      </div>

      <div class="form-group">
        <label class="font-weight-bold">Lampiran</label>
        <div class="custom-file">
          <input type="file" name="attachment" id="leaveFile" class="custom-file-input" accept=".pdf,.jpg,.jpeg,.png">
          <label class="custom-file-label" for="leaveFile">Pilih file</label>
        </div>
        <small class="form-text text-muted">PDF/JPG/PNG. Maks 5 MB. Wajib untuk cuti sakit/melahirkan.</small>
      </div>

      <div class="d-flex" style="gap:.5rem">
        <button type="submit" class="btn btn-primary">Simpan Pengajuan</button>
        <a href="{{ route('hr.leave.index') }}" class="btn btn-outline-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>

<script>
document.getElementById('leaveFile').addEventListener('change', function() {
  this.nextElementSibling.textContent = this.files.length ? this.files[0].name : 'Pilih file';
});
</script>
@endsection
