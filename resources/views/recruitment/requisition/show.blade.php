@extends('layouts.grain')
@section('title', 'Detail Requisition')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('recruitment.requisitions.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="row">
  <div class="col-lg-7">
    <div class="card mb-4">
      <div class="card-header font-weight-bold d-flex justify-content-between">
        <span>{{ $requisition->title }}</span>
        <span class="badge badge-{{ \App\Models\JobRequisition::$statusBadges[$requisition->status] ?? 'secondary' }}">
          {{ \App\Models\JobRequisition::$statusLabels[$requisition->status] ?? $requisition->status }}
        </span>
      </div>
      <div class="card-body">
        <dl class="row mb-3">
          <dt class="col-sm-4 text-muted">Perusahaan</dt><dd class="col-sm-8">{{ $requisition->company?->short_name ?? $requisition->company?->name }}</dd>
          <dt class="col-sm-4 text-muted">Unit</dt><dd class="col-sm-8">{{ $requisition->scopeLabel() }}</dd>
          <dt class="col-sm-4 text-muted">Headcount Diminta</dt><dd class="col-sm-8">{{ $requisition->headcount_requested }}</dd>
          <dt class="col-sm-4 text-muted">Tipe Karyawan</dt><dd class="col-sm-8">{{ $requisition->employmentType?->name ?? '—' }}</dd>
          <dt class="col-sm-4 text-muted">Target Bergabung</dt><dd class="col-sm-8">{{ optional($requisition->target_join_date)->format('d/m/Y') ?? '—' }}</dd>
          <dt class="col-sm-4 text-muted">Diajukan Oleh</dt><dd class="col-sm-8">{{ $requisition->requestedBy?->name ?? '—' }}</dd>
          @if($requisition->reason)
            <dt class="col-sm-4 text-muted">Alasan</dt><dd class="col-sm-8">{{ $requisition->reason }}</dd>
          @endif
          @if($requisition->status === 'rejected' && $requisition->notes_rejection)
            <dt class="col-sm-4 text-muted">Alasan Ditolak</dt><dd class="col-sm-8 text-danger">{{ $requisition->notes_rejection }}</dd>
          @endif
        </dl>

        @if($requisition->isDraft())
          <hr>
          <form method="POST" action="{{ route('recruitment.requisitions.submit', $requisition) }}">@csrf
            <button class="btn btn-success">Ajukan untuk Persetujuan</button>
            <a href="{{ route('recruitment.requisitions.edit', $requisition) }}" class="btn btn-outline-secondary">Edit</a>
          </form>
        @endif
        @if(in_array($requisition->status, ['draft','rejected','cancelled']))
          <form method="POST" action="{{ route('recruitment.requisitions.destroy', $requisition) }}" class="mt-2" onsubmit="return confirm('Hapus requisition ini?')">
            @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Hapus</button>
          </form>
        @endif
      </div>
    </div>

    @if($requisition->isOpen())
    <div class="card mb-4">
      <div class="card-header font-weight-bold d-flex justify-content-between align-items-center">
        <span>Kandidat</span>
        @can('recruitment.create')
        <a href="{{ route('recruitment.candidates.create', ['job_requisition_id' => $requisition->id]) }}" class="btn btn-xs btn-primary">
          <i class="gd-plus mr-1"></i> Tambah Kandidat
        </a>
        @endcan
      </div>
      <div class="card-body">
        @if($requisition->candidates->isEmpty())
          <p class="text-muted small mb-0">Belum ada kandidat.</p>
        @else
          <table class="table table-sm mb-0">
            <thead class="thead-light"><tr><th>Nama</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @foreach($requisition->candidates as $c)
              <tr>
                <td>{{ $c->name }}</td>
                <td><span class="badge badge-{{ \App\Models\Candidate::$statusBadges[$c->status] ?? 'secondary' }}">{{ \App\Models\Candidate::$statusLabels[$c->status] ?? $c->status }}</span></td>
                <td><a href="{{ route('recruitment.candidates.show', $c) }}" class="btn btn-xs btn-outline-secondary">Detail</a></td>
              </tr>
            @endforeach
            </tbody>
          </table>
        @endif
      </div>
    </div>
    @endif
  </div>

  <div class="col-lg-5">
    <div class="card">
      <div class="card-header font-weight-bold">Alur Persetujuan</div>
      <div class="card-body">
        @php $ar = $requisition->approvalRequest; @endphp
        @if(!$ar)
          <p class="text-muted small mb-0">Belum diajukan.</p>
        @else
          <ol class="list-unstyled mb-0">
            @foreach($ar->steps as $s)
              <li class="d-flex mb-3">
                <span class="mr-3">
                  @if($s->status === 'approved')<i class="gd-check text-success"></i>
                  @elseif($s->status === 'rejected')<i class="gd-close text-danger"></i>
                  @elseif($s->status === 'skipped')<i class="gd-minus text-muted"></i>
                  @else<i class="gd-time text-warning"></i>@endif
                </span>
                <div>
                  <div class="font-weight-bold small">Step {{ $s->step_order }} — {{ $s->approver_label }}</div>
                  <div class="small text-muted">{{ $s->approver?->name ?? '—' }}
                    @if($s->acted_at) · {{ ucfirst($s->status) }} {{ $s->acted_at->format('d/m/Y H:i') }}@endif</div>
                  @if($s->notes)<div class="small font-italic">"{{ $s->notes }}"</div>@endif
                </div>
              </li>
            @endforeach
          </ol>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
