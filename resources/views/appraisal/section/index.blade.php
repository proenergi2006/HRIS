@extends('layouts.grain')
@section('title', 'Section')

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
  <div class="card-body">
    <nav class="d-none d-md-block" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Struktur Organisasi</li>
        <li class="breadcrumb-item active">Section</li>
      </ol>
    </nav>

    <div class="h3 mb-4">Section / Bagian</div>

    <form method="POST" action="{{ route('appraisal.sections.store') }}" class="mb-4">
      @csrf
      <div class="form-row align-items-end">
        <div class="form-group col-6 col-md-2 mb-2">
          <label class="small text-muted mb-1">Kode <span class="text-danger">*</span></label>
          <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" required maxlength="20" value="{{ old('code') }}">
        </div>
        <div class="form-group col-6 col-md-3 mb-2">
          <label class="small text-muted mb-1">Nama Section <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required value="{{ old('name') }}">
        </div>
        <div class="form-group col-6 col-md-3 mb-2">
          <label class="small text-muted mb-1">Departemen <span class="text-danger">*</span></label>
          <select name="department_id" class="form-control" required>
            <option value="">— pilih —</option>
            @foreach($departments as $d)
              <option value="{{ $d->id }}" @selected(old('department_id') == $d->id)>{{ $d->name }}{{ $d->company ? ' — ' . ($d->company->short_name ?? $d->company->name) : '' }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group col-6 col-md-3 mb-2">
          <label class="small text-muted mb-1">Kepala Section</label>
          <select name="head_employee_id" class="form-control">
            <option value="">—</option>
            @foreach($employees as $e)<option value="{{ $e->id }}" @selected(old('head_employee_id') == $e->id)>{{ $e->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-auto mb-2">
          <button type="submit" class="btn btn-primary">Tambah</button>
        </div>
      </div>
    </form>

    @if($sections->isEmpty())
      <p class="text-muted small">Belum ada section.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light">
            <tr>
              <th style="width:100px">Kode</th><th>Nama</th><th style="width:220px">Departemen</th>
              <th style="width:180px">Kepala</th>
              <th class="text-center" style="width:70px">Jabatan</th>
              <th class="text-center" style="width:70px">Aktif</th><th style="width:80px"></th>
            </tr>
          </thead>
          <tbody>
          @foreach($sections as $s)
            @php $f = 'sec-' . $s->id; @endphp
            <tr>
              <td><input type="text" name="code" form="{{ $f }}" value="{{ $s->code }}" class="form-control form-control-sm"></td>
              <td><input type="text" name="name" form="{{ $f }}" value="{{ $s->name }}" class="form-control form-control-sm"></td>
              <td>
                <select name="department_id" form="{{ $f }}" class="form-control form-control-sm">
                  @foreach($departments as $d)<option value="{{ $d->id }}" @selected($s->department_id == $d->id)>{{ $d->name }}</option>@endforeach
                </select>
              </td>
              <td>
                <select name="head_employee_id" form="{{ $f }}" class="form-control form-control-sm">
                  <option value="">—</option>
                  @foreach($employees as $e)<option value="{{ $e->id }}" @selected($s->head_employee_id == $e->id)>{{ $e->name }}</option>@endforeach
                </select>
              </td>
              <td class="text-center align-middle">{{ $s->positions_count }}</td>
              <td class="text-center align-middle">
                <input type="hidden" name="is_active" form="{{ $f }}" value="0">
                <div class="custom-control custom-switch d-inline-block">
                  <input type="checkbox" class="custom-control-input" id="sact-{{ $s->id }}" form="{{ $f }}" name="is_active" value="1" {{ $s->is_active ? 'checked' : '' }}>
                  <label class="custom-control-label" for="sact-{{ $s->id }}"></label>
                </div>
              </td>
              <td class="align-middle text-nowrap">
                <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                <a href="#" class="btn btn-xs btn-outline-danger"
                   data-confirm="Hapus section {{ $s->name }}?" data-confirm-title="Hapus Section"
                   data-form="del-sec-{{ $s->id }}"
                   @if($s->positions_count > 0 || $s->employees_count > 0) style="pointer-events:none;opacity:.4" title="Masih dipakai" @endif>
                  <i class="gd-trash"></i>
                </a>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>

      @foreach($sections as $s)
        <form id="sec-{{ $s->id }}" method="POST" action="{{ route('appraisal.sections.update', $s) }}" class="d-none">@csrf @method('PUT')</form>
        <form id="del-sec-{{ $s->id }}" method="POST" action="{{ route('appraisal.sections.destroy', $s) }}" class="d-none">@csrf @method('DELETE')</form>
      @endforeach
    @endif
  </div>
</div>
@endsection
