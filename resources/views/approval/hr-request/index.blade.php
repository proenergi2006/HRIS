@extends('layouts.grain')
@section('title', $cfg['label'])

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div class="h3 mb-0">Pengajuan {{ $cfg['label'] }}</div>
      <a href="{{ route('approval.hr-request.create', $kind) }}" class="btn btn-primary"><i class="gd-plus mr-1"></i>Buat Pengajuan</a>
    </div>

    <div class="table-responsive">
      <table class="table table-sm">
        <thead class="thead-light"><tr><th>Karyawan</th><th>Ringkasan</th><th>Status</th><th>Persetujuan</th><th style="width:120px"></th></tr></thead>
        <tbody>
        @forelse($requests as $r)
          <tr>
            <td class="align-middle">{{ $r->employee?->name ?? '-' }}</td>
            <td class="align-middle small">{{ $r->approvalSummary() }}</td>
            <td class="align-middle"><span class="badge badge-{{ ['draft'=>'secondary','pending'=>'warning','approved'=>'success','rejected'=>'danger','cancelled'=>'secondary'][$r->status] ?? 'secondary' }}">{{ ucfirst($r->status) }}</span></td>
            <td class="align-middle small">
              @php $ar = $r->approvalRequest; @endphp
              @if($ar)
                Step {{ $ar->current_step_order }} / {{ $ar->steps->count() }}
              @else — @endif
            </td>
            <td class="align-middle">
              <a href="{{ route('approval.hr-request.show', [$kind, $r->id]) }}" class="btn btn-xs btn-outline-secondary">Detail</a>
              @if($r->status === 'draft')
                <a href="{{ route('approval.hr-request.edit', [$kind, $r->id]) }}" class="btn btn-xs btn-outline-warning">Edit</a>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-muted small py-3">Belum ada pengajuan.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
    {{ $requests->links() }}
  </div>
</div>
@endsection
