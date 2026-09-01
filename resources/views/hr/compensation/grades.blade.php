@extends('layouts.grain')
@section('title', 'Struktur Gaji Internal')

@section('content')
@include('components.notification')

<div class="h3 mb-0">Kompensasi</div>
@include('hr.compensation._nav', ['active' => 'grades'])

<div class="alert alert-info small mb-4">
  Band gaji <strong>internal</strong> per Level — dipakai HR mengontrol ruang kenaikan gaji
  (merit increase) tanpa harus promosi jabatan/level. Beda dari Benchmark Eksternal (acuan
  pasar). Pilih PT untuk isi band <strong>khusus PT itu</strong> (mis. beda budget per anak
  usaha), atau "Semua PT" untuk band global yang berlaku sebagai fallback.
</div>

<form method="GET" class="form-inline mb-3">
  <label class="mr-2 small font-weight-bold">PT</label>
  <select name="company_id" class="form-control form-control-sm" onchange="this.form.submit()">
    <option value="">— Semua PT (global/fallback) —</option>
    @foreach($companies as $c)
      <option value="{{ $c->id }}" @selected($companyId == $c->id)>{{ $c->name }}</option>
    @endforeach
  </select>
</form>

<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light">
          <tr>
            <th>Level</th>
            <th style="width:160px">Min (Rp)</th>
            <th style="width:160px">Tengah (Rp)</th>
            <th style="width:160px">Maks (Rp)</th>
            <th>Catatan</th>
            <th style="width:70px"></th>
          </tr>
        </thead>
        <tbody>
        @foreach($levels as $level)
          @php $f = 'grade-form-' . $level->id; $g = $existing->get($level->id); @endphp
          <tr>
            <td class="align-middle font-weight-bold">{{ $level->name }}</td>
            <td><input type="number" data-rupiah name="grade_min" form="{{ $f }}" value="{{ $g->grade_min ?? '' }}" class="form-control form-control-sm" min="0" required></td>
            <td><input type="number" data-rupiah name="grade_mid" form="{{ $f }}" value="{{ $g->grade_mid ?? '' }}" class="form-control form-control-sm" min="0" required></td>
            <td><input type="number" data-rupiah name="grade_max" form="{{ $f }}" value="{{ $g->grade_max ?? '' }}" class="form-control form-control-sm" min="0" required></td>
            <td><input type="text" name="notes" form="{{ $f }}" value="{{ $g->notes ?? '' }}" class="form-control form-control-sm"></td>
            <td class="text-right"><button type="submit" form="{{ $f }}" class="btn btn-xs btn-outline-primary">Simpan</button></td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @if($levels->isEmpty())
    <div class="card-body text-muted small">Belum ada data Level.</div>
  @endif
</div>

@foreach($levels as $level)
  <form id="grade-form-{{ $level->id }}" method="POST" action="{{ route('hr.compensation.grades.update', $level) }}" class="d-none">
    @csrf @method('PUT')
    <input type="hidden" name="company_id" value="{{ $companyId }}">
  </form>
@endforeach
@endsection
