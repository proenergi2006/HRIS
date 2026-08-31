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

<div class="card">
  <div class="card-header font-weight-bold">Daftar Jabatan</div>
  <div class="card-body">

    <p class="small text-muted">
      <strong>Tunjangan Jabatan</strong>, <strong>Tarif Harian</strong> (dasar Tunjangan Makan &amp; Transport,
      dikalikan jumlah hari hadir), dan <strong>Tarif Lembur</strong> (Rp/jam) di sini otomatis dipakai saat
      Generate Slip. <strong>Atasan</strong> = jabatan yang dilapori (dipakai untuk bagan &amp; alur persetujuan).
    </p>

    <form method="POST" action="{{ route('appraisal.positions.store') }}" class="mb-4">
      @csrf
      <div class="form-row">
        <div class="form-group col-md-1 mb-2">
          <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" placeholder="Kode" required maxlength="20">
        </div>
        <div class="form-group col-md-3 mb-2">
          <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Nama jabatan" required>
        </div>
        <div class="form-group col-md-2 mb-2">
          <select name="company_id" class="form-control">
            <option value="">Semua PT</option>
            @foreach($companies as $c)<option value="{{ $c->id }}">{{ $c->short_name ?? $c->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-md-2 mb-2">
          <select name="department_id" class="form-control pos-department">
            <option value="">-- Departemen --</option>
            @foreach($departments as $dep)<option value="{{ $dep->id }}">{{ $dep->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-md-2 mb-2">
          <select name="section_id" class="form-control pos-section">
            <option value="">-- Section --</option>
            @foreach($sections as $sec)<option value="{{ $sec->id }}" data-department="{{ $sec->department_id }}">{{ $sec->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-md-2 mb-2">
          <select name="level_id" class="form-control">
            <option value="">-- Level --</option>
            @foreach($levels as $lv)<option value="{{ $lv->id }}">{{ $lv->name }}</option>@endforeach
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group col-md-3 mb-2">
          <select name="reports_to_position_id" class="form-control">
            <option value="">-- Melapor ke (atasan) --</option>
            @foreach($allPositions as $ap)<option value="{{ $ap->id }}">{{ $ap->name }}</option>@endforeach
          </select>
        </div>
        <div class="form-group col-md-3 mb-2">
          <input type="number" data-rupiah name="tunjangan_jabatan" class="form-control" placeholder="Tunjangan Jabatan (Rp)" min="0" step="1000">
        </div>
        <div class="form-group col-md-3 mb-2">
          <input type="number" data-rupiah name="tunjangan_harian" class="form-control" placeholder="Tarif Makan & Transport /hari (Rp)" min="0" step="1000">
        </div>
        <div class="form-group col-md-3 mb-2">
          <input type="number" data-rupiah name="tarif_lembur" class="form-control" placeholder="Tarif Lembur /jam (Rp)" min="0" step="1000">
        </div>
        <div class="form-group col-12 mb-2">
          <textarea name="job_description" class="form-control" rows="2" placeholder="Uraian jabatan (opsional)"></textarea>
        </div>
        <div class="form-group col-12 mb-0">
          <button type="submit" class="btn btn-primary">Tambah Jabatan</button>
        </div>
      </div>
    </form>

    @if($positions->isEmpty())
      <p class="text-muted small">Belum ada jabatan. Tambahkan di atas.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0" style="min-width:1300px">
          <thead class="thead-light">
            <tr>
              <th>Kode</th><th>Nama</th><th>Perusahaan</th><th>Departemen</th><th>Section</th><th>Level</th><th>Atasan</th>
              <th>Tunj. Jabatan</th><th>Tarif/Hari</th><th>Tarif Lembur</th>
              <th class="text-center">Kry</th><th class="text-center">Aktif</th><th></th>
            </tr>
          </thead>
          <tbody>
          @foreach($positions as $p)
            @php $f = 'pos-form-' . $p->id; @endphp
            <tr>
              <td style="width:90px"><input type="text" name="code" form="{{ $f }}" value="{{ $p->code }}" class="form-control form-control-sm"></td>
              <td style="min-width:150px"><input type="text" name="name" form="{{ $f }}" value="{{ $p->name }}" class="form-control form-control-sm"></td>
              <td style="width:120px">
                <select name="company_id" form="{{ $f }}" class="form-control form-control-sm">
                  <option value="">Semua</option>
                  @foreach($companies as $c)<option value="{{ $c->id }}" @selected($p->company_id == $c->id)>{{ $c->short_name ?? $c->name }}</option>@endforeach
                </select>
              </td>
              <td style="width:140px">
                <select name="department_id" form="{{ $f }}" class="form-control form-control-sm pos-department">
                  <option value="">—</option>
                  @foreach($departments as $dep)<option value="{{ $dep->id }}" @selected($p->department_id == $dep->id)>{{ $dep->name }}</option>@endforeach
                </select>
              </td>
              <td style="width:140px">
                <select name="section_id" form="{{ $f }}" class="form-control form-control-sm pos-section">
                  <option value="">—</option>
                  @foreach($sections as $sec)<option value="{{ $sec->id }}" data-department="{{ $sec->department_id }}" @selected($p->section_id == $sec->id)>{{ $sec->name }}</option>@endforeach
                </select>
              </td>
              <td style="width:120px">
                <select name="level_id" form="{{ $f }}" class="form-control form-control-sm">
                  <option value="">—</option>
                  @foreach($levels as $lv)<option value="{{ $lv->id }}" @selected($p->level_id == $lv->id)>{{ $lv->name }}</option>@endforeach
                </select>
              </td>
              <td style="width:150px">
                <select name="reports_to_position_id" form="{{ $f }}" class="form-control form-control-sm">
                  <option value="">—</option>
                  @foreach($allPositions as $ap)
                    @if($ap->id !== $p->id)
                      <option value="{{ $ap->id }}" @selected($p->reports_to_position_id == $ap->id)>{{ $ap->name }}</option>
                    @endif
                  @endforeach
                </select>
              </td>
              <td style="width:120px"><input type="number" data-rupiah name="tunjangan_jabatan" form="{{ $f }}" value="{{ $p->tunjangan_jabatan }}" class="form-control form-control-sm" min="0" step="1000" placeholder="0"></td>
              <td style="width:110px"><input type="number" data-rupiah name="tunjangan_harian" form="{{ $f }}" value="{{ $p->tunjangan_harian }}" class="form-control form-control-sm" min="0" step="1000" placeholder="0"></td>
              <td style="width:110px"><input type="number" data-rupiah name="tarif_lembur" form="{{ $f }}" value="{{ $p->tarif_lembur }}" class="form-control form-control-sm" min="0" step="1000" placeholder="0"></td>
              <td class="text-center align-middle">{{ $p->employees_count }}</td>
              <td class="text-center align-middle" style="width:60px">
                <input type="hidden" name="is_active" form="{{ $f }}" value="0">
                <div class="custom-control custom-switch d-inline-block">
                  <input type="checkbox" class="custom-control-input" id="pos-active-{{ $p->id }}" form="{{ $f }}" name="is_active" value="1" {{ $p->is_active ? 'checked' : '' }}>
                  <label class="custom-control-label" for="pos-active-{{ $p->id }}"></label>
                </div>
              </td>
              <td style="width:80px;white-space:nowrap">
                <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></button>
                <a href="#" class="btn btn-xs btn-outline-danger"
                   data-confirm="Hapus jabatan {{ $p->name }}?" data-confirm-title="Hapus Jabatan"
                   data-form="pos-delete-{{ $p->id }}"
                   @if($p->employees_count > 0) style="pointer-events:none;opacity:.4" title="Masih dipakai karyawan" @endif>
                  <i class="gd-trash"></i>
                </a>
              </td>
            </tr>
            <tr>
              <td></td>
              <td colspan="12" class="pb-3">
                <input type="text" name="job_description" form="{{ $f }}" value="{{ $p->job_description }}" class="form-control form-control-sm" placeholder="Uraian jabatan (opsional)">
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>

      @foreach($positions as $p)
        <form id="pos-form-{{ $p->id }}" method="POST" action="{{ route('appraisal.positions.update', $p) }}" class="d-none">@csrf @method('PUT')</form>
        <form id="pos-delete-{{ $p->id }}" method="POST" action="{{ route('appraisal.positions.destroy', $p) }}" class="d-none">@csrf @method('DELETE')</form>
      @endforeach
    @endif
  </div>
</div>

<script>
// Section cuma boleh dari Departemen yang sedang dipilih — sembunyikan opsi
// yang tidak nyambung (baik di form tambah maupun tiap baris edit).
(function () {
  function filterSections(deptSelect) {
    var scope = deptSelect.closest('.form-row, tr');
    var secSelect = scope && scope.querySelector('.pos-section');
    if (!secSelect) return;
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

  document.querySelectorAll('.pos-department').forEach(function (sel) {
    filterSections(sel);
    sel.addEventListener('change', function () { filterSections(sel); });
  });
})();
</script>
@endsection
