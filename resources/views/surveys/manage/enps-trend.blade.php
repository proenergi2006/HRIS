@extends('layouts.grain')
@section('title', 'Tren eNPS')

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('surveys.manage.index') }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>
<div class="h3 mb-3">Tren eNPS (Employee Net Promoter Score)</div>

<div class="alert alert-info small mb-4">
  Skor eNPS = %Promoter (skor 9-10) &minus; %Detraktor (skor 0-6), dari pertanyaan skala 0-10 di
  setiap survey bertipe eNPS yang sudah/pernah dibuka. Rentang -100 s.d. +100.
</div>

@if($points->isEmpty())
  <div class="card"><div class="card-body text-muted small">Belum ada survey eNPS yang dibuka. Buat survey baru dengan tipe "eNPS" di menu Kelola Survey.</div></div>
@else
<div class="card mb-4">
  <div class="card-header font-weight-bold">Grafik Tren</div>
  <div class="card-body">
    <div class="d-flex align-items-end" style="gap:12px;height:160px">
      @php $max = max(1, $points->max('score') ?? 1); $min = min(-1, $points->min('score') ?? -1); $range = $max - $min; @endphp
      @foreach($points as $p)
        @php $h = $range > 0 ? round(($p['score'] - $min) / $range * 130) : 65; @endphp
        <div style="flex:1;text-align:center">
          <div class="small font-weight-bold mb-1">{{ $p['score'] }}</div>
          <div style="background:{{ $p['score'] >= 0 ? '#0F2A4A' : '#c0392b' }};height:{{ max(4,$h) }}px;border-radius:3px 3px 0 0"></div>
          <div class="small text-muted mt-1" style="writing-mode:vertical-rl;text-orientation:mixed;max-height:70px">{{ \Illuminate\Support\Str::limit($p['label'], 24) }}</div>
        </div>
      @endforeach
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header font-weight-bold">Detail per Survey</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light"><tr><th>Survey</th><th>Mulai</th><th class="text-center">Responden</th><th class="text-center">Promoter</th><th class="text-center">Passive</th><th class="text-center">Detraktor</th><th class="text-center">Skor eNPS</th></tr></thead>
        <tbody>
        @foreach($points as $p)
          <tr>
            <td><a href="{{ route('surveys.manage.results', $p['survey']) }}">{{ $p['label'] }}</a></td>
            <td class="small">{{ $p['date']?->translatedFormat('d M Y') ?? '—' }}</td>
            <td class="text-center">{{ $p['total'] }}</td>
            <td class="text-center text-success">{{ $p['promoters'] }}</td>
            <td class="text-center text-warning">{{ $p['passives'] }}</td>
            <td class="text-center text-danger">{{ $p['detractors'] }}</td>
            <td class="text-center font-weight-bold" style="color:{{ $p['score'] >= 0 ? '#0F2A4A' : '#c0392b' }}">{{ $p['score'] }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif
@endsection
