@extends('layouts.grain')
@section('title', 'Profil Kompetensi Jabatan')

@section('content')
@include('components.notification')

<div class="h3 mb-0">Competency Framework</div>

<ul class="nav nav-pills my-4">
  <li class="nav-item"><a class="nav-link" href="{{ route('competency.dictionary.index') }}">Kamus Kompetensi</a></li>
  <li class="nav-item"><a class="nav-link active" href="{{ route('competency.positions.index') }}">Profil Jabatan</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('competency.assessments.index') }}">Penilaian</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('competency.gap') }}">Analisis Gap</a></li>
</ul>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Perusahaan</label>
        <select name="company_id" class="form-control form-control-sm" onchange="this.form.submit()">
          <option value="">-- Semua --</option>
          @foreach($companies as $c)<option value="{{ $c->id }}" @selected($companyId == $c->id)>{{ $c->name }}</option>@endforeach
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
  <div class="card-header font-weight-bold">Jabatan</div>
  <div class="card-body p-0">
    @if($positions->isEmpty())
      <p class="text-center text-muted py-4">Tidak ada jabatan pada filter ini.</p>
    @else
    <table class="table table-sm mb-0">
      <thead class="thead-light">
        <tr><th>Jabatan</th><th>Departemen</th><th>Level</th><th class="text-center">Jml Kompetensi</th><th style="width:120px"></th></tr>
      </thead>
      <tbody>
        @foreach($positions as $p)
        <tr>
          <td class="font-weight-bold">{{ $p->name }}</td>
          <td>{{ $p->department?->name ?? '-' }}</td>
          <td>{{ $p->level?->name ?? '-' }}</td>
          <td class="text-center">
            <span class="badge badge-{{ $p->competency_requirements_count ? 'info' : 'secondary' }}">{{ $p->competency_requirements_count }}</span>
          </td>
          <td><a href="{{ route('competency.positions.show', $p) }}" class="btn btn-xs btn-outline-primary">Atur Kompetensi</a></td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif
  </div>
</div>
@endsection
