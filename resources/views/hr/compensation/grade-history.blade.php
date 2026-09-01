@extends('layouts.grain')
@section('title', 'Riwayat Struktur Gaji — ' . $level->name)

@section('content')
@include('components.notification')

<div class="mb-3"><a href="{{ route('hr.compensation.grades', ['company_id' => $companyId]) }}" class="text-muted small"><i class="gd-angle-left"></i> Kembali</a></div>
<div class="h3 mb-3">Riwayat Struktur Gaji — {{ $level->name }}</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light"><tr><th>Tanggal</th><th class="text-right">Min</th><th class="text-right">Tengah</th><th class="text-right">Maks</th><th>Diubah Oleh</th></tr></thead>
        <tbody>
        @forelse($history as $h)
          <tr>
            <td class="small">{{ $h->created_at->format('d/m/Y H:i') }}</td>
            <td class="text-right">Rp {{ number_format($h->grade_min,0,',','.') }}</td>
            <td class="text-right">Rp {{ number_format($h->grade_mid,0,',','.') }}</td>
            <td class="text-right">Rp {{ number_format($h->grade_max,0,',','.') }}</td>
            <td class="small">{{ $h->changedBy?->name ?? '-' }}</td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-muted small p-3">Belum ada riwayat perubahan.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
