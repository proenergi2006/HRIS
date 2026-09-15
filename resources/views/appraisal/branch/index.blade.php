@extends('layouts.grain')
@section('title', 'Cabang')

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
  <div class="card-body">
    <nav class="d-none d-md-block" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Struktur Organisasi</li>
        <li class="breadcrumb-item active">Cabang</li>
      </ol>
    </nav>

    <div class="h3 mb-4">Cabang</div>
    <p class="text-muted small">Lokasi kerja per perusahaan (mis. HO, Jakarta, Surabaya). Dipakai sebagai pilihan
      "Cabang" di form Karyawan &amp; Jabatan, dan sebagai tingkat teratas di Bagan Organisasi.</p>

    <form method="POST" action="{{ route('appraisal.branches.store') }}" class="mb-4">
      @csrf
      <div class="form-row align-items-end">
        <div class="form-group col-6 col-md-2 mb-2">
          <label class="small text-muted mb-1">Kode <span class="text-danger">*</span></label>
          <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" required maxlength="20" value="{{ old('code') }}">
        </div>
        <div class="form-group col-6 col-md-3 mb-2">
          <label class="small text-muted mb-1">Nama Cabang <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required value="{{ old('name') }}" placeholder="mis. HO, Jakarta">
        </div>
        <div class="form-group col-6 col-md-3 mb-2">
          <label class="small text-muted mb-1">Perusahaan <span class="text-danger">*</span></label>
          <select name="company_id" class="form-control" required>
            <option value="">— pilih —</option>
            @foreach($companies as $c)<option value="{{ $c->id }}" @selected(old('company_id') == $c->id)>{{ $c->short_name ?? $c->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-6 col-md-3 mb-2">
          <label class="small text-muted mb-1">Kepala Cabang</label>
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

    @if($branches->isEmpty())
      <p class="text-muted small">Belum ada cabang.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light">
            <tr>
              <th style="width:100px">Kode</th><th>Nama</th><th style="width:150px">Perusahaan</th>
              <th style="width:180px">Kepala</th>
              <th class="text-center" style="width:80px">Karyawan</th>
              <th class="text-center" style="width:70px">Aktif</th><th style="width:80px"></th>
            </tr>
          </thead>
          <tbody>
          @foreach($branches as $b)
            @php $f = 'brc-' . $b->id; @endphp
            <tr>
              <td><input type="text" name="code" form="{{ $f }}" value="{{ $b->code }}" class="form-control form-control-sm"></td>
              <td><input type="text" name="name" form="{{ $f }}" value="{{ $b->name }}" class="form-control form-control-sm"></td>
              <td>
                <select name="company_id" form="{{ $f }}" class="form-control form-control-sm">
                  @foreach($companies as $c)<option value="{{ $c->id }}" @selected($b->company_id == $c->id)>{{ $c->short_name ?? $c->name }}</option>@endforeach
                </select>
              </td>
              <td>
                <select name="head_employee_id" form="{{ $f }}" class="form-control form-control-sm">
                  <option value="">—</option>
                  @foreach($employees as $e)<option value="{{ $e->id }}" @selected($b->head_employee_id == $e->id)>{{ $e->name }}</option>@endforeach
                </select>
              </td>
              <td class="text-center align-middle">{{ $b->employees_count }}</td>
              <td class="text-center align-middle">
                <input type="hidden" name="is_active" form="{{ $f }}" value="0">
                <div class="custom-control custom-switch d-inline-block">
                  <input type="checkbox" class="custom-control-input" id="bact-{{ $b->id }}" form="{{ $f }}" name="is_active" value="1" {{ $b->is_active ? 'checked' : '' }}>
                  <label class="custom-control-label" for="bact-{{ $b->id }}"></label>
                </div>
              </td>
              <td class="align-middle text-nowrap">
                <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                <a href="#" class="btn btn-xs btn-outline-danger"
                   data-confirm="Hapus cabang {{ $b->name }}?" data-confirm-title="Hapus Cabang"
                   data-form="del-brc-{{ $b->id }}"
                   @if($b->employees_count > 0 || $b->positions_count > 0) style="pointer-events:none;opacity:.4" title="Masih dipakai" @endif>
                  <i class="gd-trash"></i>
                </a>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>

      @foreach($branches as $b)
        <form id="brc-{{ $b->id }}" method="POST" action="{{ route('appraisal.branches.update', $b) }}" class="d-none">@csrf @method('PUT')</form>
        <form id="del-brc-{{ $b->id }}" method="POST" action="{{ route('appraisal.branches.destroy', $b) }}" class="d-none">@csrf @method('DELETE')</form>
      @endforeach
    @endif
  </div>
</div>
@endsection
