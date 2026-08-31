@extends('layouts.grain')
@section('title', '1-on-1 Saya')

@section('content')
@include('components.notification')

<div class="h3 mb-1">1-on-1 Saya</div>
<p class="text-muted">Riwayat catatan ngobrol rutin dengan atasan Anda.</p>

@forelse($checkins as $c)
  <div class="card mb-3">
    <div class="card-body">
      <div class="font-weight-bold small">{{ $c->checkin_date->format('d M Y') }}
        <span class="text-muted font-weight-normal">— {{ $c->createdBy?->name ?? '—' }}</span></div>
      <p class="small mb-1 mt-2">{{ $c->notes }}</p>
      @if($c->action_items)<p class="small text-muted mb-1"><strong>Tindak lanjut:</strong> {{ $c->action_items }}</p>@endif
      @if($c->next_checkin_date)<p class="small text-muted mb-2">1-on-1 berikutnya: {{ $c->next_checkin_date->format('d/m/Y') }}</p>@endif

      @if($c->employee_comment)
        <div class="alert alert-light border py-2 px-3 small mb-0"><strong>Komentar Anda:</strong> {{ $c->employee_comment }}</div>
      @else
        <form method="POST" action="{{ route('appraisal.checkins.comment', $c) }}" class="form-row align-items-end mt-2">
          @csrf
          <div class="form-group col-md-9 mb-1">
            <input type="text" name="employee_comment" class="form-control form-control-sm" placeholder="Tambah komentar Anda (opsional)">
          </div>
          <div class="form-group col-md-3 mb-1"><button class="btn btn-sm btn-outline-primary btn-block">Kirim</button></div>
        </form>
      @endif
    </div>
  </div>
@empty
  <div class="card"><div class="card-body text-muted small">Belum ada catatan 1-on-1.</div></div>
@endforelse
@endsection
