@extends('layouts.grain')
@section('title', 'Input Absensi Harian')

@section('content')
@include('components.notification')

<nav class="d-none d-md-block" aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('hr.attendance.index', ['company_id' => $companyId]) }}">Rekap Absensi</a></li>
    <li class="breadcrumb-item active">Input</li>
  </ol>
</nav>

<div class="mb-3 d-flex justify-content-between align-items-center">
  <div>
    <div class="h3 mb-0">Input Absensi Harian</div>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body">
    <form method="GET" class="form-row align-items-end mb-0" id="filterForm">
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Perusahaan</label>
        <select name="company_id" class="form-control form-control-sm" onchange="this.form.submit()">
          @foreach($companies as $c)
            <option value="{{ $c->id }}" {{ $companyId == $c->id ? 'selected' : '' }}>{{ $c->short_name ?? $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-auto mb-0">
        <label class="small font-weight-bold">Tanggal</label>
        <input type="date" name="date" class="form-control form-control-sm"
               value="{{ request('date', now()->format('Y-m-d')) }}"
               onchange="this.form.submit()">
      </div>
    </form>
  </div>
</div>

@if($employees->isEmpty())
  <div class="alert alert-warning">Belum ada karyawan aktif untuk perusahaan ini.</div>
@else
<form method="POST" action="{{ route('hr.attendance.bulk') }}">
  @csrf
  <input type="hidden" name="company_id" value="{{ $companyId }}">
  <input type="hidden" name="date" value="{{ request('date', now()->format('Y-m-d')) }}">

  <div class="card">
    <div class="card-header font-weight-bold">
      Absensi — {{ request('date') ? \Carbon\Carbon::parse(request('date'))->translatedFormat('l, d F Y') : now()->translatedFormat('l, d F Y') }}
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light">
            <tr>
              <th style="width:200px">Nama</th>
              <th style="width:160px">Status</th>
              <th style="width:100px">Jam Masuk</th>
              <th style="width:100px">Jam Keluar</th>
              <th style="width:80px">Terlambat</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody>
            @foreach($employees as $i => $emp)
            <input type="hidden" name="attendance[{{ $i }}][employee_id]" value="{{ $emp->id }}">
            <tr>
              <td class="align-middle">
                <div class="font-weight-bold" style="font-size:.88rem">{{ $emp->name }}</div>
                <small class="text-muted">{{ $emp->nip }}</small>
              </td>
              <td>
                <select name="attendance[{{ $i }}][status]" class="form-control form-control-sm status-sel">
                  @foreach(\App\Models\HR\AttendanceRecord::$statusLabels as $val => $lbl)
                    <option value="{{ $val }}" {{ $val === 'hadir' ? 'selected' : '' }}>{{ $lbl }}</option>
                  @endforeach
                </select>
              </td>
              <td>
                <input type="time" name="attendance[{{ $i }}][check_in]"
                       class="form-control form-control-sm check-in" value="08:00">
              </td>
              <td>
                <input type="time" name="attendance[{{ $i }}][check_out]"
                       class="form-control form-control-sm check-out" value="17:00">
              </td>
              <td>
                <input type="number" name="attendance[{{ $i }}][late_minutes]"
                       class="form-control form-control-sm" value="0" min="0" placeholder="mnt">
              </td>
              <td>
                <input type="text" name="attendance[{{ $i }}][notes]"
                       class="form-control form-control-sm" placeholder="Opsional">
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
      <small class="text-muted">{{ $employees->count() }} karyawan</small>
      <button type="submit" class="btn btn-primary">Simpan Absensi</button>
    </div>
  </div>
</form>

<script>
document.querySelectorAll('.status-sel').forEach(function(sel) {
  sel.addEventListener('change', function() {
    var row   = this.closest('tr');
    var ci    = row.querySelector('.check-in');
    var co    = row.querySelector('.check-out');
    var hide  = ['alpha','izin','sakit','cuti','libur'].includes(this.value);
    ci.disabled = co.disabled = hide;
    if (hide) { ci.value = ''; co.value = ''; }
    else { ci.value = '08:00'; co.value = '17:00'; }
  });
});
</script>
@endif
@endsection
