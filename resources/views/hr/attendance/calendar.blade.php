@extends('layouts.grain')
@section('title', 'Kalender Absensi')

@section('content')
@include('components.notification')

@php
  use App\Models\HR\AttendanceRecord;

  $monthNames = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  $cellBg = ['hadir'=>'#cef8f1','telat'=>'#fef7d8','izin'=>'#dadbfc','sakit'=>'#d4dffc','cuti'=>'#cdf7fd','alpha'=>'#fcdede','libur'=>'#f2f2f2'];
  $cellFg = ['hadir'=>'#067260','telat'=>'#836f1f','izin'=>'#25287c','sakit'=>'#14307d','cuti'=>'#047080','alpha'=>'#7c2f2f','libur'=>'#6b7280'];

  // Offset kolom pertama (Senin = 0)
  $offset = $start->dayOfWeekIso - 1;
  $prev   = $start->copy()->subMonth();
  $next   = $start->copy()->addMonth();
  $nav    = fn ($d) => route('hr.attendance.calendar', ['company_id' => $companyId, 'employee_id' => $employee?->id, 'month' => $d->month, 'year' => $d->year]);
@endphp

<nav class="d-none d-md-block" aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('hr.attendance.index', ['company_id' => $companyId]) }}">Rekap Absensi</a></li>
    <li class="breadcrumb-item active">Kalender</li>
  </ol>
</nav>

<div class="mb-3 d-flex justify-content-between align-items-start flex-wrap" style="gap:.5rem">
  <div>
    <div class="h3 mb-0">Kalender Absensi</div>
    <small class="text-muted">{{ $employee?->name ?? '—' }} · {{ $monthNames[$month] }} {{ $year }}</small>
  </div>
  <a href="{{ route('hr.attendance.index', ['company_id' => $companyId, 'month' => $month, 'year' => $year]) }}" class="btn btn-outline-primary btn-sm">Rekap semua karyawan</a>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end">
      <div class="form-group col-12 col-md-3 mb-2">
        <label class="small mb-1">Perusahaan</label>
        <select name="company_id" class="form-control form-control-sm" onchange="this.form.employee_id.value='';this.form.submit()">
          @foreach($companies as $c)<option value="{{ $c->id }}" @selected($c->id === $companyId)>{{ $c->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-group col-12 col-md-4 mb-2">
        <label class="small mb-1">Karyawan</label>
        <select name="employee_id" class="form-control form-control-sm" onchange="this.form.submit()">
          @foreach($employees as $e)<option value="{{ $e->id }}" @selected($e->id === $employee?->id)>{{ $e->name }}{{ $e->nip ? ' — ' . $e->nip : '' }}</option>@endforeach
        </select>
      </div>
      <div class="form-group col-6 col-md-3 mb-2">
        <label class="small mb-1">Bulan</label>
        <select name="month" class="form-control form-control-sm" onchange="this.form.submit()">
          @for($m = 1; $m <= 12; $m++)<option value="{{ $m }}" @selected($m === $month)>{{ $monthNames[$m] }}</option>@endfor
        </select>
      </div>
      <div class="form-group col-6 col-md-2 mb-2">
        <label class="small mb-1">Tahun</label>
        <select name="year" class="form-control form-control-sm" onchange="this.form.submit()">
          @for($y = now()->year + 1; $y >= now()->year - 4; $y--)<option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>@endfor
        </select>
      </div>
    </form>

    {{-- Filter status: klik untuk menyorot kotak bertanda status tsb --}}
    <div class="d-flex flex-wrap align-items-center mt-1" style="gap:.4rem" id="status-filter">
      <button type="button" class="btn btn-sm btn-primary" data-status="">Semua</button>
      @foreach(AttendanceRecord::$statusLabels as $key => $label)
        <button type="button" class="btn btn-sm btn-outline-primary" data-status="{{ $key }}">
          {{ $label }}@if(($summary[$key] ?? 0) > 0) <span class="badge badge-light">{{ $summary[$key] }}</span>@endif
        </button>
      @endforeach
      <button type="button" class="btn btn-sm btn-outline-primary" data-status="kosong">Belum diisi</button>
    </div>
  </div>
</div>

@if($employee)
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <a href="{{ $nav($prev) }}" class="btn btn-sm btn-outline-primary">&laquo; {{ $monthNames[$prev->month] }}</a>
    <strong>{{ $monthNames[$month] }} {{ $year }}</strong>
    <a href="{{ $nav($next) }}" class="btn btn-sm btn-outline-primary">{{ $monthNames[$next->month] }} &raquo;</a>
  </div>
  <div class="card-body">
    <div class="att-grid">
      @foreach(['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $d)<div class="att-dow">{{ $d }}</div>@endforeach
      @for($i = 0; $i < $offset; $i++)<div></div>@endfor

      @for($day = 1; $day <= $start->daysInMonth; $day++)
        @php
          $date   = $start->copy()->day($day);
          $key    = $date->toDateString();
          $rec    = $records->get($key);
          $leave  = $rec ? null : $leaves->first(fn ($l) => $l->start_date->lte($date) && $l->end_date->gte($date));
          $status = $rec?->status ?? ($leave ? 'cuti' : null);
          $weekend = $date->isWeekend();
          $bg = $status ? $cellBg[$status] : ($weekend ? '#f8f9fa' : '#fff');
          $fg = $status ? $cellFg[$status] : '#6b7280';
        @endphp
        <button type="button" class="att-cell" data-status="{{ $status ?? 'kosong' }}"
                data-date="{{ $key }}" data-label="{{ $date->translatedFormat('l, d F Y') }}"
                data-rec-status="{{ $rec?->status }}" data-in="{{ $rec?->check_in ? substr($rec->check_in, 0, 5) : '' }}"
                data-out="{{ $rec?->check_out ? substr($rec->check_out, 0, 5) : '' }}"
                data-late="{{ $rec?->late_minutes }}" data-ot="{{ $rec?->overtime_minutes }}" data-notes="{{ $rec?->notes }}"
                style="background:{{ $bg }};color:{{ $fg }}">
          <div class="att-day">{{ $day }}</div>
          @if($status)
            <div class="att-status">{{ AttendanceRecord::$statusLabels[$status] }}@if(! $rec && $leave) <small>({{ $leave->leaveType?->name }})</small>@endif</div>
            @if($rec?->check_in)<div class="att-time">{{ substr($rec->check_in, 0, 5) }}–{{ $rec->check_out ? substr($rec->check_out, 0, 5) : '…' }}</div>@endif
            @if($rec && $rec->late_minutes)<div class="att-time">telat {{ $rec->late_minutes }} mnt</div>@endif
          @elseif(! $weekend)
            <div class="att-time">+ isi</div>
          @endif
        </button>
      @endfor
    </div>
  </div>
</div>

{{-- Modal input absensi — di-push ke level body (@stack('modals')) supaya tidak terkurung
     stacking context .content dan tertutup backdrop. --}}
@push('modals')
<div class="modal fade" id="att-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form method="POST" action="{{ route('hr.attendance.store') }}" class="modal-content">
      @csrf
      <input type="hidden" name="from_calendar" value="1">
      <input type="hidden" name="company_id" value="{{ $companyId }}">
      <input type="hidden" name="employee_id" value="{{ $employee->id }}">
      <input type="hidden" name="date" id="att-date">
      <div class="modal-header">
        <h5 class="modal-title">{{ $employee->name }}<br><small class="text-muted" id="att-label"></small></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Status</label>
          <select name="status" id="att-status" class="form-control" required>
            @foreach(AttendanceRecord::$statusLabels as $k => $label)<option value="{{ $k }}">{{ $label }}</option>@endforeach
          </select>
        </div>
        <div class="form-row">
          <div class="form-group col-6"><label>Jam masuk</label><input type="time" name="check_in" id="att-in" class="form-control"></div>
          <div class="form-group col-6"><label>Jam pulang</label><input type="time" name="check_out" id="att-out" class="form-control"></div>
        </div>
        <div class="form-row">
          <div class="form-group col-6"><label>Terlambat (menit)</label><input type="number" min="0" name="late_minutes" id="att-late" class="form-control"></div>
          <div class="form-group col-6"><label>Lembur (menit)</label><input type="number" min="0" name="overtime_minutes" id="att-ot" class="form-control"></div>
        </div>
        <div class="form-group mb-0"><label>Catatan</label><input type="text" name="notes" id="att-notes" maxlength="500" class="form-control"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endpush
@else
  <div class="alert alert-info">Belum ada karyawan aktif di perusahaan ini.</div>
@endif

<style>
  .att-grid { display:grid; grid-template-columns:repeat(7, minmax(0, 1fr)); gap:6px; }
  .att-dow { text-align:center; font-size:.75rem; font-weight:600; color:#1e4fa3; text-transform:uppercase; padding-bottom:2px; }
  .att-cell { min-height:84px; border:1px solid #dbe4f3; border-radius:8px; padding:6px 8px; text-align:left; cursor:pointer; transition:box-shadow .12s, opacity .12s; }
  .att-cell:hover { box-shadow:0 0 0 2px #2563eb55; }
  .att-cell.dim { opacity:.25; }
  .att-day { font-weight:700; font-size:.95rem; }
  .att-status { font-size:.72rem; font-weight:600; line-height:1.2; }
  .att-time { font-size:.68rem; opacity:.85; }
  @media (max-width:575px) { .att-cell { min-height:62px; padding:4px; } .att-status, .att-time { font-size:.6rem; } }
</style>

@endsection

@section('scripts')
<script>
(function () {
  var cells = document.querySelectorAll('.att-cell');

  document.querySelectorAll('#status-filter button').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var s = btn.dataset.status;
      document.querySelectorAll('#status-filter button').forEach(function (b) {
        b.classList.toggle('btn-primary', b === btn);
        b.classList.toggle('btn-outline-primary', b !== btn);
      });
      cells.forEach(function (c) { c.classList.toggle('dim', s !== '' && c.dataset.status !== s); });
    });
  });

  cells.forEach(function (c) {
    c.addEventListener('click', function () {
      var d = c.dataset;
      document.getElementById('att-date').value = d.date;
      document.getElementById('att-label').textContent = d.label;
      document.getElementById('att-status').value = d.recStatus || 'hadir';
      document.getElementById('att-in').value = d.in || '';
      document.getElementById('att-out').value = d.out || '';
      document.getElementById('att-late').value = d.late || '';
      document.getElementById('att-ot').value = d.ot || '';
      document.getElementById('att-notes').value = d.notes || '';
      $('#att-modal').modal('show');
    });
  });
})();
</script>
@endsection
