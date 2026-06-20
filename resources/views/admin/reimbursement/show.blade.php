@extends('layouts.grain')
@section('title', 'Detail Pengajuan - ' . $reimbursement->request_number)

@section('content')
@include('components.notification')

<div class="mb-3 d-flex justify-content-between align-items-center">
  <a href="{{ route('reimbursement.admin.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="gd-angle-left mr-1"></i> Kembali
  </a>
  <a href="{{ route('reimbursement.admin.pdf', $reimbursement) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
    <i class="gd-export mr-1"></i> Download PDF
  </a>
</div>

{{-- Info Header --}}
<div class="card mb-3">
  <div class="card-body">
    <div class="row">
      <div class="col-sm-3">
        <div class="text-muted small">No. Pengajuan</div>
        <div class="font-weight-bold">{{ $reimbursement->request_number }}</div>
      </div>
      <div class="col-sm-3">
        <div class="text-muted small">Karyawan</div>
        <div class="font-weight-bold">{{ $reimbursement->user->name }}</div>
      </div>
      <div class="col-sm-2">
        <div class="text-muted small">Tanggal</div>
        <div>{{ $reimbursement->request_date->format('d M Y') }}</div>
      </div>
      <div class="col-sm-2">
        <div class="text-muted small">Untuk</div>
        <div>{{ \App\Models\Reimbursement\ReimbursementRequest::$medicalForLabels[$reimbursement->medical_for] }}</div>
      </div>
      <div class="col-sm-2 text-right">
        <div class="text-muted small">Total Klaim</div>
        <div class="h4 font-weight-bold text-primary mb-0">Rp {{ number_format($reimbursement->total_claim, 0, ',', '.') }}</div>
      </div>
    </div>
    @if($reimbursement->notes)
      <hr class="my-2"><small class="text-muted">Catatan: {{ $reimbursement->notes }}</small>
    @endif
  </div>
</div>

{{-- Saldo Karyawan --}}
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
    <div class="font-weight-bold {{ $reimbursement->total_claim > $balance->remaining_balance ? 'text-danger' : 'text-success' }}">
      Rp {{ number_format($balance->remaining_balance, 0, ',', '.') }}
    </div>
  </div></div>
</div>
@else
<div class="alert alert-warning mb-3">Karyawan ini belum memiliki saldo untuk tahun {{ $reimbursement->request_date->year }}.</div>
@endif

{{-- Items --}}
<div class="card mb-3">
  <div class="card-header font-weight-bold">Rincian Biaya</div>
  <div class="card-body p-0" style="overflow-x:auto">
    <table class="table table-bordered table-sm mb-0" style="min-width:1200px;font-size:.82rem">
      <thead class="thead-light">
        <tr>
          <th>Nama Pasien</th><th>Tgl Berobat</th><th>Faskes / RS</th><th>Diagnosa</th>
          @foreach(\App\Models\Reimbursement\ReimbursementItem::AMOUNT_FIELDS as $lbl)
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
            @php $s = $reimbursement->items->sum($field) @endphp
            <td class="text-right">{{ $s > 0 ? number_format($s, 0, ',', '.') : '-' }}</td>
          @endforeach
          <td class="text-right">Rp {{ number_format($reimbursement->total_claim, 0, ',', '.') }}</td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

{{-- Attachments --}}
@if($reimbursement->attachments->isNotEmpty())
<div class="card mb-3">
  <div class="card-header font-weight-bold">Lampiran</div>
  <div class="card-body py-2">
    @foreach($reimbursement->attachments as $att)
      <a href="{{ route('reimbursement.admin.attachment', [$reimbursement, $att]) }}" target="_blank"
         class="btn btn-sm btn-outline-secondary mr-1 mb-1">
        <i class="gd-clip mr-1"></i>{{ $att->file_name }}
      </a>
    @endforeach
  </div>
</div>
@endif

{{-- Approval Actions --}}
@if($reimbursement->isSubmitted())
<div class="card border-warning">
  <div class="card-header font-weight-bold bg-warning text-dark">Tindakan Approval</div>
  <div class="card-body">
    <div class="row">
      <div class="col-md-6">
        <form method="POST" action="{{ route('reimbursement.admin.approve', $reimbursement) }}">
          @csrf
          <button type="submit" class="btn btn-success btn-block"
                  onclick="return confirm('Setujui pengajuan ini? Saldo karyawan akan berkurang Rp {{ number_format($reimbursement->total_claim, 0, ',', '.') }}')">
            <i class="gd-check mr-1"></i> Setujui Pengajuan
          </button>
        </form>
      </div>
      <div class="col-md-6">
        <form method="POST" action="{{ route('reimbursement.admin.reject', $reimbursement) }}">
          @csrf
          <div class="input-group">
            <input type="text" name="rejection_reason" class="form-control" placeholder="Alasan penolakan (opsional)">
            <div class="input-group-append">
              <button type="submit" class="btn btn-danger"
                      onclick="return confirm('Tolak pengajuan ini?')">
                <i class="gd-close mr-1"></i> Tolak
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@elseif($reimbursement->isApproved())
<div class="alert alert-success">
  Disetujui oleh <strong>{{ $reimbursement->approver?->name }}</strong>
  pada {{ $reimbursement->approved_at->format('d M Y, H:i') }}
</div>
@elseif($reimbursement->isRejected())
<div class="alert alert-danger">
  Ditolak oleh <strong>{{ $reimbursement->approver?->name }}</strong>
  pada {{ $reimbursement->approved_at->format('d M Y, H:i') }}
  @if($reimbursement->rejection_reason)
    <br><strong>Alasan:</strong> {{ $reimbursement->rejection_reason }}
  @endif
</div>
@endif
@endsection
