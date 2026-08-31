@extends('layouts.grain')
@section('title', 'Ajukan Perubahan Data')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('appraisal.employee-data-changes.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="h3 mb-4">Ajukan Perubahan Data — {{ $employee->name }}</div>

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('appraisal.employee-data-changes.store') }}">
      @csrf

      <div class="form-group">
        <label>Field yang Diubah <span class="text-danger">*</span></label>
        <select name="field_key" id="field_key" class="form-control @error('field_key') is-invalid @enderror" required>
          <option value="">-- Pilih Field --</option>
          @foreach(\App\Models\EmployeeDataChangeRequest::$fieldLabels as $key => $label)
            <option value="{{ $key }}" data-current="{{ $employee->{$key} }}" @selected(old('field_key') === $key)>{{ $label }}</option>
          @endforeach
        </select>
        @error('field_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label>Nilai Sekarang</label>
        <input type="text" id="current_value" class="form-control" value="" readonly disabled>
      </div>

      <div class="form-group">
        <label>Nilai Baru <span class="text-danger">*</span></label>
        <input type="text" name="new_value" class="form-control @error('new_value') is-invalid @enderror" value="{{ old('new_value') }}" required maxlength="1000">
        @error('new_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label>Alasan</label>
        <textarea name="reason" rows="3" class="form-control">{{ old('reason') }}</textarea>
      </div>

      <div class="d-flex justify-content-between mt-3">
        <a href="{{ route('appraisal.employee-data-changes.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Simpan Draft</button>
      </div>
    </form>
  </div>
</div>

<script>
document.getElementById('field_key').addEventListener('change', function () {
  var opt = this.options[this.selectedIndex];
  document.getElementById('current_value').value = opt.dataset.current || '(kosong)';
});
</script>
@endsection
