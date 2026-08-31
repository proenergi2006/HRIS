@extends('layouts.grain')
@section('title', 'Detail Pengajuan Lembur')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('hr.overtime-requests.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="row">
  <div class="col-lg-7">
    <div class="card mb-4">
      <div class="card-header font-weight-bold d-flex justify-content-between">
        <span>Lembur {{ $overtimeRequest->date?->format('d/m/Y') }}</span>
        <span class="badge badge-{{ \App\Models\OvertimeRequest::$statusBadges[$overtimeRequest->status] ?? 'secondary' }}">
          {{ \App\Models\OvertimeRequest::$statusLabels[$overtimeRequest->status] ?? $overtimeRequest->status }}
        </span>
      </div>
      <div class="card-body">
        <dl class="row mb-3">
          <dt class="col-sm-4 text-muted">Karyawan</dt><dd class="col-sm-8">{{ $overtimeRequest->employee?->name }}</dd>
          <dt class="col-sm-4 text-muted">Jumlah Jam</dt><dd class="col-sm-8">{{ rtrim(rtrim(number_format((float) $overtimeRequest->planned_hours, 1), '0'), '.') }} jam</dd>
          @if($overtimeRequest->reason)
            <dt class="col-sm-4 text-muted">Alasan</dt><dd class="col-sm-8">{{ $overtimeRequest->reason }}</dd>
          @endif
          @if($overtimeRequest->status === 'rejected' && $overtimeRequest->notes)
            <dt class="col-sm-4 text-muted">Catatan Penolakan</dt><dd class="col-sm-8 text-danger">{{ $overtimeRequest->notes }}</dd>
          @endif
        </dl>

        @if($overtimeRequest->isDraft())
          <hr>
          <form method="POST" action="{{ route('hr.overtime-requests.submit', $overtimeRequest) }}">@csrf
            <button class="btn btn-success">Ajukan untuk Persetujuan</button>
          </form>
        @endif
        @if(in_array($overtimeRequest->status, ['draft','rejected','cancelled']))
          <form method="POST" action="{{ route('hr.overtime-requests.destroy', $overtimeRequest) }}" class="mt-2" onsubmit="return confirm('Hapus pengajuan ini?')">
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
        @php $ar = $overtimeRequest->approvalRequest; @endphp
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
