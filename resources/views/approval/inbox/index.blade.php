@extends('layouts.grain')
@section('title', 'Kotak Persetujuan')

@section('content')
@include('components.notification')

<div class="card mb-3 mb-md-4">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div class="h3 mb-0">Kotak Persetujuan</div>
      <div>
        <a href="{{ route('approval.inbox.history') }}" class="btn btn-sm btn-outline-secondary">Riwayat</a>
        <a href="{{ route('approval.delegations.index') }}" class="btn btn-sm btn-outline-secondary">Delegasi</a>
      </div>
    </div>

    @if($steps->isEmpty())
      <p class="text-muted">Tidak ada pengajuan yang menunggu persetujuan Anda.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm">
          <thead class="thead-light">
            <tr><th>Jenis</th><th>Ringkasan</th><th>Pengaju</th><th>Diajukan</th><th style="width:130px">Peran Anda</th><th style="width:90px"></th></tr>
          </thead>
          <tbody>
          @foreach($steps as $step)
            <tr class="{{ $step->isOverdue() ? 'table-warning' : '' }}">
              <td class="align-middle"><span class="badge badge-info">{{ $step->request->type_label }}</span></td>
              <td class="align-middle">{{ $step->request->summary }}</td>
              <td class="align-middle small">{{ $step->request->requester?->name ?? '-' }}</td>
              <td class="align-middle small">{{ $step->request->submitted_at?->format('d/m/Y H:i') }}</td>
              <td class="align-middle small">{{ $step->approver_label }}{{ $step->approver_user_id !== auth()->id() && $step->approver_user_id ? ' (delegasi)' : '' }}</td>
              <td class="align-middle"><a href="{{ route('approval.inbox.show', $step) }}" class="btn btn-sm btn-primary">Tinjau</a></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
