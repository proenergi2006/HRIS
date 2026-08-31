@extends('layouts.grain')
@section('title', 'Penilaian Kompetensi')

@section('content')
@include('components.notification')

@php $L = \App\Models\Competency\Competency::$levelLabels; @endphp

<div class="h3 mb-0">Competency Framework</div>

<ul class="nav nav-pills my-4">
  <li class="nav-item"><a class="nav-link" href="{{ route('competency.dictionary.index') }}">Kamus Kompetensi</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('competency.positions.index') }}">Profil Jabatan</a></li>
  <li class="nav-item"><a class="nav-link active" href="{{ route('competency.assessments.index') }}">Penilaian</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('competency.gap') }}">Analisis Gap</a></li>
</ul>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Karyawan</label>
        <select name="employee_id" class="form-control form-control-sm" onchange="this.form.submit()">
          <option value="">-- Semua --</option>
          @foreach($employees as $e)<option value="{{ $e->id }}" @selected($employeeId == $e->id)>{{ $e->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Kompetensi</label>
        <select name="competency_id" class="form-control form-control-sm" onchange="this.form.submit()">
          <option value="">-- Semua --</option>
          @foreach($competencies as $c)<option value="{{ $c->id }}" @selected($competencyId == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Departemen</label>
        <select name="department_id" class="form-control form-control-sm" onchange="this.form.submit()">
          <option value="">-- Semua --</option>
          @foreach($departments as $d)<option value="{{ $d->id }}" @selected($departmentId == $d->id)>{{ $d->name }}</option>@endforeach
        </select>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span class="font-weight-bold">Hasil Penilaian</span>
    @if($employeeId)
      <a href="{{ route('competency.assessments.employee', $employeeId) }}" class="btn btn-sm btn-primary">Nilai Karyawan Ini</a>
    @endif
  </div>
  <div class="card-body p-0">
    @if($assessments->isEmpty())
      <p class="text-center text-muted py-4">Belum ada penilaian kompetensi.</p>
    @else
    <table class="table table-sm mb-0">
      <thead class="thead-light"><tr><th>Karyawan</th><th>Departemen</th><th>Kompetensi</th><th>Level</th><th>Tgl Nilai</th><th>Penilai</th><th style="width:80px"></th></tr></thead>
      <tbody>
        @foreach($assessments as $a)
        <tr>
          <td>{{ $a->employee?->name }}</td>
          <td>{{ $a->employee?->department?->name ?? '-' }}</td>
          <td>{{ $a->competency?->name }}</td>
          <td><span class="badge badge-{{ \App\Models\Competency\Competency::$levelBadges[$a->actual_level] ?? 'secondary' }}">{{ $a->actual_level }} — {{ $L[$a->actual_level] ?? '?' }}</span></td>
          <td class="small">{{ $a->assessed_on?->format('d/m/Y') ?? '-' }}</td>
          <td class="small">{{ $a->assessor?->name ?? '-' }}</td>
          <td><a href="{{ route('competency.assessments.employee', $a->employee_id) }}" class="btn btn-xs btn-outline-primary"><i class="gd-pencil"></i></a></td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif
  </div>
</div>
@endsection
