@extends('layouts.grain')
@section('title', 'Detail Kasbon / Pinjaman')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('hr.loans.index', ['company_id' => $loan->company_id]) }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.5rem">
  <div class="h3 mb-3">{{ $loan->type_label }} — {{ $loan->employee?->name }}</div>
  <span class="badge badge-{{ \App\Models\HR\EmployeeLoan::$statusBadges[$loan->status] ?? 'secondary' }}">{{ $loan->status_label }}</span>
</div>

<div class="row">
  <div class="col-md-5">
    <div class="card mb-3">
      <div class="card-body">
        <dl class="row mb-0 small">
          <dt class="col-5">Karyawan</dt><dd class="col-7">{{ $loan->employee?->name }} ({{ $loan->employee?->nip }})</dd>
          <dt class="col-5">Perusahaan</dt><dd class="col-7">{{ $loan->employee?->company?->short_name ?? $loan->employee?->company?->name }}</dd>
          <dt class="col-5">No. Referensi</dt><dd class="col-7">{{ $loan->reference_no ?: '—' }}</dd>
          <dt class="col-5">Pokok</dt><dd class="col-7">Rp {{ number_format($loan->principal, 0, ',', '.') }}</dd>
          <dt class="col-5">Cicilan / bulan</dt><dd class="col-7">Rp {{ number_format($loan->installment_amount, 0, ',', '.') }} × {{ $loan->installment_count }}</dd>
          <dt class="col-5">Sisa</dt><dd class="col-7">Rp {{ number_format($loan->outstanding(), 0, ',', '.') }}</dd>
          <dt class="col-5">Mulai potong</dt><dd class="col-7">{{ str_pad($loan->start_month, 2, '0', STR_PAD_LEFT) }}/{{ $loan->start_year }}</dd>
          <dt class="col-5">Dicatat oleh</dt><dd class="col-7">{{ $loan->createdBy?->name ?? '—' }}</dd>
          @if($loan->notes)<dt class="col-5">Catatan</dt><dd class="col-7">{{ $loan->notes }}</dd>@endif
        </dl>
        @if($loan->status === 'active')
          <form method="POST" action="{{ route('hr.loans.cancel', $loan) }}" class="mt-3" onsubmit="return confirm('Batalkan kasbon/pinjaman ini? Cicilan yang belum terpotong akan dihapus.')">
            @csrf
            <button class="btn btn-sm btn-outline-danger">Batalkan</button>
          </form>
        @endif
      </div>
    </div>
  </div>

  <div class="col-md-7">
    <div class="card">
      <div class="card-header py-2"><strong class="small">Jadwal Cicilan</strong></div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead class="thead-light"><tr><th>Periode</th><th class="text-right">Jumlah</th><th>Status</th><th>Slip</th><th></th></tr></thead>
          <tbody>
          @foreach($loan->installments->sortBy(['period_year', 'period_month']) as $inst)
            <tr>
              <td>{{ str_pad($inst->period_month, 2, '0', STR_PAD_LEFT) }}/{{ $inst->period_year }}</td>
              <td class="text-right">Rp {{ number_format($inst->amount, 0, ',', '.') }}</td>
              <td>
                @if($inst->status === 'deducted')<span class="badge badge-success">Terpotong</span>
                @elseif($inst->status === 'waived')<span class="badge badge-secondary">Di-waive</span>
                @else<span class="badge badge-warning">Menunggu</span>@endif
              </td>
              <td class="small">
                @if($inst->slip && $inst->slip->period)
                  {{ str_pad($inst->slip->period->month, 2, '0', STR_PAD_LEFT) }}/{{ $inst->slip->period->year }}
                @else — @endif
              </td>
              <td>
                @if($inst->status === 'pending' && $loan->status === 'active')
                  <form method="POST" action="{{ route('hr.loans.installments.waive', [$loan, $inst]) }}" onsubmit="return confirm('Tandai cicilan ini sebagai di-waive (tidak ditagih)?')">
                    @csrf
                    <button class="btn btn-xs btn-outline-secondary">Waive</button>
                  </form>
                @endif
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
