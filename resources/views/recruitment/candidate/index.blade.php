@extends('layouts.grain')
@section('title', 'Kandidat')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Kandidat</div>
  @can('recruitment.create')
  <a href="{{ route('recruitment.candidates.create') }}" class="btn btn-primary btn-sm">
    <i class="gd-plus mr-1"></i> Tambah Kandidat
  </a>
  @endcan
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-4 mb-0">
        <label class="small font-weight-bold">Requisition</label>
        <select name="job_requisition_id" class="form-control form-control-sm" onchange="this.form.submit()">
          <option value="">-- Semua --</option>
          @foreach($requisitions as $r)
            <option value="{{ $r->id }}" @selected(request('job_requisition_id') == $r->id)>{{ $r->title }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Status</label>
        <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
          <option value="">-- Semua --</option>
          @foreach(\App\Models\Candidate::$statusLabels as $key => $label)
            <option value="{{ $key }}" @selected(request('status') == $key)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body">
    @if($candidates->isEmpty())
      <p class="text-muted small mb-0">Belum ada kandidat.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light"><tr><th>Nama</th><th>Requisition</th><th>Sumber</th><th class="text-right">Ekspektasi</th><th class="text-center">Assessment</th><th>Tahap</th><th>Status</th><th></th></tr></thead>
          <tbody>
          @foreach($candidates as $c)
            <tr>
              <td>{{ $c->name }}<div class="small text-muted">{{ $c->email }} {{ $c->phone ? '· '.$c->phone : '' }}</div></td>
              <td>{{ $c->jobRequisition?->title ?? '—' }}</td>
              <td>{{ $c->source ?? '—' }}</td>
              <td class="text-right">{{ $c->expected_salary ? number_format($c->expected_salary, 0, ',', '.') : '—' }}</td>
              <td class="text-center">
                @if($c->assessment_result)
                  <span class="badge badge-{{ $c->assessment_result === 'pass' ? 'success' : ($c->assessment_result === 'fail' ? 'danger' : 'warning') }}">
                    {{ \App\Models\Candidate::$assessmentLabels[$c->assessment_result] ?? $c->assessment_result }}</span>
                @else <span class="text-muted">—</span> @endif
              </td>
              <td>@php $st = $c->stage(); @endphp<span class="badge badge-{{ $st['badge'] }}">{{ $st['label'] }}</span></td>
              <td><span class="badge badge-{{ \App\Models\Candidate::$statusBadges[$c->status] ?? 'secondary' }}">{{ \App\Models\Candidate::$statusLabels[$c->status] ?? $c->status }}</span></td>
              <td><a href="{{ route('recruitment.candidates.show', $c) }}" class="btn btn-xs btn-outline-secondary">Detail</a></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
