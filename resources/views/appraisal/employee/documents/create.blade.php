@extends('layouts.grain')
@section('title', 'Unggah Dokumen — ' . $employee->name)

@section('content')
@include('components.notification')

<div class="mb-3">
  <div class="h4 mb-0">Unggah Dokumen</div>
  <small class="text-muted">
    <a href="{{ route('appraisal.employees.index') }}">Karyawan</a> /
    <a href="{{ route('appraisal.employees.edit', $employee) }}">{{ $employee->name }}</a> /
    <a href="{{ route('appraisal.employees.documents.index', $employee) }}">Dokumen</a> /
    Unggah
  </small>
</div>

<div class="card" style="max-width:640px">
  <div class="card-body">
    <form method="POST"
          action="{{ route('appraisal.employees.documents.store', $employee) }}"
          enctype="multipart/form-data">
      @csrf

      <div class="form-group">
        <label class="font-weight-bold">Jenis Dokumen <span class="text-danger">*</span></label>
        <select name="doc_type" class="form-control @error('doc_type') is-invalid @enderror" required>
          <option value="">-- Pilih jenis --</option>
          @foreach($docTypes as $key => $label)
            <option value="{{ $key }}" {{ old('doc_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
        @error('doc_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="font-weight-bold">Judul Dokumen <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
               value="{{ old('title') }}" placeholder="Contoh: KTP atas nama Budi" required>
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="font-weight-bold">File <span class="text-danger">*</span></label>
        <div class="custom-file">
          <input type="file" name="file" id="fileInput"
                 class="custom-file-input @error('file') is-invalid @enderror"
                 accept=".pdf,.jpg,.jpeg,.png" required>
          <label class="custom-file-label" for="fileInput">Pilih file</label>
        </div>
        <small class="form-text text-muted">PDF, JPG, PNG. Maks 10 MB.</small>
        @error('file')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="font-weight-bold">Tanggal Kadaluarsa</label>
        <input type="date" name="expires_at" class="form-control @error('expires_at') is-invalid @enderror"
               value="{{ old('expires_at') }}">
        <small class="form-text text-muted">Opsional. Isi jika dokumen memiliki masa berlaku (misal SIM, sertifikasi).</small>
        @error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="font-weight-bold">Keterangan</label>
        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                  rows="2" placeholder="Catatan tambahan (opsional)">{{ old('notes') }}</textarea>
        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Unggah</button>
        <a href="{{ route('appraisal.employees.documents.index', $employee) }}"
           class="btn btn-outline-secondary ml-2">Batal</a>
      </div>
    </form>
  </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('fileInput').addEventListener('change', function () {
  var label = this.nextElementSibling;
  label.textContent = this.files.length ? this.files[0].name : 'Pilih file';
});
</script>
@endsection
