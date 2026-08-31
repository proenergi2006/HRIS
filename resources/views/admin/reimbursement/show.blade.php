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
  <div class="card-body">
    <div class="table-responsive">
    <table class="table table-bordered table-sm mb-0" style="min-width:1200px;font-size:.82rem">
      <thead class="thead-light">
        <tr>
          <th>Nama Pasien</th><th>Tgl Berobat</th><th>Faskes / RS</th><th>Diagnosa</th>
          @foreach(\App\Models\Reimbursement\ReimbursementItem::AMOUNT_FIELDS as $lbl)
            <th class="text-right">{{ $lbl }}</th>
          @endforeach
          <th class="text-right">Total</th>
          @if($reimbursement->isSubmitted())
            <th class="text-center" style="width:90px">Aksi</th>
          @endif
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
          @if($reimbursement->isSubmitted())
            <td class="text-center" style="white-space:nowrap">
              <button type="button" class="btn btn-xs btn-outline-warning" title="Koreksi rincian"
                      onclick="openSiproModal('edit-item-{{ $item->id }}')">
                <i class="gd-pencil"></i>
              </button>
              <form method="POST" action="{{ route('reimbursement.admin.items.destroy', [$reimbursement, $item]) }}"
                    id="form-delete-item-{{ $item->id }}" class="d-inline">
                @csrf @method('DELETE')
              </form>
              <button type="button" class="btn btn-xs btn-outline-danger" title="Hapus item"
                      data-confirm="Hapus item &quot;{{ $item->patient_name }}&quot; ({{ $item->institution }}, {{ $item->treatment_date->format('d/m/Y') }}) dari pengajuan ini? Total klaim akan dihitung ulang."
                      data-confirm-title="Hapus Item Klaim?"
                      data-confirm-type="danger"
                      data-confirm-ok="Ya, Hapus"
                      data-form="form-delete-item-{{ $item->id }}">
                <i class="gd-trash"></i>
              </button>
            </td>
          @endif
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
          @if($reimbursement->isSubmitted())<td></td>@endif
        </tr>
      </tfoot>
    </table>
    </div>
    @if($reimbursement->isSubmitted())
      <small class="text-muted d-block mt-2">
        Gunakan <i class="gd-pencil"></i> untuk mengoreksi nominal item (mis. biaya obat yang tidak ditanggung), atau <i class="gd-trash"></i> untuk menghapus item yang tidak bisa diklaim sama sekali. Total klaim dihitung ulang otomatis.
      </small>
    @endif
  </div>
</div>

{{-- Attachments --}}
@php
  $docTypes  = \App\Models\Reimbursement\ReimbursementAttachment::$docTypes;
  $attByType = $reimbursement->attachments->keyBy('doc_type');
@endphp
@if($reimbursement->attachments->isNotEmpty())
<div class="card mb-3">
  <div class="card-header font-weight-bold">Dokumen Pendukung</div>
  <div class="card-body p-0">
    <table class="table table-sm mb-0">
      <thead class="thead-light">
        <tr>
          <th style="width:220px">Jenis Dokumen</th>
          <th>File</th>
          <th class="text-center" style="width:80px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($docTypes as $type => $label)
          @if(isset($attByType[$type]))
          @php $att = $attByType[$type]; @endphp
          <tr>
            <td class="font-weight-bold" style="font-size:.88rem">{{ $label }}</td>
            <td>
              <a href="{{ route('reimbursement.admin.attachment', [$reimbursement, $att]) }}"
                 target="_blank" class="btn btn-xs btn-outline-primary">
                <i class="gd-clip mr-1"></i>{{ $att->file_name }}
              </a>
            </td>
            <td class="text-center">
              <a href="{{ route('reimbursement.admin.attachment', [$reimbursement, $att]) }}"
                 class="btn btn-xs btn-outline-secondary" download title="Unduh">
                <i class="gd-download"></i>
              </a>
            </td>
          </tr>
          @endif
        @endforeach
        {{-- Legacy attachments without doc_type: admin bisa set jenisnya --}}
        @foreach($reimbursement->attachments->whereNull('doc_type') as $att)
        <tr>
          <td style="font-size:.88rem">
            <form method="POST" action="{{ route('reimbursement.admin.attachment.doc-type', [$reimbursement, $att]) }}"
                  class="d-flex align-items-center" style="gap:.35rem">
              @csrf
              <select name="doc_type" class="form-control form-control-sm" style="max-width:220px" required>
                <option value="">-- Jenis dokumen? --</option>
                @foreach($docTypes as $type => $label)
                  <option value="{{ $type }}">{{ $label }}</option>
                @endforeach
              </select>
              <button type="submit" class="btn btn-xs btn-outline-primary" title="Simpan jenis dokumen">
                <i class="gd-check"></i>
              </button>
            </form>
          </td>
          <td>
            <a href="{{ route('reimbursement.admin.attachment', [$reimbursement, $att]) }}"
               target="_blank" class="btn btn-xs btn-outline-secondary">
              <i class="gd-clip mr-1"></i>{{ $att->file_name }}
            </a>
          </td>
          <td></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

{{-- Approval Actions --}}
@php $ar = $reimbursement->approvalRequest; @endphp
@if($reimbursement->isPending())
<div class="card border-warning mb-3">
  <div class="card-header font-weight-bold bg-warning text-dark">Periode Pembayaran</div>
  <div class="card-body">
    <form method="POST" action="{{ route('reimbursement.admin.payment-period', $reimbursement) }}">
      @csrf
      <div class="form-group mb-2" style="max-width:320px">
        <label class="font-weight-bold small">Periode Pembayaran (bulan gaji)</label>
        <div class="form-row">
          <div class="col-7">
            <select name="payment_month" class="form-control form-control-sm" required>
              @foreach(range(1,12) as $m)
                <option value="{{ $m }}" {{ (int) old('payment_month', $reimbursement->payment_month ?? now()->month) === $m ? 'selected' : '' }}>
                  {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-5">
            <select name="payment_year" class="form-control form-control-sm" required>
              @foreach(range(now()->year - 1, now()->year + 1) as $y)
                <option value="{{ $y }}" {{ (int) old('payment_year', $reimbursement->payment_year ?? now()->year) === $y ? 'selected' : '' }}>{{ $y }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <small class="text-muted">Klaim ini akan masuk pembayaran gaji periode bulan tersebut saat disetujui.</small>
      </div>
      <button class="btn btn-sm btn-outline-primary">Simpan Periode</button>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header font-weight-bold">Alur Persetujuan</div>
  <div class="card-body">
    <p class="text-muted small">Menunggu tindakan di <a href="{{ route('approval.inbox.index') }}">Kotak Persetujuan</a>.</p>
    @if($ar)
      <ol class="list-unstyled mb-0">
        @foreach($ar->steps as $s)
          <li class="d-flex mb-2">
            <span class="mr-3" style="width:20px">
              @if($s->status === 'approved')<i class="gd-check text-success"></i>
              @elseif($s->status === 'rejected')<i class="gd-close text-danger"></i>
              @elseif($s->status === 'skipped')<i class="gd-minus text-muted"></i>
              @else<i class="gd-time text-warning"></i>@endif
            </span>
            <div>
              <div class="font-weight-bold small">Step {{ $s->step_order }} — {{ $s->approver_label }}</div>
              <small class="text-muted">{{ $s->approver?->name ?? ($s->approver_type === 'specific_role' ? 'berbasis role' : '—') }}
                @if($s->acted_at) · {{ ucfirst($s->status) }} {{ $s->acted_at->format('d/m/Y H:i') }}@endif</small>
            </div>
          </li>
        @endforeach
      </ol>
    @endif
  </div>
</div>
@elseif($reimbursement->isApproved())
<div class="alert alert-success">
  Disetujui @if($reimbursement->approver) oleh <strong>{{ $reimbursement->approver->name }}</strong>@endif
  @if($reimbursement->approved_at) pada {{ $reimbursement->approved_at->format('d M Y, H:i') }}@endif
  @if($reimbursement->payment_period_label)
    <br>Dibayarkan pada periode gaji <strong>{{ $reimbursement->payment_period_label }}</strong>.
  @endif
</div>
@elseif($reimbursement->isRejected())
<div class="alert alert-danger">
  Ditolak
  @if($reimbursement->approved_at) pada {{ $reimbursement->approved_at->format('d M Y, H:i') }}@endif
  @if($reimbursement->rejection_reason)
    <br><strong>Alasan:</strong> {{ $reimbursement->rejection_reason }}
  @endif
</div>
@endif
@endsection

@if($reimbursement->isPending())
@push('modals')
@foreach($reimbursement->items as $item)
<div class="sipro-overlay" id="edit-item-{{ $item->id }}" role="dialog" aria-modal="true" aria-labelledby="edit-item-{{ $item->id }}-title">
  <div class="sipro-backdrop" onclick="closeSiproModal('edit-item-{{ $item->id }}')"></div>
  <div class="sipro-dialog" style="max-width:480px">
    <div class="sipro-header">
      <h5 id="edit-item-{{ $item->id }}-title" style="display:flex;align-items:center;gap:8px">
        <i class="gd-pencil"></i> Koreksi Rincian — {{ $item->patient_name }}
      </h5>
      <button class="sipro-close" onclick="closeSiproModal('edit-item-{{ $item->id }}')" aria-label="Tutup">&times;</button>
    </div>
    <form method="POST" action="{{ route('reimbursement.admin.items.update', [$reimbursement, $item]) }}">
      @csrf @method('PUT')
      <div class="sipro-body">
        <p class="text-muted mb-3" style="font-size:.85rem">
          {{ $item->institution }} &middot; {{ $item->treatment_date->format('d M Y') }}.
          Ubah nominal per kategori (mis. nolkan biaya obat yang tidak ditanggung), lalu simpan.
        </p>
        <div class="form-row">
          @foreach(\App\Models\Reimbursement\ReimbursementItem::AMOUNT_FIELDS as $field => $lbl)
          <div class="form-group col-6 mb-2">
            <label class="small mb-1">{{ $lbl }}</label>
            <input type="number" data-rupiah min="0" step="1" name="{{ $field }}"
                   class="form-control form-control-sm reimb-amt-{{ $item->id }}"
                   value="{{ $item->$field }}"
                   oninput="reimbItemRecalc({{ $item->id }})">
          </div>
          @endforeach
        </div>
        <div class="text-right font-weight-bold pt-2" style="border-top:1px solid #e9ecef">
          Total: Rp <span id="reimb-total-{{ $item->id }}">{{ number_format($item->total_claim, 0, ',', '.') }}</span>
        </div>
      </div>
      <div class="sipro-footer">
        <button type="button" class="btn btn-light btn-sm" onclick="closeSiproModal('edit-item-{{ $item->id }}')">Batal</button>
        <button type="submit" class="btn btn-warning btn-sm">
          <i class="gd-check mr-1"></i> Simpan Koreksi
        </button>
      </div>
    </form>
  </div>
</div>
@endforeach

<script>
function reimbItemRecalc(itemId) {
  var inputs = document.querySelectorAll('.reimb-amt-' + itemId);
  var total = 0;
  inputs.forEach(function(inp) { total += parseInt((inp.value || '0').replace(/\D/g, ''), 10) || 0; });
  var el = document.getElementById('reimb-total-' + itemId);
  if (el) el.textContent = total.toLocaleString('id-ID');
}
</script>
@endpush
@endif
