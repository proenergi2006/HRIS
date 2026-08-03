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

<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header font-weight-bold">Daftar Departemen</div>
      <div class="card-body">

        <form method="POST" action="{{ route('appraisal.departments.store') }}" class="mb-3">
          @csrf
          <div class="form-row">
            <div class="form-group col-3 mb-2">
              <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" placeholder="Kode" required maxlength="20">
            </div>
            <div class="form-group col-5 mb-2">
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Nama departemen" required>
            </div>
            <div class="form-group col-3 mb-2">
              <select name="company_id" class="form-control">
                <option value="">Semua Perusahaan</option>
                @foreach($companies as $c)
                  <option value="{{ $c->id }}">{{ $c->short_name ?? $c->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-1 mb-2">
              <button type="submit" class="btn btn-primary btn-block">+</button>
            </div>
          </div>
          @error('code')<div class="text-danger small">{{ $message }}</div>@enderror
          @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
        </form>

        @if($departments->isEmpty())
          <p class="text-muted small">Belum ada departemen. Tambahkan di atas.</p>
        @else
          <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead class="thead-light"><tr><th>Kode</th><th>Nama</th><th>Perusahaan</th><th class="text-center">Karyawan</th><th class="text-center">Aktif</th><th></th></tr></thead>
            <tbody>
            @foreach($departments as $d)
              <tr>
                <td style="width:110px"><input type="text" name="code" form="dept-form-{{ $d->id }}" value="{{ old('code', $d->code) }}" class="form-control form-control-sm"></td>
                <td><input type="text" name="name" form="dept-form-{{ $d->id }}" value="{{ old('name', $d->name) }}" class="form-control form-control-sm"></td>
                <td style="width:180px">
                  <select name="company_id" form="dept-form-{{ $d->id }}" class="form-control form-control-sm">
                    <option value="">Semua</option>
                    @foreach($companies as $c)
                      <option value="{{ $c->id }}" {{ $d->company_id == $c->id ? 'selected' : '' }}>{{ $c->short_name ?? $c->name }}</option>
                    @endforeach
                  </select>
                </td>
                <td class="text-center align-middle">{{ $d->employees_count }}</td>
                <td class="text-center align-middle" style="width:70px">
                  <div class="custom-control custom-switch">
                    <input type="hidden" name="is_active" form="dept-form-{{ $d->id }}" value="0">
                    <input type="checkbox" class="custom-control-input" id="dept-active-{{ $d->id }}" form="dept-form-{{ $d->id }}"
                           name="is_active" value="1" {{ $d->is_active ? 'checked' : '' }}>
                    <label class="custom-control-label" for="dept-active-{{ $d->id }}"></label>
                  </div>
                </td>
                <td style="width:70px;white-space:nowrap">
                  <button type="submit" form="dept-form-{{ $d->id }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                  <button type="submit" form="dept-delete-{{ $d->id }}" class="btn btn-xs btn-outline-danger"
                          onclick="return confirm('Hapus departemen {{ $d->name }}?')"
                          {{ $d->employees_count > 0 ? 'disabled title=Masih dipakai karyawan' : '' }}>
                    <i class="gd-trash"></i>
                  </button>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
          </div>

          @foreach($departments as $d)
            <form id="dept-form-{{ $d->id }}" method="POST" action="{{ route('appraisal.departments.update', $d) }}" class="d-none">
              @csrf @method('PUT')
            </form>
            <form id="dept-delete-{{ $d->id }}" method="POST" action="{{ route('appraisal.departments.destroy', $d) }}" class="d-none">
              @csrf @method('DELETE')
            </form>
          @endforeach
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
