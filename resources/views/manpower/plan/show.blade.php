@extends('layouts.grain')
@section('title', 'Detail Rencana Manpower')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('manpower.plans.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="row">
  <div class="col-lg-7">
    <div class="card mb-4">
      <div class="card-header font-weight-bold d-flex justify-content-between">
        <span>{{ $plan->scopeLabel() }} — {{ $plan->periodLabel() }}</span>
        <span class="badge badge-{{ \App\Models\ManpowerPlan::$statusBadges[$plan->status] ?? 'secondary' }}">
          {{ \App\Models\ManpowerPlan::$statusLabels[$plan->status] ?? $plan->status }}
        </span>
      </div>
      <div class="card-body">
        <dl class="row mb-3">
          <dt class="col-sm-4 text-muted">Perusahaan</dt><dd class="col-sm-8">{{ $plan->company?->short_name ?? $plan->company?->name }}</dd>
          <dt class="col-sm-4 text-muted">Departemen</dt><dd class="col-sm-8">{{ $plan->department?->name ?? '—' }}</dd>
          <dt class="col-sm-4 text-muted">Section</dt><dd class="col-sm-8">{{ $plan->section?->name ?? '—' }}</dd>
          <dt class="col-sm-4 text-muted">Jabatan</dt><dd class="col-sm-8">{{ $plan->position?->name ?? '—' }}</dd>
          <dt class="col-sm-4 text-muted">Rencana Headcount</dt><dd class="col-sm-8">{{ $plan->planned_headcount }}</dd>
          <dt class="col-sm-4 text-muted">Aktual Sekarang</dt><dd class="col-sm-8">{{ $plan->actualHeadcount() }}</dd>
          <dt class="col-sm-4 text-muted">Diajukan Oleh</dt><dd class="col-sm-8">{{ $plan->requestedBy?->name ?? '—' }}</dd>
          @if($plan->notes)
            <dt class="col-sm-4 text-muted">Catatan</dt><dd class="col-sm-8">{{ $plan->notes }}</dd>
          @endif
          @if($plan->status === 'rejected' && $plan->notes_rejection)
            <dt class="col-sm-4 text-muted">Alasan Ditolak</dt><dd class="col-sm-8 text-danger">{{ $plan->notes_rejection }}</dd>
          @endif
        </dl>

        @if($plan->isDraft())
          <hr>
          <form method="POST" action="{{ route('manpower.plans.submit', $plan) }}">@csrf
            <button class="btn btn-success">Ajukan untuk Persetujuan</button>
            <a href="{{ route('manpower.plans.edit', $plan) }}" class="btn btn-outline-secondary">Edit</a>
          </form>
        @endif
        @if(in_array($plan->status, ['draft','rejected','cancelled']))
          <form method="POST" action="{{ route('manpower.plans.destroy', $plan) }}" class="mt-2" onsubmit="return confirm('Hapus rencana ini?')">
            @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Hapus</button>
          </form>
        @endif
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card">
      <div class="card-header font-weight-bold">Alur Persetujuan</div>
      <div class="card-body">
        @php $ar = $plan->approvalRequest; @endphp
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
