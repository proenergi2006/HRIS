@extends('layouts.grain')
@section('title', 'Divisi')

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
  <div class="card-body">
    <nav class="d-none d-md-block" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Struktur Organisasi</li>
        <li class="breadcrumb-item active">Divisi</li>
      </ol>
    </nav>

    <div class="h3 mb-4">Divisi</div>

    <form method="POST" action="{{ route('appraisal.divisions.store') }}" class="mb-4">
      @csrf
      <div class="form-row align-items-end">
        <div class="form-group col-6 col-md-2 mb-2">
          <label class="small text-muted mb-1">Kode <span class="text-danger">*</span></label>
          <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" required maxlength="20" value="{{ old('code') }}">
        </div>
        <div class="form-group col-6 col-md-3 mb-2">
          <label class="small text-muted mb-1">Nama Divisi <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required value="{{ old('name') }}">
        </div>
        <div class="form-group col-6 col-md-2 mb-2">
          <label class="small text-muted mb-1">Perusahaan <span class="text-danger">*</span></label>
          <select name="company_id" class="form-control" required>
            <option value="">— pilih —</option>
            @foreach($companies as $c)<option value="{{ $c->id }}" @selected(old('company_id') == $c->id)>{{ $c->short_name ?? $c->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-6 col-md-2 mb-2">
          <label class="small text-muted mb-1">Kepala Divisi</label>
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

    @if($divisions->isEmpty())
      <p class="text-muted small">Belum ada divisi.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light">
            <tr>
              <th style="width:100px">Kode</th><th>Nama</th><th style="width:150px">Perusahaan</th>
              <th style="width:180px">Kepala</th><th style="width:110px">Cost Center</th>
              <th class="text-center" style="width:70px">Dept</th>
              <th class="text-center" style="width:70px">Aktif</th><th style="width:80px"></th>
            </tr>
          </thead>
          <tbody>
          @foreach($divisions as $d)
            @php $f = 'div-' . $d->id; @endphp
            <tr>
              <td><input type="text" name="code" form="{{ $f }}" value="{{ $d->code }}" class="form-control form-control-sm"></td>
              <td><input type="text" name="name" form="{{ $f }}" value="{{ $d->name }}" class="form-control form-control-sm"></td>
              <td>
                <select name="company_id" form="{{ $f }}" class="form-control form-control-sm">
                  @foreach($companies as $c)<option value="{{ $c->id }}" @selected($d->company_id == $c->id)>{{ $c->short_name ?? $c->name }}</option>@endforeach
                </select>
              </td>
              <td>
                <select name="head_employee_id" form="{{ $f }}" class="form-control form-control-sm">
                  <option value="">—</option>
                  @foreach($employees as $e)<option value="{{ $e->id }}" @selected($d->head_employee_id == $e->id)>{{ $e->name }}</option>@endforeach
                </select>
              </td>
              <td><input type="text" name="cost_center" form="{{ $f }}" value="{{ $d->cost_center }}" class="form-control form-control-sm"></td>
              <td class="text-center align-middle">{{ $d->departments_count }}</td>
              <td class="text-center align-middle">
                <input type="hidden" name="is_active" form="{{ $f }}" value="0">
                <div class="custom-control custom-switch d-inline-block">
                  <input type="checkbox" class="custom-control-input" id="dact-{{ $d->id }}" form="{{ $f }}" name="is_active" value="1" {{ $d->is_active ? 'checked' : '' }}>
                  <label class="custom-control-label" for="dact-{{ $d->id }}"></label>
                </div>
              </td>
              <td class="align-middle text-nowrap">
                <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                <a href="#" class="btn btn-xs btn-outline-danger"
                   data-confirm="Hapus divisi {{ $d->name }}?" data-confirm-title="Hapus Divisi"
                   data-form="del-div-{{ $d->id }}"
                   @if($d->departments_count > 0 || $d->employees_count > 0) style="pointer-events:none;opacity:.4" title="Masih dipakai" @endif>
                  <i class="gd-trash"></i>
                </a>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>

      @foreach($divisions as $d)
        <form id="div-{{ $d->id }}" method="POST" action="{{ route('appraisal.divisions.update', $d) }}" class="d-none">@csrf @method('PUT')</form>
        <form id="del-div-{{ $d->id }}" method="POST" action="{{ route('appraisal.divisions.destroy', $d) }}" class="d-none">@csrf @method('DELETE')</form>
      @endforeach
    @endif
  </div>
</div>
@endsection
