@extends('layouts.grain')
@section('title', 'Departemen')

@section('content')
@include('components.notification')

<div class="mb-3">
  <a href="{{ route('appraisal.employees.index') }}" class="text-muted small">
    <i class="gd-angle-left"></i> Kembali ke Data Karyawan
  </a>
</div>

<div class="h3 mb-4">Departemen</div>

<div class="card">
  <div class="card-header font-weight-bold">Daftar Departemen</div>
  <div class="card-body">

    <form method="POST" action="{{ route('appraisal.departments.store') }}" class="mb-4">
      @csrf
      <div class="form-row align-items-end">
        <div class="form-group col-6 col-md-2 mb-2">
          <label class="small text-muted mb-1">Kode <span class="text-danger">*</span></label>
          <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" required maxlength="20" value="{{ old('code') }}">
        </div>
        <div class="form-group col-6 col-md-3 mb-2">
          <label class="small text-muted mb-1">Nama Departemen <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required value="{{ old('name') }}">
        </div>
        <div class="form-group col-6 col-md-2 mb-2">
          <label class="small text-muted mb-1">Perusahaan</label>
          <select name="company_id" class="form-control">
            <option value="">Semua Perusahaan</option>
            @foreach($companies as $c)<option value="{{ $c->id }}" @selected(old('company_id') == $c->id)>{{ $c->short_name ?? $c->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-6 col-md-2 mb-2">
          <label class="small text-muted mb-1">Divisi</label>
          <select name="division_id" class="form-control">
            <option value="">—</option>
            @foreach($divisions as $dv)<option value="{{ $dv->id }}" @selected(old('division_id') == $dv->id)>{{ $dv->name }}{{ $dv->company ? ' (' . ($dv->company->short_name ?? $dv->company->name) . ')' : '' }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-6 col-md-2 mb-2">
          <label class="small text-muted mb-1">Kepala</label>
          <select name="head_employee_id" class="form-control">
            <option value="">—</option>
            @foreach($employees as $e)<option value="{{ $e->id }}" @selected(old('head_employee_id') == $e->id)>{{ $e->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-6 col-md-2 mb-2">
          <label class="small text-muted mb-1">Cost Center</label>
          <input type="text" name="cost_center" class="form-control" value="{{ old('cost_center') }}">
        </div>
        <div class="form-group col-auto mb-2">
          <button type="submit" class="btn btn-primary">Tambah</button>
        </div>
      </div>
    </form>

    @if($departments->isEmpty())
      <p class="text-muted small">Belum ada departemen. Tambahkan di atas.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light">
            <tr>
              <th style="width:100px">Kode</th><th>Nama</th>
              <th style="width:150px">Perusahaan</th><th style="width:160px">Divisi</th>
              <th style="width:170px">Kepala</th><th style="width:110px">Cost Center</th>
              <th class="text-center" style="width:60px">Kry</th>
              <th class="text-center" style="width:60px">Aktif</th><th style="width:80px"></th>
            </tr>
          </thead>
          <tbody>
          @foreach($departments as $d)
            @php $f = 'dept-form-' . $d->id; @endphp
            <tr>
              <td><input type="text" name="code" form="{{ $f }}" value="{{ old('code', $d->code) }}" class="form-control form-control-sm"></td>
              <td><input type="text" name="name" form="{{ $f }}" value="{{ old('name', $d->name) }}" class="form-control form-control-sm"></td>
              <td>
                <select name="company_id" form="{{ $f }}" class="form-control form-control-sm">
                  <option value="">Semua</option>
                  @foreach($companies as $c)<option value="{{ $c->id }}" @selected($d->company_id == $c->id)>{{ $c->short_name ?? $c->name }}</option>@endforeach
                </select>
              </td>
              <td>
                <select name="division_id" form="{{ $f }}" class="form-control form-control-sm">
                  <option value="">—</option>
                  @foreach($divisions as $dv)<option value="{{ $dv->id }}" @selected($d->division_id == $dv->id)>{{ $dv->name }}</option>@endforeach
                </select>
              </td>
              <td>
                <select name="head_employee_id" form="{{ $f }}" class="form-control form-control-sm">
                  <option value="">—</option>
                  @foreach($employees as $e)<option value="{{ $e->id }}" @selected($d->head_employee_id == $e->id)>{{ $e->name }}</option>@endforeach
                </select>
              </td>
              <td><input type="text" name="cost_center" form="{{ $f }}" value="{{ $d->cost_center }}" class="form-control form-control-sm"></td>
              <td class="text-center align-middle">{{ $d->employees_count }}</td>
              <td class="text-center align-middle">
                <input type="hidden" name="is_active" form="{{ $f }}" value="0">
                <div class="custom-control custom-switch d-inline-block">
                  <input type="checkbox" class="custom-control-input" id="dept-active-{{ $d->id }}" form="{{ $f }}" name="is_active" value="1" {{ $d->is_active ? 'checked' : '' }}>
                  <label class="custom-control-label" for="dept-active-{{ $d->id }}"></label>
                </div>
              </td>
              <td class="align-middle text-nowrap">
                <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                <a href="#" class="btn btn-xs btn-outline-danger"
                   data-confirm="Hapus departemen {{ $d->name }}?" data-confirm-title="Hapus Departemen"
                   data-form="dept-delete-{{ $d->id }}"
                   @if($d->employees_count > 0) style="pointer-events:none;opacity:.4" title="Masih dipakai karyawan" @endif>
                  <i class="gd-trash"></i>
                </a>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>

      @foreach($departments as $d)
        <form id="dept-form-{{ $d->id }}" method="POST" action="{{ route('appraisal.departments.update', $d) }}" class="d-none">@csrf @method('PUT')</form>
        <form id="dept-delete-{{ $d->id }}" method="POST" action="{{ route('appraisal.departments.destroy', $d) }}" class="d-none">@csrf @method('DELETE')</form>
      @endforeach
    @endif
  </div>
</div>
@endsection
