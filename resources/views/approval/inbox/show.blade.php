@extends('layouts.grain')
@section('title', 'Tinjau Persetujuan')

@section('content')
@include('components.notification')

@php $req = $step->request; @endphp

<div class="mb-3"><a href="{{ route('approval.inbox.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali ke Kotak Persetujuan</a></div>

<div class="row">
  <div class="col-lg-7">
    <div class="card mb-4">
      <div class="card-header font-weight-bold">{{ $req->type_label }}</div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-4 text-muted">Ringkasan</dt><dd class="col-sm-8">{{ $req->summary }}</dd>
          <dt class="col-sm-4 text-muted">Pengaju</dt><dd class="col-sm-8">{{ $req->requester?->name ?? '-' }}</dd>
          <dt class="col-sm-4 text-muted">Karyawan Terkait</dt><dd class="col-sm-8">{{ $req->subjectEmployee?->name ?? '-' }}</dd>
          <dt class="col-sm-4 text-muted">Perusahaan</dt><dd class="col-sm-8">{{ $req->company?->short_name ?? $req->company?->name ?? '-' }}</dd>
          <dt class="col-sm-4 text-muted">Diajukan</dt><dd class="col-sm-8">{{ $req->submitted_at?->format('d/m/Y H:i') }}</dd>
        </dl>

        @php $a = $req->approvable; @endphp
        @if($a)
          <hr>
          <div class="small">
            @if(method_exists($a, 'approvalDetails'))
              @foreach($a->approvalDetails() as $label => $v)
                <div><span class="text-muted">{{ $label }}:</span> {{ $v }}</div>
              @endforeach
            @else
              {{-- Fallback generik: model ini belum punya approvalDetails() sendiri,
                   jadi tampilkan raw attribute apa adanya (bisa berisi ID mentah). --}}
              @foreach($a->getAttributes() as $k => $v)
                @continue(in_array($k, ['id','created_at','updated_at','company_id','requested_by_user_id','employee_id','status']))
                @continue($v === null || $v === '')
                <div><span class="text-muted">{{ $k }}:</span> {{ $v }}</div>
              @endforeach
            @endif
          </div>
        @endif
      </div>
    </div>

    @if($step->status === 'pending' && $req->status === 'pending')
    <div class="card">
      <div class="card-body">
        <form method="POST" action="{{ route('approval.inbox.approve', $step) }}" class="mb-2">
          @csrf
          <div class="form-group"><label class="small">Catatan (opsional)</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
          <button class="btn btn-success"><i class="gd-check mr-1"></i>Setujui</button>
        </form>
        <form method="POST" action="{{ route('approval.inbox.reject', $step) }}"
              onsubmit="return confirm('Tolak pengajuan ini? Alur akan berhenti.')">
          @csrf
          <div class="form-group"><label class="small">Alasan penolakan <span class="text-danger">*</span></label><textarea name="notes" class="form-control" rows="2" required></textarea></div>
          <button class="btn btn-outline-danger"><i class="gd-close mr-1"></i>Tolak</button>
        </form>
      </div>
    </div>
    @endif
  </div>

  <div class="col-lg-5">
    <div class="card">
      <div class="card-header font-weight-bold">Alur Persetujuan</div>
      <div class="card-body">
        <ol class="list-unstyled mb-0">
          @foreach($req->steps as $s)
            <li class="d-flex mb-3">
              <span class="mr-3">
                @if($s->status === 'approved')<i class="gd-check text-success"></i>
                @elseif($s->status === 'rejected')<i class="gd-close text-danger"></i>
                @elseif($s->status === 'skipped')<i class="gd-minus text-muted"></i>
                @else<i class="gd-time text-warning"></i>@endif
              </span>
              <div>
                <div class="font-weight-bold small">Step {{ $s->step_order }} — {{ $s->approver_label }}</div>
                <div class="small text-muted">
                  {{ $s->approver?->name ?? ($s->approver_type === 'specific_role' ? 'role-based' : '—') }}
                  @if($s->acted_at) · {{ ucfirst($s->status) }} oleh {{ $s->actedBy?->name }} {{ $s->acted_at->format('d/m/Y H:i') }}@endif
                </div>
                @if($s->notes)<div class="small font-italic">"{{ $s->notes }}"</div>@endif
              </div>
            </li>
          @endforeach
        </ol>
      </div>
    </div>
  </div>
</div>
@endsection
