@extends('layouts.grain')
@section('title', 'Detail ' . $cfg['label'])

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('approval.hr-request.index', $kind) }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="row">
  <div class="col-lg-7">
    <div class="card mb-4">
      <div class="card-header font-weight-bold d-flex justify-content-between">
        <span>{{ $cfg['label'] }}</span>
        <span class="badge badge-{{ ['draft'=>'secondary','pending'=>'warning','approved'=>'success','rejected'=>'danger','cancelled'=>'secondary'][$row->status] ?? 'secondary' }}">{{ ucfirst($row->status) }}</span>
      </div>
      <div class="card-body">
        <dl class="row mb-3">
          <dt class="col-sm-4 text-muted">Karyawan</dt><dd class="col-sm-8">{{ $row->employee?->name }}</dd>
        </dl>
        <div class="small">
          @foreach($row->getAttributes() as $k => $v)
            @continue(in_array($k, ['id','created_at','updated_at','company_id','requested_by_user_id','employee_id','status']))
            @continue($v === null || $v === '')
            <div><span class="text-muted">{{ $k }}:</span> {{ $v }}</div>
          @endforeach
        </div>

        @if($row->status === 'draft')
          <hr>
          <form method="POST" action="{{ route('approval.hr-request.submit', [$kind, $row->id]) }}">@csrf
            <button class="btn btn-success">Ajukan untuk Persetujuan</button>
            <a href="{{ route('approval.hr-request.edit', [$kind, $row->id]) }}" class="btn btn-outline-secondary">Edit</a>
          </form>
        @endif
        @if(in_array($row->status, ['draft','rejected','cancelled']))
          <form method="POST" action="{{ route('approval.hr-request.destroy', [$kind, $row->id]) }}" class="mt-2" onsubmit="return confirm('Hapus pengajuan?')">
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
        @php $ar = $row->approvalRequest; @endphp
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
