@extends('layouts.grain')
@section('title', 'Detail Bonus / Insentif')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('hr.bonus.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div>
    <div class="h3 mb-0">{{ $period->name }}</div>
    <div class="text-muted small">
      {{ $period->company?->name }} — {{ $period->type_label }} — Bayar {{ $period->payment_date?->format('d/m/Y') }}
      — Pajak: {{ $period->is_taxable ? 'Ya' : 'Tidak' }}
    </div>
  </div>
  <div class="d-flex" style="gap:.5rem">
    @if(!$period->isClosed())
      <form method="POST" action="{{ route('hr.bonus.generate', $period) }}">
        @csrf
        <button class="btn btn-outline-primary btn-sm"><i class="gd-reload mr-1"></i> Siapkan Daftar Karyawan</button>
      </form>
      <form method="POST" action="{{ route('hr.bonus.close', $period) }}" onsubmit="return confirm('Tutup periode ini? Tidak bisa diubah setelah ditutup.')">
        @csrf
        <button class="btn btn-success btn-sm"><i class="gd-check mr-1"></i> Tutup</button>
      </form>
    @else
      <span class="badge badge-success align-self-center">Ditutup {{ $period->closed_at?->format('d/m/Y H:i') }}</span>
    @endif
  </div>
</div>

<form method="POST" action="{{ route('hr.bonus.amounts', $period) }}">
  @csrf
  <div class="card">
    <div class="card-body">
      @if($employees->isEmpty())
        <p class="text-muted small mb-0">Tidak ada karyawan aktif.</p>
      @else
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead class="thead-light"><tr><th>Karyawan</th><th class="text-right" style="width:200px">Bruto (Rp)</th><th class="text-right">PPh21</th><th class="text-right">Netto</th><th></th></tr></thead>
            <tbody>
            @foreach($employees as $emp)
              @php $pay = $payments->get($emp->id); @endphp
              <tr>
                <td>{{ $emp->name }}<div class="small text-muted">{{ $emp->nip }}</div></td>
                <td class="text-right">
                  <input type="number" data-rupiah min="0" name="amounts[{{ $emp->id }}]" class="form-control form-control-sm text-right"
                         value="{{ $pay->gross_amount ?? 0 }}" {{ $period->isClosed() ? 'disabled' : '' }}>
                </td>
                <td class="text-right text-danger">{{ $pay ? 'Rp ' . number_format($pay->tax_amount, 0, ',', '.') : '—' }}</td>
                <td class="text-right font-weight-bold">{{ $pay ? 'Rp ' . number_format($pay->net_amount, 0, ',', '.') : '—' }}</td>
                <td>@if($pay && $pay->gross_amount > 0)<a href="{{ route('hr.bonus.pdf', [$period, $pay]) }}" class="btn btn-xs btn-outline-info"><i class="gd-download"></i></a>@endif</td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
        @unless($period->isClosed())
          <div class="text-right mt-3"><button class="btn btn-primary btn-sm">Simpan Nominal &amp; Hitung Pajak</button></div>
        @endunless
      @endif
    </div>
  </div>
</form>
@endsection
