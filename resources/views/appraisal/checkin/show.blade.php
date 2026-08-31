@extends('layouts.grain')
@section('title', '1-on-1 — ' . $employee->name)

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('appraisal.checkins.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>
<div class="h3 mb-4">1-on-1 — {{ $employee->name }}</div>

<div class="card mb-4">
  <div class="card-header font-weight-bold">Catatan Baru</div>
  <div class="card-body">
    <form method="POST" action="{{ route('appraisal.checkins.store', $employee) }}">
      @csrf
      <div class="form-row">
        <div class="form-group col-md-3">
          <label class="small">Tanggal <span class="text-danger">*</span></label>
          <input type="date" name="checkin_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}" required>
        </div>
        <div class="form-group col-md-3">
          <label class="small">1-on-1 Berikutnya</label>
          <input type="date" name="next_checkin_date" class="form-control form-control-sm">
        </div>
      </div>
      <div class="form-group">
        <label class="small">Catatan Diskusi <span class="text-danger">*</span></label>
        <textarea name="notes" rows="3" class="form-control form-control-sm" required placeholder="Apa yang dibahas, perkembangan, kendala..."></textarea>
      </div>
      <div class="form-group">
        <label class="small">Tindak Lanjut / Action Items</label>
        <textarea name="action_items" rows="2" class="form-control form-control-sm"></textarea>
      </div>
      <button class="btn btn-sm btn-primary">Simpan</button>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header font-weight-bold">Riwayat ({{ $checkins->count() }})</div>
  <div class="card-body">
    @forelse($checkins as $c)
      <div class="border rounded p-3 mb-3">
        <div class="d-flex justify-content-between align-items-start">
          <div class="font-weight-bold small">{{ $c->checkin_date->format('d M Y') }}
            <span class="text-muted font-weight-normal">— dicatat {{ $c->createdBy?->name ?? '—' }}</span></div>
          <form method="POST" action="{{ route('appraisal.checkins.destroy', $c) }}" onsubmit="return confirm('Hapus catatan ini?')">
            @csrf @method('DELETE')<button class="btn btn-xs btn-outline-danger"><i class="gd-trash"></i></button>
          </form>
        </div>
        <p class="small mb-1 mt-2">{{ $c->notes }}</p>
        @if($c->action_items)<p class="small text-muted mb-1"><strong>Tindak lanjut:</strong> {{ $c->action_items }}</p>@endif
        @if($c->next_checkin_date)<p class="small text-muted mb-1">Rencana 1-on-1 berikutnya: {{ $c->next_checkin_date->format('d/m/Y') }}</p>@endif
        @if($c->employee_comment)
          <div class="alert alert-light border py-2 px-3 small mb-0 mt-2"><strong>Komentar {{ $employee->name }}:</strong> {{ $c->employee_comment }}</div>
        @endif
      </div>
    @empty
      <p class="text-muted small mb-0">Belum ada catatan 1-on-1.</p>
    @endforelse
  </div>
</div>
@endsection
