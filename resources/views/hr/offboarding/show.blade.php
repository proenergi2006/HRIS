@extends('layouts.grain')
@section('title', 'Clearance Resign — ' . $employee->name)

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('hr.offboarding.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="h3 mb-3">Checklist Clearance Resign — {{ $employee->name }}</div>

@if($outstandingLoans->isNotEmpty())
  <div class="alert alert-warning py-2 px-3" style="font-size:.85rem">
    <i class="gd-alert mr-1"></i> Karyawan masih punya kasbon/pinjaman aktif dengan sisa
    <strong>Rp {{ number_format($outstandingLoans->sum(fn($l) => $l->outstanding()), 0, ',', '.') }}</strong>.
    <a href="{{ route('hr.loans.index', ['company_id' => $employee->company_id]) }}">Lihat Kasbon/Pinjaman</a>.
  </div>
@endif

@if($termination)
<div class="card mb-3 border-primary">
  <div class="card-header font-weight-bold">Exit Interview &amp; Final Settlement</div>
  <div class="card-body">
    <div class="small text-muted mb-3">
      Proses keluar: <strong>{{ \App\Models\TerminationRequest::$typeLabels[$termination->termination_type] ?? $termination->termination_type }}</strong>
      @if($termination->effective_date) · efektif {{ $termination->effective_date->format('d/m/Y') }}@endif
      @if($termination->reason) — "{{ $termination->reason }}"@endif
    </div>
    <form method="POST" action="{{ route('hr.offboarding.exit.update', $employee) }}">
      @csrf @method('PUT')
      <div class="row">
        <div class="col-md-6">
          <div class="font-weight-bold small text-muted mb-2">EXIT INTERVIEW</div>
          <div class="form-group">
            <label class="small">Tanggal</label>
            <input type="date" name="exit_interview_date" class="form-control form-control-sm"
                   value="{{ optional($termination->exit_interview_date)->format('Y-m-d') }}">
          </div>
          <div class="form-group">
            <label class="small">Catatan / Ringkasan Wawancara</label>
            <textarea name="exit_interview_notes" rows="4" class="form-control form-control-sm">{{ $termination->exit_interview_notes }}</textarea>
          </div>
          @if($termination->hasExitInterview())
            <div class="small text-success"><i class="gd-check"></i> Tercatat oleh {{ $termination->exitInterviewBy?->name ?? '—' }}</div>
          @endif
        </div>
        <div class="col-md-6">
          <div class="font-weight-bold small text-muted mb-2">FINAL SETTLEMENT</div>
          <div class="form-group">
            <label class="small">Tanggal Pembayaran</label>
            <input type="date" name="final_settlement_date" class="form-control form-control-sm"
                   value="{{ optional($termination->final_settlement_date)->format('Y-m-d') }}">
          </div>
          <div class="form-group">
            <label class="small">Nominal (Rp)</label>
            <input type="text" data-rupiah name="final_settlement_amount" class="form-control form-control-sm"
                   value="{{ $termination->final_settlement_amount }}" placeholder="mis. sisa gaji, pesangon, uang cuti">
          </div>
          <div class="form-group">
            <label class="small">Rincian / Catatan</label>
            <textarea name="final_settlement_notes" rows="2" class="form-control form-control-sm">{{ $termination->final_settlement_notes }}</textarea>
          </div>
        </div>
      </div>
      <button class="btn btn-sm btn-primary">Simpan</button>
      <a href="{{ route('appraisal.employees.documents.index', $employee) }}" class="btn btn-sm btn-outline-secondary">
        + Unggah Resignation Letter / Berita Acara Clearance
      </a>
    </form>
  </div>
</div>
@endif

@foreach(\App\Models\OffboardingChecklistItem::$categoryLabels as $catKey => $catLabel)
  @php $catTasks = $tasks->filter(fn($t) => $t->item->category === $catKey); @endphp
  @continue($catTasks->isEmpty())
  <div class="card mb-3">
    <div class="card-header font-weight-bold">{{ $catLabel }}</div>
    <div class="card-body">
      @foreach($catTasks as $task)
        <form method="POST" action="{{ route('hr.offboarding.tasks.toggle', [$employee, $task]) }}"
              class="d-flex justify-content-between align-items-center border-bottom py-2">
          @csrf
          <div>
            <span class="{{ $task->is_done ? 'text-muted' : '' }}" style="{{ $task->is_done ? 'text-decoration:line-through' : '' }}">
              {{ $task->item->label }}
            </span>
            @if($task->item->is_required)<span class="badge badge-light border ml-1" style="font-size:.65rem">wajib</span>@endif
            @if($task->is_done)
              <div class="small text-muted">Selesai {{ $task->done_at?->format('d/m/Y H:i') }} · {{ $task->doneBy?->name }}</div>
            @endif
          </div>
          <button type="submit" class="btn btn-sm {{ $task->is_done ? 'btn-outline-secondary' : 'btn-success' }}">
            {{ $task->is_done ? 'Batalkan' : 'Tandai Selesai' }}
          </button>
        </form>
      @endforeach
    </div>
  </div>
@endforeach
@endsection
