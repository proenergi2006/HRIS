@extends('layouts.grain')
@section('title', 'Detail Perjalanan Dinas')

@php
  $labels  = \App\Models\Perdin\PerdinRequest::$statusLabels;
  $badges  = \App\Models\Perdin\PerdinRequest::$statusBadges;
  $cats    = \App\Models\Perdin\PerdinRequest::$categoryLabels;
  $roleLbl = \App\Models\Perdin\PerdinApproval::$roleLabels;
  $isOwner = $perdin->user_id === auth()->id();
@endphp

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center">
  <a href="{{ route('perdin.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="gd-angle-left mr-1"></i> Kembali
  </a>
  <div>
    @if($isOwner && $perdin->isEditable())
      <a href="{{ route('perdin.edit', $perdin) }}" class="btn btn-sm btn-outline-warning mr-1">
        <i class="gd-pencil mr-1"></i> Edit
      </a>
      <form method="POST" action="{{ route('perdin.submit', $perdin) }}" class="d-inline" id="form-submit-perdin">
        @csrf
        <button type="button" class="btn btn-sm btn-success"
                data-confirm="Permohonan {{ $perdin->no_advance }} (Rp {{ number_format($perdin->total_budget, 0, ',', '.') }}) akan dikirim untuk disetujui. Setelah disubmit tidak bisa diedit kembali."
                data-confirm-title="Submit Permohonan?"
                data-confirm-type="primary"
                data-confirm-ok="Ya, Submit"
                data-form="form-submit-perdin">
          <i class="gd-arrow-up mr-1"></i> Submit
        </button>
      </form>
    @endif
    <a href="{{ route('perdin.pdf', $perdin) }}" target="_blank" class="btn btn-sm btn-outline-secondary ml-1">
      <i class="gd-export mr-1"></i> Download PDF
    </a>
  </div>
</div>

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

{{-- Header info --}}
<div class="card mb-3">
  <div class="card-body">
    <div class="row">
      <div class="col-sm-3">
        <div class="text-muted small">No. Advance</div>
        <div class="font-weight-bold">{{ $perdin->no_advance }}</div>
      </div>
      <div class="col-sm-3">
        <div class="text-muted small">Pemohon</div>
        <div>{{ $perdin->user->name }}</div>
      </div>
      <div class="col-sm-2">
        <div class="text-muted small">Tujuan</div>
        <div>{{ $perdin->destination }}</div>
      </div>
      <div class="col-sm-2">
        <div class="text-muted small">Status</div>
        <span class="badge badge-{{ $badges[$perdin->status] }}">{{ $labels[$perdin->status] }}</span>
      </div>
      <div class="col-sm-2 text-right">
        <div class="text-muted small">Total Anggaran</div>
        <div class="h5 font-weight-bold text-primary mb-0">Rp {{ number_format($perdin->total_budget, 0, ',', '.') }}</div>
      </div>
    </div>
    <hr class="my-2">
    <div class="row small">
      <div class="col-sm-3"><span class="text-muted">Departemen:</span> {{ $perdin->department ?? '-' }}</div>
      <div class="col-sm-3"><span class="text-muted">Berangkat:</span> {{ $perdin->departure_date->format('d M Y') }} {{ $perdin->departure_time }}</div>
      <div class="col-sm-3"><span class="text-muted">Kembali:</span> {{ $perdin->return_date->format('d M Y') }} {{ $perdin->return_time }}</div>
      <div class="col-sm-3"><span class="text-muted">Ditanggung Sendiri:</span> Rp {{ number_format($perdin->total_budget_self, 0, ',', '.') }}</div>
    </div>
    @if($perdin->purpose)
      <hr class="my-2">
      <small class="text-muted">Maksud Perjalanan:</small>
      <div>{{ $perdin->purpose }}</div>
    @endif
    @if($perdin->isRejected() && $perdin->notes_rejection)
      <div class="alert alert-danger mt-2 mb-0 py-2">
        <strong>Alasan penolakan:</strong> {{ $perdin->notes_rejection }}
      </div>
    @endif
  </div>
</div>

{{-- Approval action (for current approver) --}}
@if($canApprove)
<div class="card mb-3 border-primary">
  <div class="card-header bg-primary text-white font-weight-bold">
    Tindakan Persetujuan — {{ $roleLbl[$perdin->nextApprovalRole()] ?? '' }}
  </div>
  <div class="card-body">
    <div class="form-row">
      <div class="col-md-8 mb-2">
        <form method="POST" action="{{ route('perdin.approve', $perdin) }}" id="form-approve">
          @csrf
          <input type="hidden" name="notes" id="approve-notes-hidden">
        </form>
        <form method="POST" action="{{ route('perdin.reject', $perdin) }}" id="form-reject">
          @csrf
          <label class="font-weight-bold small">Catatan</label>
          <textarea name="notes" id="reject-notes" class="form-control" rows="2"
                    placeholder="Catatan (wajib jika menolak)"></textarea>
        </form>
      </div>
      <div class="col-md-4 d-flex align-items-end mb-2">
        <button type="button" class="btn btn-success mr-2" onclick="
          document.getElementById('approve-notes-hidden').value = document.getElementById('reject-notes').value;
          document.getElementById('form-approve').submit();">
          <i class="gd-check mr-1"></i> Setujui
        </button>
        <button type="submit" form="form-reject" class="btn btn-outline-danger"
          onclick="if(!document.getElementById('reject-notes').value.trim()){alert('Catatan wajib diisi untuk menolak.');return false;}">
          <i class="gd-ban mr-1"></i> Tolak
        </button>
      </div>
    </div>
  </div>
</div>
@endif

{{-- Budget grouped by category --}}
<div class="card mb-3">
  <div class="card-header font-weight-bold">Rincian Anggaran</div>
  <div class="card-body">
    <div class="table-responsive">
    <table class="table table-bordered table-sm mb-0" style="min-width:720px;font-size:.85rem">
      <thead class="thead-light">
        <tr>
          <th>Item</th>
          <th class="text-center">Ditanggung</th>
          <th class="text-right">Qty</th>
          <th class="text-right">Biaya Satuan</th>
          <th class="text-right">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($cats as $catKey => $catLabel)
          @php $rows = $perdin->budgetItems->where('category', $catKey); @endphp
          @if($rows->isNotEmpty())
            <tr class="table-light"><td colspan="5" class="font-weight-bold">{{ $catLabel }}</td></tr>
            @foreach($rows as $item)
              <tr>
                <td class="pl-4">{{ $item->item_name }}</td>
                <td class="text-center">
                  @if($item->isByGa())
                    <span class="badge badge-info">By GA</span>
                  @else
                    <span class="badge badge-light">Sendiri</span>
                  @endif
                </td>
                <td class="text-right">{{ $item->qty }}</td>
                <td class="text-right">{{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->total_cost, 0, ',', '.') }}</td>
              </tr>
            @endforeach
          @endif
        @endforeach
      </tbody>
      <tfoot class="table-light font-weight-bold">
        <tr><td colspan="4" class="text-right">Total Anggaran</td>
            <td class="text-right">Rp {{ number_format($perdin->total_budget, 0, ',', '.') }}</td></tr>
      </tfoot>
    </table>
    </div>
  </div>
</div>

{{-- Itinerary --}}
@if($perdin->itineraries->isNotEmpty())
<div class="card mb-3">
  <div class="card-header font-weight-bold">Itinerary — {{ $perdin->routeLabel() }}</div>
  <div class="card-body">
    <div class="table-responsive">
    <table class="table table-bordered table-sm mb-0" style="font-size:.85rem">
      <thead class="thead-light">
        <tr><th style="width:40px">No</th><th>Tanggal</th><th>Jam</th><th>Zona</th><th>Keterangan</th></tr>
      </thead>
      <tbody>
        @foreach($perdin->itineraries as $it)
        <tr>
          <td class="text-center">{{ $it->no }}</td>
          <td>{{ $it->travel_date->format('d M Y') }}</td>
          <td>{{ $it->time_start }}@if($it->time_end) – {{ $it->time_end }}@endif</td>
          <td>{{ $it->timezone }}</td>
          <td>{{ $it->description }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    </div>
  </div>
</div>
@endif

{{-- Approval timeline --}}
<div class="card">
  <div class="card-header font-weight-bold">Riwayat Persetujuan</div>
  <div class="card-body">
    @forelse($perdin->approvals as $ap)
      <div class="d-flex mb-2">
        <div class="mr-2">
          <span class="badge badge-{{ $ap->action === 'approve' ? 'success' : 'danger' }}">
            {{ $ap->action === 'approve' ? 'Disetujui' : 'Ditolak' }}
          </span>
        </div>
        <div>
          <strong>{{ $roleLbl[$ap->role] ?? $ap->role }}</strong> — {{ $ap->approver?->name ?? '-' }}
          <span class="text-muted small">{{ $ap->acted_at?->format('d M Y, H:i') }}</span>
          @if($ap->notes)<div class="small text-muted">“{{ $ap->notes }}”</div>@endif
        </div>
      </div>
    @empty
      <div class="text-muted text-center py-2">Belum ada tindakan persetujuan.</div>
    @endforelse
  </div>
</div>
@endsection
