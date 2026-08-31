@extends('layouts.grain')
@section('title', $requisition->id ? 'Edit Requisition' : 'Requisition Baru')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('recruitment.requisitions.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="h3 mb-4">{{ $requisition->id ? 'Edit Requisition' : 'Job Requisition Baru' }}</div>

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ $requisition->id ? route('recruitment.requisitions.update', $requisition) : route('recruitment.requisitions.store') }}">
      @if($requisition->id) @method('PUT') @endif
      @csrf

      <div class="form-row">
        <div class="form-group col-md-6">
          <label>Judul Posisi <span class="text-danger">*</span></label>
          <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                 value="{{ old('title', $requisition->title) }}" placeholder="mis. Staff Accounting" required>
          @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-3">
          <label>Perusahaan <span class="text-danger">*</span></label>
          <select name="company_id" class="form-control @error('company_id') is-invalid @enderror" required>
            @foreach($companies as $c)
              <option value="{{ $c->id }}" @selected(old('company_id', $requisition->company_id) == $c->id)>{{ $c->short_name ?? $c->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Headcount Diminta <span class="text-danger">*</span></label>
          <input type="number" name="headcount_requested" min="1" class="form-control @error('headcount_requested') is-invalid @enderror"
                 value="{{ old('headcount_requested', $requisition->headcount_requested ?? 1) }}" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-3">
          <label>Departemen</label>
          <select name="department_id" class="form-control req-department">
            <option value="">-- Pilih --</option>
            @foreach($departments as $d)
              <option value="{{ $d->id }}" @selected(old('department_id', $requisition->department_id) == $d->id)>{{ $d->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Section</label>
          <select name="section_id" class="form-control req-section">
            <option value="">-- Pilih --</option>
            @foreach($sections as $s)
              <option value="{{ $s->id }}" data-department="{{ $s->department_id }}" @selected(old('section_id', $requisition->section_id) == $s->id)>{{ $s->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Isi Jabatan Kosong Existing</label>
          <select name="position_id" class="form-control">
            <option value="">-- Jabatan Baru --</option>
            @foreach($positions as $p)
              <option value="{{ $p->id }}" @selected(old('position_id', $requisition->position_id) == $p->id)>{{ $p->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Tipe Karyawan</label>
          <select name="employment_type_id" class="form-control">
            <option value="">-- Pilih --</option>
            @foreach($employmentTypes as $et)
              <option value="{{ $et->id }}" @selected(old('employment_type_id', $requisition->employment_type_id) == $et->id)>{{ $et->name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-3">
          <label>Target Tanggal Bergabung</label>
          <input type="date" name="target_join_date" class="form-control"
                 value="{{ old('target_join_date', optional($requisition->target_join_date)->format('Y-m-d')) }}">
        </div>
      </div>

      <div class="form-group">
        <label>Alasan / Justifikasi</label>
        <textarea name="reason" rows="3" class="form-control">{{ old('reason', $requisition->reason) }}</textarea>
      </div>

      <div class="d-flex justify-content-between mt-3">
        <a href="{{ route('recruitment.requisitions.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">{{ $requisition->id ? 'Simpan Perubahan' : 'Simpan Draft' }}</button>
      </div>
    </form>
  </div>
</div>

<script>
(function () {
  var deptSelect = document.querySelector('.req-department');
  var secSelect  = document.querySelector('.req-section');
  if (!deptSelect || !secSelect) return;

  function filterSections() {
    var deptId = deptSelect.value;
    var current = secSelect.value;
    var stillValid = false;
    Array.from(secSelect.options).forEach(function (opt) {
      if (!opt.value) return;
      var visible = !deptId || opt.dataset.department === deptId;
      opt.hidden = !visible;
      if (opt.value === current && visible) stillValid = true;
    });
    if (!stillValid) secSelect.value = '';
  }

  filterSections();
  deptSelect.addEventListener('change', filterSections);
})();
</script>
@endsection
