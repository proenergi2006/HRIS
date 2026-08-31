@extends('layouts.grain')
@section('title', 'Job Requisition')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div class="h3 mb-0">Job Requisition</div>
  @can('recruitment.create')
  <a href="{{ route('recruitment.requisitions.create') }}" class="btn btn-primary btn-sm">
    <i class="gd-plus mr-1"></i> Requisition Baru
  </a>
  @endcan
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Perusahaan</label>
        <select name="company_id" class="form-control form-control-sm" onchange="this.form.submit()">
          @foreach($companies as $c)
            <option value="{{ $c->id }}" @selected($companyId == $c->id)>{{ $c->short_name ?? $c->name }}</option>
          @endforeach
        </select>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body">
    @if($requisitions->isEmpty())
      <p class="text-muted small mb-0">Belum ada job requisition untuk perusahaan ini.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light">
            <tr><th>Judul</th><th>Tipe</th><th>Unit</th><th>MPP</th><th class="text-center">Headcount</th><th class="text-center">Kandidat</th><th>Status</th><th>Diajukan Oleh</th><th></th></tr>
          </thead>
          <tbody>
          @foreach($requisitions as $r)
            <tr>
              <td>{{ $r->title }}</td>
              <td class="small">
                @php $tt = ['replacement' => 'Pengganti', 'additional' => 'Tambahan', 'new_position' => 'Posisi Baru']; @endphp
                <span class="badge badge-{{ $r->request_type === 'replacement' ? 'light' : 'info' }}">{{ $tt[$r->request_type] ?? $r->request_type }}</span>
              </td>
              <td>{{ $r->scopeLabel() }}</td>
              <td class="small text-muted">{{ $r->manpowerPlan ? $r->manpowerPlan->periodLabel() : '—' }}</td>
              <td class="text-center">{{ $r->headcount_requested }}</td>
              <td class="text-center">
                <a href="{{ route('recruitment.candidates.index', ['job_requisition_id' => $r->id]) }}">{{ $r->candidates_count }}</a>
              </td>
              <td><span class="badge badge-{{ \App\Models\JobRequisition::$statusBadges[$r->status] ?? 'secondary' }}">{{ \App\Models\JobRequisition::$statusLabels[$r->status] ?? $r->status }}</span></td>
              <td class="small text-muted">{{ $r->requestedBy?->name ?? '—' }}</td>
              <td><a href="{{ route('recruitment.requisitions.show', $r) }}" class="btn btn-xs btn-outline-secondary">Detail</a></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
