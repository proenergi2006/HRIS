@extends('layouts.grain')
@section('title', 'Nilai Kompetensi — ' . $employee->name)

@section('content')
@include('components.notification')

@php $L = \App\Models\Competency\Competency::$levelLabels; @endphp

<div class="mb-3">
  <a href="{{ route('competency.assessments.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali ke daftar penilaian</a>
</div>

<div class="card mb-3">
  <div class="card-body">
    <div class="h3 mb-1">Penilaian Kompetensi</div>
    <div class="text-muted">
      <strong>{{ $employee->name }}</strong>
      @if($employee->position) &nbsp;·&nbsp; {{ $employee->position->name }} @endif
      @if($employee->department) &nbsp;·&nbsp; {{ $employee->department->name }} @endif
    </div>
    <div class="text-muted small mt-1">
      Kolom <strong>Wajib</strong> diambil dari profil kompetensi jabatan. Kosongkan level = hapus penilaian.
    </div>
  </div>
</div>

<form method="POST" action="{{ route('competency.assessments.upsert', $employee) }}">
  @csrf
  <div class="card">
    <div class="card-header font-weight-bold">Kompetensi</div>
    <div class="card-body">
      <div class="table-responsive mb-3">
        <table class="table table-sm table-bordered mb-0" id="comp-table">
          <thead class="thead-light">
            <tr><th>Kompetensi</th><th style="width:120px">Kategori</th><th style="width:80px" class="text-center">Wajib</th><th style="width:170px">Level Aktual</th><th style="width:70px" class="text-center">Gap</th><th>Catatan</th></tr>
          </thead>
          <tbody id="comp-body">
          @forelse($rows as $r)
            @php $cid = $r['competency']->id; @endphp
            <tr class="comp-row" data-required="{{ $r['required_level'] ?? 0 }}">
              <td class="align-middle">{{ $r['competency']->name }}</td>
              <td class="align-middle small">{{ $r['competency']->category ?? '-' }}</td>
              <td class="align-middle text-center">{{ $r['required_level'] ?? '—' }}</td>
              <td>
                <select name="levels[{{ $cid }}]" class="form-control form-control-sm level-input">
                  <option value="">—</option>
                  @foreach($L as $n => $lbl)<option value="{{ $n }}" @selected(($r['actual_level'] ?? null) == $n)>{{ $n }} — {{ $lbl }}</option>@endforeach
                </select>
              </td>
              <td class="align-middle text-center gap-cell">-</td>
              <td><input type="text" name="notes[{{ $cid }}]" class="form-control form-control-sm" maxlength="255"></td>
            </tr>
          @empty
            <tr id="empty-row"><td colspan="6" class="text-center text-muted py-3">Jabatan karyawan ini belum punya profil kompetensi. Tambahkan kompetensi lain di bawah.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>

      @if($others->isNotEmpty())
      <div class="form-row align-items-end">
        <div class="form-group col-md-5 mb-2">
          <label class="small font-weight-bold mb-1">Tambah kompetensi lain</label>
          <select id="add-comp-select" class="form-control form-control-sm">
            <option value="">-- Pilih kompetensi --</option>
            @foreach($others as $o)
              <option value="{{ $o->id }}" data-name="{{ $o->name }}" data-category="{{ $o->category }}">{{ $o->category ? '['.$o->category.'] ' : '' }}{{ $o->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group col-md-2 mb-2">
          <button type="button" class="btn btn-sm btn-outline-primary btn-block" id="add-comp-btn"><i class="gd-plus mr-1"></i> Tambah Baris</button>
        </div>
      </div>
      @endif

      <div class="d-flex justify-content-between mt-3">
        <a href="{{ route('competency.assessments.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary"><i class="gd-check mr-1"></i> Simpan Penilaian</button>
      </div>
    </div>
  </div>
</form>
@endsection

@section('scripts')
<script>
(function ($) {
  var LEVELS = @json($L);

  function recalcRow(tr) {
    var required = parseInt($(tr).data('required')) || 0;
    var actual = parseInt($(tr).find('.level-input').val()) || 0;
    var gap = required > 0 ? Math.max(0, required - actual) : 0;
    var $cell = $(tr).find('.gap-cell');
    if (required === 0) { $cell.text('-').css('color', ''); return; }
    $cell.text(gap).css('color', gap > 0 ? '#dc3545' : '#16a34a');
  }

  function recalcAll() { $('#comp-body .comp-row').each(function () { recalcRow(this); }); }

  $(document).on('input change', '.level-input', function () { recalcRow($(this).closest('tr')); });

  $('#add-comp-btn').on('click', function () {
    var $sel = $('#add-comp-select');
    var cid = $sel.val();
    if (!cid) return;
    var $opt = $sel.find('option:selected');
    var name = $opt.data('name'), category = $opt.data('category') || '-';
    $('#empty-row').remove();

    var opts = '<option value="">—</option>';
    $.each(LEVELS, function (n, lbl) { opts += '<option value="' + n + '">' + n + ' — ' + lbl + '</option>'; });

    var row = '<tr class="comp-row" data-required="0">' +
      '<td class="align-middle">' + name + '</td>' +
      '<td class="align-middle small">' + category + '</td>' +
      '<td class="align-middle text-center">—</td>' +
      '<td><select name="levels[' + cid + ']" class="form-control form-control-sm level-input">' + opts + '</select></td>' +
      '<td class="align-middle text-center gap-cell">-</td>' +
      '<td><input type="text" name="notes[' + cid + ']" class="form-control form-control-sm" maxlength="255"></td>' +
      '</tr>';
    $('#comp-body').append(row);
    $opt.remove();
    $sel.val('');
  });

  recalcAll();
})(jQuery);
</script>
@endsection
