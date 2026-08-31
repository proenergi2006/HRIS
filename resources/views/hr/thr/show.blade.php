@extends('layouts.grain')
@section('title', 'Detail THR')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('hr.thr.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>

<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
  <div>
    <div class="h3 mb-0">{{ $period->holiday_name }} {{ $period->year }}</div>
    <div class="text-muted small">{{ $period->company?->name }} — Bayar {{ $period->payment_date?->format('d/m/Y') }}</div>
  </div>
  <div class="d-flex" style="gap:.5rem">
    @if(!$period->isClosed())
      <form method="POST" action="{{ route('hr.thr.generate', $period) }}" onsubmit="return confirm('Hitung ulang THR semua karyawan aktif di perusahaan ini?')">
        @csrf
        <button class="btn btn-outline-primary btn-sm"><i class="gd-reload mr-1"></i> Hitung / Hitung Ulang</button>
      </form>
      <form method="POST" action="{{ route('hr.thr.close', $period) }}" onsubmit="return confirm('Tutup periode THR ini? Tidak bisa dihitung ulang setelah ditutup.')">
        @csrf
        <button class="btn btn-success btn-sm"><i class="gd-check mr-1"></i> Tutup Periode</button>
      </form>
    @else
      <span class="badge badge-success align-self-center">Ditutup {{ $period->closed_at?->format('d/m/Y H:i') }} oleh {{ $period->closedBy?->name }}</span>
    @endif
  </div>
</div>

<div class="card">
  <div class="card-body">
    @if($payments->isEmpty())
      <p class="text-muted small mb-0">Belum dihitung. Klik "Hitung / Hitung Ulang".</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light">
            <tr><th>Karyawan</th><th class="text-right">Gaji Pokok+Tunj. Jabatan</th><th class="text-center">Masa Kerja</th><th class="text-center">Rasio</th><th class="text-right">THR</th><th></th></tr>
          </thead>
          <tbody>
          @foreach($employees as $emp)
            @php $pay = $payments->get($emp->id); @endphp
            <tr>
              <td>{{ $emp->name }}</td>
              @if($pay)
                <td class="text-right">Rp {{ number_format($pay->base_salary, 0, ',', '.') }}</td>
                <td class="text-center">{{ $pay->months_worked }} bln</td>
                <td class="text-center">{{ number_format($pay->proration_ratio * 100, 1) }}%</td>
                <td class="text-right font-weight-bold">Rp {{ number_format($pay->thr_amount, 0, ',', '.') }}</td>
                <td><a href="{{ route('hr.thr.pdf', [$period, $pay]) }}" class="btn btn-xs btn-outline-info"><i class="gd-download"></i></a></td>
              @else
                <td colspan="5" class="text-muted small">Belum dihitung / masa kerja &lt;1 bulan</td>
              @endif
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
