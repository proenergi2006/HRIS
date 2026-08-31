@extends('layouts.grain')
@section('title', 'Slip Gaji Saya')

@section('content')
@include('components.notification')

<div class="h3 mb-4">Slip Gaji Saya</div>

<div class="card">
  <div class="card-body">
    @if($slips->isEmpty())
      <p class="text-muted small mb-0">Belum ada slip gaji yang final.</p>
    @else
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="thead-light"><tr><th>Periode</th><th class="text-right">Gross</th><th class="text-right">Potongan</th><th class="text-right">Net</th><th></th></tr></thead>
          <tbody>
          @foreach($slips as $s)
            <tr>
              <td>{{ $s->period?->period_label }}</td>
              <td class="text-right">Rp {{ number_format($s->gross_salary, 0, ',', '.') }}</td>
              <td class="text-right">Rp {{ number_format($s->total_deductions, 0, ',', '.') }}</td>
              <td class="text-right font-weight-bold">Rp {{ number_format($s->net_salary, 0, ',', '.') }}</td>
              <td><a href="{{ route('payroll.my.pdf', $s) }}" class="btn btn-xs btn-outline-info"><i class="gd-download mr-1"></i>PDF</a></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>

@if($taxYears->isNotEmpty())
<div class="card mt-3">
  <div class="card-header py-2"><strong class="small">Bukti Potong PPh21 Tahunan</strong></div>
  <div class="card-body py-2">
    @foreach($taxYears as $y)
      <a href="{{ route('payroll.my.bukti-potong-pdf', $y) }}" class="btn btn-xs btn-outline-secondary mr-1 mb-1">
        <i class="gd-download mr-1"></i> {{ $y }}
      </a>
    @endforeach
  </div>
</div>
@endif
@endsection
