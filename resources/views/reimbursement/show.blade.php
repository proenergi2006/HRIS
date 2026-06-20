@extends('layouts.grain')
@section('title', 'Detail Pengajuan Reimbursement')

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center">
  <a href="{{ route('reimbursement.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="gd-angle-left mr-1"></i> Kembali
  </a>
  <div>
    @if($reimbursement->isDraft())
      <a href="{{ route('reimbursement.edit', $reimbursement) }}" class="btn btn-sm btn-outline-warning mr-1">
        <i class="gd-pencil mr-1"></i> Edit
      </a>
      <form method="POST" action="{{ route('reimbursement.submit', $reimbursement) }}" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-sm btn-success"
                onclick="return confirm('Submit pengajuan ini? Tidak bisa diedit setelah disubmit.')">
          <i class="gd-arrow-up mr-1"></i> Submit Pengajuan
        </button>
      </form>
    @endif
    <a href="{{ route('reimbursement.pdf', $reimbursement) }}" target="_blank" class="btn btn-sm btn-outline-secondary ml-1">
      <i class="gd-export mr-1"></i> Download PDF
    </a>
  </div>
</div>

{{-- Status + Info --}}
<div class="card mb-3">
  <div class="card-body">
    <div class="row">
      <div class="col-sm-3">
        <div class="text-muted small">No. Pengajuan</div>
        <div class="font-weight-bold">{{ $reimbursement->request_number }}</div>
      </div>
      <div class="col-sm-2">
        <div class="text-muted small">Tanggal</div>
        <div>{{ $reimbursement->request_date->format('d M Y') }}</div>
      </div>
      <div class="col-sm-2">
        <div class="text-muted small">Untuk</div>
        <div>{{ \App\Models\Reimbursement\ReimbursementRequest::$medicalForLabels[$reimbursement->medical_for] }}</div>
      </div>
      <div class="col-sm-2">
        <div class="text-muted small">Status</div>
        <span class="badge badge-{{ \App\Models\Reimbursement\ReimbursementRequest::$statusBadges[$reimbursement->status] }}">
          {{ \App\Models\Reimbursement\ReimbursementRequest::$statusLabels[$reimbursement->status] }}
        </span>
      </div>
      <div class="col-sm-3 text-right">
        <div class="text-muted small">Total Klaim</div>
        <div class="h4 font-weight-bold text-primary mb-0">Rp {{ number_format($reimbursement->total_claim, 0, ',', '.') }}</div>
      </div>
    </div>
    @if($reimbursement->notes)
      <hr class="my-2">
      <small class="text-muted">Catatan: {{ $reimbursement->notes }}</small>
    @endif
    @if($reimbursement->isRejected() && $reimbursement->rejection_reason)
      <div class="alert alert-danger mt-2 mb-0 py-2">
        <strong>Alasan penolakan:</strong> {{ $reimbursement->rejection_reason }}
      </div>
    @endif
    @if($reimbursement->isApproved())
      <div class="alert alert-success mt-2 mb-0 py-2">
        Disetujui oleh <strong>{{ $reimbursement->approver?->name }}</strong>
        pada {{ $reimbursement->approved_at->format('d M Y, H:i') }}
      </div>
    @endif
  </div>
</div>

{{-- Saldo --}}
@if($balance)
<div class="row mb-3">
  <div class="col-sm-4"><div class="card text-center py-2">
    <div class="text-muted small">Saldo Awal {{ $balance->period_year }}</div>
    <div class="font-weight-bold text-primary">Rp {{ number_format($balance->initial_balance, 0, ',', '.') }}</div>
  </div></div>
  <div class="col-sm-4"><div class="card text-center py-2">
    <div class="text-muted small">Terpakai</div>
    <div class="font-weight-bold text-danger">Rp {{ number_format($balance->used_balance, 0, ',', '.') }}</div>
  </div></div>
  <div class="col-sm-4"><div class="card text-center py-2">
    <div class="text-muted small">Sisa Saldo</div>
    <div class="font-weight-bold text-success">Rp {{ number_format($balance->remaining_balance, 0, ',', '.') }}</div>
  </div></div>
</div>
@endif

{{-- Items Table --}}
<div class="card mb-3">
  <div class="card-header font-weight-bold">Rincian Biaya</div>
  <div class="card-body p-0" style="overflow-x:auto">
    <table class="table table-bordered table-sm mb-0" style="min-width:1200px;font-size:.82rem">
      <thead class="thead-light">
        <tr>
          <th>Nama Pasien</th>
          <th>Tgl Berobat</th>
          <th>Faskes / RS</th>
          <th>Diagnosa</th>
          @foreach(\App\Models\Reimbursement\ReimbursementItem::AMOUNT_FIELDS as $key => $lbl)
            <th class="text-right">{{ $lbl }}</th>
          @endforeach
          <th class="text-right">Total</th>
        </tr>
      </thead>
      <tbody>
      @foreach($reimbursement->items as $item)
        <tr>
          <td>{{ $item->patient_name }}</td>
          <td>{{ $item->treatment_date->format('d/m/Y') }}</td>
          <td>{{ $item->institution }}</td>
          <td>{{ $item->diagnose ?? '-' }}</td>
          @foreach(array_keys(\App\Models\Reimbursement\ReimbursementItem::AMOUNT_FIELDS) as $field)
            <td class="text-right">{{ $item->$field > 0 ? number_format($item->$field, 0, ',', '.') : '-' }}</td>
          @endforeach
          <td class="text-right font-weight-bold">{{ number_format($item->total_claim, 0, ',', '.') }}</td>
        </tr>
      @endforeach
      </tbody>
      <tfoot class="table-light font-weight-bold">
        <tr>
          <td colspan="4" class="text-right">Grand Total</td>
          @foreach(array_keys(\App\Models\Reimbursement\ReimbursementItem::AMOUNT_FIELDS) as $field)
            @php $colSum = $reimbursement->items->sum($field) @endphp
            <td class="text-right">{{ $colSum > 0 ? number_format($colSum, 0, ',', '.') : '-' }}</td>
          @endforeach
          <td class="text-right">Rp {{ number_format($reimbursement->total_claim, 0, ',', '.') }}</td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

{{-- Attachments --}}
@if($reimbursement->attachments->isNotEmpty())
<div class="card">
  <div class="card-header font-weight-bold">Lampiran</div>
  <div class="card-body py-2">
    @foreach($reimbursement->attachments as $att)
      <a href="{{ route('reimbursement.attachment', [$reimbursement, $att]) }}" target="_blank"
         class="btn btn-sm btn-outline-secondary mr-1 mb-1">
        <i class="gd-clip mr-1"></i>{{ $att->file_name }}
      </a>
    @endforeach
  </div>
</div>
@endif
@endsection
