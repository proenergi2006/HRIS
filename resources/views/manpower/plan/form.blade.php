@extends('layouts.grain')
@section('title', $plan->id ? 'Edit Rencana Manpower' : 'Rencana Manpower Baru')

@section('content')
@include('components.notification')

<div class="mb-3">
  <a href="{{ route('manpower.plans.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a>
</div>

<div class="h3 mb-4">{{ $plan->id ? 'Edit Rencana Manpower' : 'Rencana Manpower Baru' }}</div>

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ $plan->id ? route('manpower.plans.update', $plan) : route('manpower.plans.store') }}">
      @if($plan->id) @method('PUT') @endif
      @csrf

      <div class="form-row">
        <div class="form-group col-md-3">
          <label>Perusahaan <span class="text-danger">*</span></label>
          <select name="company_id" class="form-control @error('company_id') is-invalid @enderror" required>
            @foreach($companies as $c)
              <option value="{{ $c->id }}" @selected(old('company_id', $plan->company_id) == $c->id)>{{ $c->short_name ?? $c->name }}</option>
            @endforeach
          </select>
          @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-3">
          <label>Departemen</label>
          <select name="department_id" class="form-control mp-department">
            <option value="">-- Semua Departemen --</option>
            @foreach($departments as $d)
              <option value="{{ $d->id }}" @selected(old('department_id', $plan->department_id) == $d->id)>{{ $d->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Section</label>
          <select name="section_id" class="form-control mp-section">
            <option value="">-- Semua Section --</option>
            @foreach($sections as $s)
              <option value="{{ $s->id }}" data-department="{{ $s->department_id }}" @selected(old('section_id', $plan->section_id) == $s->id)>{{ $s->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Jabatan</label>
          <select name="position_id" class="form-control">
            <option value="">-- Semua Jabatan --</option>
            @foreach($positions as $p)
              <option value="{{ $p->id }}" @selected(old('position_id', $plan->position_id) == $p->id)>{{ $p->name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <p class="small text-muted">
        Kosongkan Departemen/Section/Jabatan untuk rencana level company. Isi salah satu untuk
        rencana lebih spesifik (mis. cuma Departemen = rencana seluruh departemen itu).
      </p>

      <div class="form-row">
        <div class="form-group col-md-2">
          <label>Tahun <span class="text-danger">*</span></label>
          <input type="number" name="year" class="form-control @error('year') is-invalid @enderror"
                 value="{{ old('year', $plan->year ?? now()->year) }}" required>
          @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-2">
          <label>Bulan</label>
          <select name="month" class="form-control">
            <option value="">-- Tahunan --</option>
            @for($m = 1; $m <= 12; $m++)
              <option value="{{ $m }}" @selected(old('month', $plan->month) == $m)>{{ \Carbon\Carbon::create(2000, $m, 1)->translatedFormat('F') }}</option>
            @endfor
          </select>
        </div>
        <div class="form-group col-md-2">
          <label>Rencana Headcount <span class="text-danger">*</span></label>
          <input type="number" name="planned_headcount" min="0" class="form-control @error('planned_headcount') is-invalid @enderror"
                 value="{{ old('planned_headcount', $plan->planned_headcount) }}" required>
          @error('planned_headcount')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="form-group">
        <label>Catatan</label>
        <textarea name="notes" rows="3" class="form-control">{{ old('notes', $plan->notes) }}</textarea>
      </div>

      <div class="d-flex justify-content-between mt-3">
        <a href="{{ route('manpower.plans.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">{{ $plan->id ? 'Simpan Perubahan' : 'Simpan Draft' }}</button>
      </div>
    </form>
  </div>
</div>

<script>
// Section cuma boleh dari Departemen yang sedang dipilih (pola sama seperti form Jabatan).
(function () {
  var deptSelect = document.querySelector('.mp-department');
  var secSelect  = document.querySelector('.mp-section');
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
