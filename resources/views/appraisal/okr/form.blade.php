@extends('layouts.grain')
@section('title', $okr->id ? 'Edit Sasaran' : 'Sasaran Baru')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('appraisal.okr.index', ['company_id' => $defaultCompanyId, 'year' => $defaultYear]) }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>
<div class="h3 mb-4">{{ $okr->id ? 'Edit Sasaran' : 'Sasaran Baru' }}</div>

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ $okr->id ? route('appraisal.okr.update', $okr) : route('appraisal.okr.store') }}">
      @if($okr->id) @method('PUT') @endif
      @csrf

      <div class="form-group">
        <label>Judul Sasaran <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $okr->title) }}" placeholder="mis. Tingkatkan retensi karyawan 10%" required>
      </div>
      <div class="form-group">
        <label>Deskripsi</label>
        <textarea name="description" rows="2" class="form-control">{{ old('description', $okr->description) }}</textarea>
      </div>
      <div class="form-row">
        <div class="form-group col-md-3">
          <label>Perusahaan <span class="text-danger">*</span></label>
          <select name="company_id" class="form-control" required>
            @foreach($companies as $c)<option value="{{ $c->id }}" @selected(old('company_id', $okr->company_id ?? $defaultCompanyId) == $c->id)>{{ $c->short_name ?? $c->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-md-3">
          <label>Departemen</label>
          <select name="department_id" class="form-control">
            <option value="">-- Level Perusahaan --</option>
            @foreach($departments as $d)<option value="{{ $d->id }}" @selected(old('department_id', $okr->department_id) == $d->id)>{{ $d->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-md-2">
          <label>Tahun <span class="text-danger">*</span></label>
          <input type="number" name="year" class="form-control" value="{{ old('year', $okr->year ?? $defaultYear) }}" required>
        </div>
        <div class="form-group col-md-2">
          <label>Triwulan</label>
          <select name="quarter" class="form-control">
            <option value="">Tahunan</option>
            @foreach([1,2,3,4] as $q)<option value="{{ $q }}" @selected(old('quarter', $okr->quarter) == $q)>Q{{ $q }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-md-2">
          <label>Status</label>
          <select name="status" class="form-control">
            @foreach(\App\Models\Appraisal\CompanyObjective::$statusLabels as $k => $l)<option value="{{ $k }}" @selected(old('status', $okr->status ?? 'active') == $k)>{{ $l }}</option>@endforeach
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group col-md-6">
          <label>Induk Sasaran (opsional — untuk kaskade)</label>
          <select name="parent_objective_id" class="form-control">
            <option value="">-- Tidak ada (level teratas) --</option>
            @foreach($parents as $p)<option value="{{ $p->id }}" @selected(old('parent_objective_id', $okr->parent_objective_id) == $p->id)>{{ $p->title }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-md-6">
          <label>PIC / Pemilik Sasaran</label>
          <select name="owner_employee_id" class="form-control">
            <option value="">-- Pilih --</option>
            @foreach($employees as $e)<option value="{{ $e->id }}" @selected(old('owner_employee_id', $okr->owner_employee_id) == $e->id)>{{ $e->name }}</option>@endforeach
          </select>
        </div>
      </div>

      <div class="d-flex justify-content-between mt-3">
        <a href="{{ route('appraisal.okr.index', ['company_id' => $defaultCompanyId, 'year' => $defaultYear]) }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection
