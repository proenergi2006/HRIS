@extends('layouts.grain')
@section('title', 'Talent Pool — ' . $position->name)

@section('content')
@include('components.notification')

<div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap:.5rem">
  <div>
    <div class="h3 mb-0">{{ $position->name }}</div>
    <div class="text-muted small">
      {{ $position->company?->name }} — {{ $position->department?->name ?? 'Tanpa Departemen' }}
      @if($position->is_critical_position)
        <span class="badge badge-{{ \App\Models\Position::$successionRiskBadges[$position->succession_risk] ?? 'secondary' }} ml-2">
          Jabatan Kritikal @if($position->succession_risk) — Risiko {{ \App\Models\Position::$successionRiskLabels[$position->succession_risk] }} @endif
        </span>
      @endif
    </div>
  </div>
  <a href="{{ route('succession.positions') }}" class="btn btn-sm btn-outline-secondary">&larr; Kembali</a>
</div>

@if($position->succession_notes)
<div class="alert alert-secondary small">{{ $position->succession_notes }}</div>
@endif

<div class="card mb-4">
  <div class="card-header font-weight-bold">Tambah Kandidat Pengganti</div>
  <div class="card-body">
    <form method="POST" action="{{ route('succession.pool.store', $position) }}" class="form-row align-items-end">
      @csrf
      <div class="form-group col-md-5 mb-2">
        <label class="small">Karyawan</label>
        <select name="employee_id" class="form-control form-control-sm" required>
          <option value="">— pilih —</option>
          @foreach($candidateEmployees as $e)
            <option value="{{ $e->id }}">{{ $e->name }} — {{ $e->position?->name ?? 'Tanpa Jabatan' }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-3 mb-2">
        <label class="small">Kesiapan</label>
        <select name="readiness" class="form-control form-control-sm" required>
          @foreach(\App\Models\HR\TalentPoolMember::$readinessLabels as $k => $lbl)
            <option value="{{ $k }}">{{ $lbl }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group col-md-3 mb-2">
        <label class="small">Catatan Pengembangan</label>
        <input type="text" name="development_notes" class="form-control form-control-sm">
      </div>
      <div class="form-group col-md-1 mb-2">
        <button type="submit" class="btn btn-sm btn-primary btn-block">+</button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header font-weight-bold">Talent Pool ({{ $pool->count() }})</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light"><tr><th>Nama</th><th>Jabatan Saat Ini</th><th style="width:180px">Kesiapan</th><th>Catatan Pengembangan</th><th style="width:80px"></th></tr></thead>
        <tbody>
        @forelse($pool as $m)
          @php $f = 'pool-form-' . $m->id; @endphp
          <tr>
            <td class="font-weight-bold">{{ $m->employee->name }}</td>
            <td class="small text-muted">{{ $m->employee->position?->name ?? '—' }}</td>
            <td>
              <select name="readiness" form="{{ $f }}" class="form-control form-control-sm">
                @foreach(\App\Models\HR\TalentPoolMember::$readinessLabels as $k => $lbl)
                  <option value="{{ $k }}" @selected($m->readiness === $k)>{{ $lbl }}</option>
                @endforeach
              </select>
            </td>
            <td><input type="text" name="development_notes" form="{{ $f }}" value="{{ $m->development_notes }}" class="form-control form-control-sm"></td>
            <td class="text-right" style="white-space:nowrap">
              <button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-primary"><i class="gd-pencil"></i></button>
              <a href="#" class="btn btn-xs btn-outline-danger" data-confirm="Hapus {{ $m->employee->name }} dari talent pool jabatan ini?" data-confirm-title="Hapus Kandidat" data-form="pool-delete-{{ $m->id }}"><i class="gd-trash"></i></a>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-muted small p-3">Belum ada kandidat pengganti.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@foreach($pool as $m)
  <form id="pool-form-{{ $m->id }}" method="POST" action="{{ route('succession.pool.update', $m) }}" class="d-none">@csrf @method('PUT')</form>
  <form id="pool-delete-{{ $m->id }}" method="POST" action="{{ route('succession.pool.destroy', $m) }}" class="d-none">@csrf @method('DELETE')</form>
@endforeach
@endsection
