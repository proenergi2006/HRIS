@extends('layouts.grain')
@section('title', 'Shift & Roster')

@section('content')
@include('components.notification')

@php
  $monthNames = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
@endphp

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Shift &amp; Roster</div>
  <a href="{{ route('hr.roster.shifts') }}" class="btn btn-outline-secondary btn-sm"><i class="gd-settings mr-1"></i> Master Shift</a>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Perusahaan</label>
        <select name="company_id" class="form-control form-control-sm" onchange="this.form.submit()">
          @foreach($companies as $c)<option value="{{ $c->id }}" @selected($companyId == $c->id)>{{ $c->short_name ?? $c->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-group col-md-2 mb-0">
        <label class="small font-weight-bold">Bulan</label>
        <select name="month" class="form-control form-control-sm" onchange="this.form.submit()">
          @foreach($monthNames as $k => $v)<option value="{{ $k }}" @selected($month == $k)>{{ $v }}</option>@endforeach
        </select>
      </div>
      <div class="form-group col-md-2 mb-0">
        <label class="small font-weight-bold">Tahun</label>
        <input type="number" name="year" class="form-control form-control-sm" value="{{ $year }}" onchange="this.form.submit()">
      </div>
    </form>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <div class="small font-weight-bold mb-2">Terapkan Roster Massal</div>
    <form method="POST" action="{{ route('hr.roster.bulk') }}" class="form-row align-items-end">
      @csrf
      <input type="hidden" name="company_id" value="{{ $companyId }}">
      <div class="form-group col-md-4 mb-2">
        <label class="small">Karyawan</label>
        <select name="employee_ids[]" class="form-control form-control-sm" multiple size="4" required>
          @foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-group col-md-2 mb-2">
        <label class="small">Dari</label>
        <input type="date" name="date_from" class="form-control form-control-sm" required>
      </div>
      <div class="form-group col-md-2 mb-2">
        <label class="small">Sampai</label>
        <input type="date" name="date_to" class="form-control form-control-sm" required>
      </div>
      <div class="form-group col-md-2 mb-2">
        <label class="small">Shift</label>
        <select name="shift_id" class="form-control form-control-sm">
          <option value="">Libur</option>
          @foreach($shifts as $s)<option value="{{ $s->id }}">{{ $s->code }}</option>@endforeach
        </select>
      </div>
      <div class="form-group col-md-2 mb-2">
        <div class="custom-control custom-checkbox mt-3">
          <input type="checkbox" class="custom-control-input" id="skip_weekend" name="skip_weekend" value="1" checked>
          <label class="custom-control-label small" for="skip_weekend">Lewati Sabtu/Minggu</label>
        </div>
      </div>
      <div class="form-group col-md-2 mb-2">
        <button class="btn btn-primary btn-sm btn-block">Terapkan</button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-2">
    <div style="overflow-x:auto">
      <table style="border-collapse:collapse;font-size:.72rem;min-width:900px">
        <thead>
          <tr>
            <th style="position:sticky;left:0;background:#f8f9fa;padding:4px 8px;text-align:left;z-index:2">Karyawan</th>
            @for($d = 1; $d <= $daysInMonth; $d++)
              @php $dow = \Carbon\Carbon::create($year, $month, $d)->dayOfWeekIso; @endphp
              <th style="padding:3px 2px;text-align:center;min-width:44px;{{ $dow >= 6 ? 'color:#dc3545' : '' }}">{{ $d }}</th>
            @endfor
          </tr>
        </thead>
        <tbody>
          @foreach($employees as $emp)
            @php $empEntries = ($entries[$emp->id] ?? collect())->keyBy(fn($r) => $r->work_date->format('Y-m-d')); @endphp
            <tr>
              <td style="position:sticky;left:0;background:#fff;padding:4px 8px;white-space:nowrap;z-index:1">{{ $emp->name }}</td>
              @for($d = 1; $d <= $daysInMonth; $d++)
                @php
                  $key = sprintf('%04d-%02d-%02d', $year, $month, $d);
                  $entry = $empEntries[$key] ?? null;
                @endphp
                <td style="padding:1px;text-align:center">
                  <form method="POST" action="{{ route('hr.roster.cell') }}" style="margin:0">
                    @csrf
                    <input type="hidden" name="company_id" value="{{ $companyId }}">
                    <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                    <input type="hidden" name="work_date" value="{{ $key }}">
                    <select name="shift_id" onchange="this.form.submit()" style="font-size:.68rem;border:1px solid #dee2e6;border-radius:3px;padding:1px 2px;max-width:52px">
                      <option value="" @selected($entry && ! $entry->shift_id)>{{ $entry ? '–' : '' }}</option>
                      @foreach($shifts as $s)
                        <option value="{{ $s->id }}" @selected($entry && $entry->shift_id == $s->id)>{{ $s->code }}</option>
                      @endforeach
                    </select>
                  </form>
                </td>
              @endfor
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <p class="small text-muted mt-2 mb-0">Kosong = belum ada roster (import absensi pakai shift PAGI sebagai fallback). “–” = libur.</p>
  </div>
</div>
@endsection
