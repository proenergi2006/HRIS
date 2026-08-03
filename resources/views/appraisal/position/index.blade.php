@extends('layouts.grain')
@section('title', 'Jabatan')

@section('content')
@include('components.notification')

<div class="mb-3">
  <a href="{{ route('appraisal.employees.index') }}" class="text-muted small">
    <i class="gd-angle-left"></i> Kembali ke Data Karyawan
  </a>
</div>

<div class="h3 mb-4">Jabatan</div>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header font-weight-bold">Daftar Jabatan</div>
      <div class="card-body">

        <p class="small text-muted">
          <strong>Tunjangan Jabatan</strong>, <strong>Tarif Harian</strong> (dasar Tunjangan Makan &amp; Transport,
          dikalikan jumlah hari hadir), dan <strong>Tarif Lembur</strong> (Rp/jam) di sini otomatis dipakai saat
          Generate Slip — tidak perlu diisi manual lagi per karyawan.
        </p>

        <form method="POST" action="{{ route('appraisal.positions.store') }}" class="mb-3">
          @csrf
          <div class="form-row">
            <div class="form-group col-md-1 mb-2">
              <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" placeholder="Kode" required maxlength="20">
            </div>
            <div class="form-group col-md-3 mb-2">
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Nama jabatan" required>
            </div>
            <div class="form-group col-md-2 mb-2">
              <select name="department_id" class="form-control">
                <option value="">-- Departemen --</option>
                @foreach($departments as $dep)
                  <option value="{{ $dep->id }}">{{ $dep->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-2 mb-2">
              <select name="company_id" class="form-control">
                <option value="">Semua PT</option>
                @foreach($companies as $c)
                  <option value="{{ $c->id }}">{{ $c->short_name ?? $c->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-4 mb-2">
              <button type="submit" class="btn btn-primary btn-block">Tambah Jabatan</button>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-4 mb-2">
              <input type="number" name="tunjangan_jabatan" class="form-control" placeholder="Tunjangan Jabatan (Rp)" min="0" step="1000">
            </div>
            <div class="form-group col-md-4 mb-2">
              <input type="number" name="tunjangan_harian" class="form-control" placeholder="Tarif Makan & Transport /hari (Rp)" min="0" step="1000">
            </div>
            <div class="form-group col-md-4 mb-2">
              <input type="number" name="tarif_lembur" class="form-control" placeholder="Tarif Lembur /jam (Rp)" min="0" step="1000">
            </div>
          </div>
          @error('code')<div class="text-danger small">{{ $message }}</div>@enderror
          @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
        </form>

        @if($positions->isEmpty())
          <p class="text-muted small">Belum ada jabatan. Tambahkan di atas.</p>
        @else
          <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead class="thead-light">
              <tr>
                <th>Kode</th><th>Nama</th><th>Departemen</th><th>Perusahaan</th>
                <th>Tunjangan Jabatan</th><th>Tarif/Hari</th><th>Tarif Lembur</th>
                <th class="text-center">Karyawan</th><th class="text-center">Aktif</th><th></th>
              </tr>
            </thead>
            <tbody>
            @foreach($positions as $p)
              <tr>
                <td style="width:90px"><input type="text" name="code" form="pos-form-{{ $p->id }}" value="{{ $p->code }}" class="form-control form-control-sm"></td>
                <td style="min-width:150px"><input type="text" name="name" form="pos-form-{{ $p->id }}" value="{{ $p->name }}" class="form-control form-control-sm"></td>
                <td style="width:150px">
                  <select name="department_id" form="pos-form-{{ $p->id }}" class="form-control form-control-sm">
                    <option value="">-- Departemen --</option>
                    @foreach($departments as $dep)
                      <option value="{{ $dep->id }}" {{ $p->department_id == $dep->id ? 'selected' : '' }}>{{ $dep->name }}</option>
                    @endforeach
                  </select>
                </td>
                <td style="width:130px">
                  <select name="company_id" form="pos-form-{{ $p->id }}" class="form-control form-control-sm">
                    <option value="">Semua</option>
                    @foreach($companies as $c)
                      <option value="{{ $c->id }}" {{ $p->company_id == $c->id ? 'selected' : '' }}>{{ $c->short_name ?? $c->name }}</option>
                    @endforeach
                  </select>
                </td>
                <td style="width:130px">
                  <input type="number" name="tunjangan_jabatan" form="pos-form-{{ $p->id }}" value="{{ $p->tunjangan_jabatan }}"
                         class="form-control form-control-sm" min="0" step="1000" placeholder="0">
                </td>
                <td style="width:110px">
                  <input type="number" name="tunjangan_harian" form="pos-form-{{ $p->id }}" value="{{ $p->tunjangan_harian }}"
                         class="form-control form-control-sm" min="0" step="1000" placeholder="0">
                </td>
                <td style="width:110px">
                  <input type="number" name="tarif_lembur" form="pos-form-{{ $p->id }}" value="{{ $p->tarif_lembur }}"
                         class="form-control form-control-sm" min="0" step="1000" placeholder="0">
                </td>
                <td class="text-center align-middle">{{ $p->employees_count }}</td>
                <td class="text-center align-middle" style="width:70px">
                  <div class="custom-control custom-switch">
                    <input type="hidden" name="is_active" form="pos-form-{{ $p->id }}" value="0">
                    <input type="checkbox" class="custom-control-input" id="pos-active-{{ $p->id }}" form="pos-form-{{ $p->id }}"
                           name="is_active" value="1" {{ $p->is_active ? 'checked' : '' }}>
                    <label class="custom-control-label" for="pos-active-{{ $p->id }}"></label>
                  </div>
                </td>
                <td style="width:70px;white-space:nowrap">
                  <button type="submit" form="pos-form-{{ $p->id }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                  <button type="submit" form="pos-delete-{{ $p->id }}" class="btn btn-xs btn-outline-danger"
                          onclick="return confirm('Hapus jabatan {{ $p->name }}?')"
                          {{ $p->employees_count > 0 ? 'disabled title=Masih dipakai karyawan' : '' }}>
                    <i class="gd-trash"></i>
                  </button>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
          </div>

          @foreach($positions as $p)
            <form id="pos-form-{{ $p->id }}" method="POST" action="{{ route('appraisal.positions.update', $p) }}" class="d-none">
              @csrf @method('PUT')
            </form>
            <form id="pos-delete-{{ $p->id }}" method="POST" action="{{ route('appraisal.positions.destroy', $p) }}" class="d-none">
              @csrf @method('DELETE')
            </form>
          @endforeach
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
