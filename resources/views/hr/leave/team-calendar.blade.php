@extends('layouts.grain')
@section('title', 'Kalender Cuti Tim')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Kalender Cuti {{ $isHr ? '' : 'Tim' }}</div>
  <div>
    @php
      $prev = \Carbon\Carbon::create($year, $month, 1)->subMonth();
      $next = \Carbon\Carbon::create($year, $month, 1)->addMonth();
    @endphp
    <a href="{{ route('hr.leave.team-calendar', array_merge(request()->except(['month','year']), ['month' => $prev->month, 'year' => $prev->year])) }}" class="btn btn-sm btn-outline-secondary">&larr;</a>
    <span class="font-weight-bold mx-2">{{ $start->translatedFormat('F Y') }}</span>
    <a href="{{ route('hr.leave.team-calendar', array_merge(request()->except(['month','year']), ['month' => $next->month, 'year' => $next->year])) }}" class="btn btn-sm btn-outline-secondary">&rarr;</a>
  </div>
</div>

@if($isHr)
<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <input type="hidden" name="month" value="{{ $month }}">
      <input type="hidden" name="year" value="{{ $year }}">
      <div class="form-group col-md-4 mb-0">
        <label class="small font-weight-bold">PT</label>
        <select name="company_id" class="form-control form-control-sm" onchange="this.form.submit()">
          <option value="">— Semua —</option>
          @foreach($companies as $c)
            <option value="{{ $c->id }}" @selected(request('company_id') == $c->id)>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
    </form>
  </div>
</div>
@endif

<div class="card mb-4">
  <div class="card-body p-2 p-md-3">
    <div class="table-responsive">
      <table class="table table-bordered table-sm mb-0" style="table-layout:fixed">
        <thead class="thead-light">
          <tr>
            @foreach(['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $d)
              <th class="text-center small">{{ $d }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach(array_chunk($days, 7) as $week)
            <tr>
              @foreach($week as $day)
                @php
                  $key = $day->format('Y-m-d');
                  $items = $byDate->get($key, collect());
                  $inMonth = $day->month === (int) $month;
                @endphp
                <td class="align-top p-1" style="height:90px;{{ $inMonth ? '' : 'background:#f8f9fa' }}">
                  <div class="small {{ $inMonth ? 'font-weight-bold' : 'text-muted' }}">{{ $day->day }}</div>
                  @foreach($items->take(3) as $l)
                    <div class="small text-truncate" style="background:#fef3c7;border-radius:3px;padding:1px 4px;margin-top:2px" title="{{ $l->employee?->name }} — {{ $l->leaveType?->name }}">
                      {{ $l->employee?->name }}
                    </div>
                  @endforeach
                  @if($items->count() > 3)
                    <div class="small text-muted">+{{ $items->count() - 3 }} lagi</div>
                  @endif
                </td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header font-weight-bold">Daftar Cuti Bulan Ini ({{ $leaves->count() }})</div>
  <div class="card-body p-0">
    @if($leaves->isEmpty())
      <p class="text-muted small p-3 mb-0">Tidak ada cuti disetujui bulan ini.</p>
    @else
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light"><tr><th class="pl-3">Nama</th><th>Jenis Cuti</th><th>Mulai</th><th>Selesai</th><th class="text-right">Jumlah Hari</th></tr></thead>
        <tbody>
        @foreach($leaves as $l)
          <tr>
            <td class="pl-3">{{ $l->employee?->name }}</td>
            <td class="small">{{ $l->leaveType?->name ?? '-' }}</td>
            <td class="small">{{ $l->start_date->format('d/m/Y') }}</td>
            <td class="small">{{ $l->end_date->format('d/m/Y') }}</td>
            <td class="text-right">{{ $l->total_days }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
</div>
@endsection
