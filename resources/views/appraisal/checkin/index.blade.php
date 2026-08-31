@extends('layouts.grain')
@section('title', '1-on-1 / Continuous Feedback')

@section('content')
@include('components.notification')

<div class="h3 mb-1">1-on-1 / Continuous Feedback</div>
<p class="text-muted">Catatan ngobrol rutin di luar siklus penilaian kinerja formal.
  {{ $isHr ? 'Menampilkan semua karyawan.' : 'Menampilkan bawahan langsung Anda.' }}</p>

<div class="card">
  <div class="card-body p-0">
    @if($employees->isEmpty())
      <p class="text-muted small p-3 mb-0">{{ $isHr ? 'Belum ada karyawan.' : 'Anda belum punya bawahan langsung di sistem.' }}</p>
    @else
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light"><tr><th class="pl-3">Karyawan</th><th>Jabatan</th><th>1-on-1 Terakhir</th><th></th></tr></thead>
        <tbody>
        @foreach($employees as $e)
          <tr>
            <td class="pl-3">{{ $e->name }}</td>
            <td class="text-muted small">{{ $e->position?->name ?? '—' }}</td>
            <td class="small">
              @if($e->last_checkin)
                {{ $e->last_checkin->checkin_date->format('d/m/Y') }}
              @else
                <span class="text-muted">Belum pernah</span>
              @endif
            </td>
            <td class="pr-3 text-right"><a href="{{ route('appraisal.checkins.show', $e) }}" class="btn btn-xs btn-outline-primary">Buka</a></td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
</div>
@endsection
