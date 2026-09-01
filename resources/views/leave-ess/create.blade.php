@extends('layouts.grain')
@section('title', 'Ajukan Cuti / Izin')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('leave.mine.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>
<div class="h3 mb-4">Ajukan Cuti / Izin</div>

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('leave.mine.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="form-row">
        <div class="form-group col-md-4">
          <label>Jenis Cuti/Izin <span class="text-danger">*</span></label>
          <select name="leave_type_id" id="leave_type_id" class="form-control" required>
            <option value="">— pilih —</option>
            @foreach($leaveTypes as $t)
              <option value="{{ $t->id }}" data-remaining="{{ $balances[$t->id]->getRemaining() }}" @selected(old('leave_type_id') == $t->id)>{{ $t->name }}</option>
            @endforeach
          </select>
          <small class="form-text text-muted" id="balance-hint"></small>
        </div>
        <div class="form-group col-md-4">
          <label>Tanggal Mulai <span class="text-danger">*</span></label>
          <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
        </div>
        <div class="form-group col-md-4">
          <label>Tanggal Selesai <span class="text-danger">*</span></label>
          <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
        </div>
      </div>
      <div class="form-group">
        <label>Alasan</label>
        <textarea name="reason" rows="3" class="form-control" placeholder="Opsional, tapi disarankan diisi untuk mempercepat persetujuan">{{ old('reason') }}</textarea>
      </div>
      <div class="form-group">
        <label>Lampiran (opsional — mis. surat sakit/undangan)</label>
        <input type="file" name="attachment" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
        <small class="form-text text-muted">PDF/JPG/PNG, maks 5MB.</small>
      </div>
      <div class="alert alert-secondary small">
        Jumlah hari dihitung otomatis (hari kerja, Sabtu/Minggu tidak dihitung). Pengajuan akan
        melalui alur persetujuan atasan sesuai konfigurasi perusahaan.
      </div>
      <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
    </form>
  </div>
</div>

<script>
(function () {
  var sel = document.getElementById('leave_type_id');
  var hint = document.getElementById('balance-hint');
  function update() {
    var opt = sel.options[sel.selectedIndex];
    var rem = opt ? opt.getAttribute('data-remaining') : null;
    hint.textContent = rem !== null && rem !== '' ? 'Sisa saldo: ' + rem + ' hari' : '';
  }
  sel.addEventListener('change', update);
  update();
})();
</script>
@endsection
