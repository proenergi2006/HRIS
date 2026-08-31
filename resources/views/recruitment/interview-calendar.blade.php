@extends('layouts.grain')
@section('title', 'Kalender Interview')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Kalender Interview</div>
  <div>
    @php
      $prev = \Carbon\Carbon::create($year, $month, 1)->subMonth();
      $next = \Carbon\Carbon::create($year, $month, 1)->addMonth();
    @endphp
    <a href="{{ route('recruitment.interview-calendar', array_merge(request()->except(['month','year']), ['month' => $prev->month, 'year' => $prev->year])) }}" class="btn btn-sm btn-outline-secondary">&larr;</a>
    <span class="font-weight-bold mx-2">{{ $start->translatedFormat('F Y') }}</span>
    <a href="{{ route('recruitment.interview-calendar', array_merge(request()->except(['month','year']), ['month' => $next->month, 'year' => $next->year])) }}" class="btn btn-sm btn-outline-secondary">&rarr;</a>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <input type="hidden" name="month" value="{{ $month }}">
      <input type="hidden" name="year" value="{{ $year }}">
      <div class="form-group col-md-4 mb-0">
        <label class="small font-weight-bold">Pewawancara</label>
        <select name="interviewer_employee_id" class="form-control form-control-sm" onchange="this.form.submit()">
          <option value="">-- Semua --</option>
          @foreach($interviewers as $i)
            <option value="{{ $i->id }}" @selected(request('interviewer_employee_id') == $i->id)>{{ $i->name }}</option>
          @endforeach
        </select>
      </div>
    </form>
  </div>
</div>

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
                  @foreach($items->take(3) as $iv)
                    <div class="small text-truncate" style="background:#e7f1ff;border-radius:3px;padding:1px 4px;margin-top:2px" title="{{ $iv->candidate->name }} — {{ $iv->stage }}">
                      {{ $iv->scheduled_at->format('H:i') }} {{ $iv->candidate->name }}
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
  <div class="card-header font-weight-bold">Daftar Interview Bulan Ini ({{ $interviews->count() }})</div>
  <div class="card-body p-0">
    @if($interviews->isEmpty())
      <p class="text-muted small p-3 mb-0">Tidak ada interview terjadwal.</p>
    @else
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light"><tr><th class="pl-3">Tanggal</th><th>Kandidat</th><th>Requisition</th><th>Tahap</th><th>Pewawancara</th><th>Hasil</th><th></th></tr></thead>
        <tbody>
        @foreach($interviews as $iv)
          <tr>
            <td class="pl-3 small">{{ $iv->scheduled_at->format('d/m/Y H:i') }}</td>
            <td>{{ $iv->candidate->name }}</td>
            <td class="small text-muted">{{ $iv->candidate->jobRequisition?->title ?? 'Walk-in' }}</td>
            <td class="small">{{ $iv->stage }}</td>
            <td class="small">{{ $iv->interviewer?->name ?? '—' }}</td>
            <td><span class="badge badge-{{ $iv->result === 'pass' ? 'success' : ($iv->result === 'fail' ? 'danger' : 'secondary') }}">{{ \App\Models\CandidateInterview::$resultLabels[$iv->result] ?? $iv->result }}</span></td>
            <td class="pr-3 text-right"><a href="{{ route('recruitment.candidates.show', $iv->candidate) }}" class="btn btn-xs btn-outline-secondary">Detail</a></td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
</div>
@endsection
