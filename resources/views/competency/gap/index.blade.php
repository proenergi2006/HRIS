@extends('layouts.grain')
@section('title', 'Analisis Gap Kompetensi')

@section('content')
@include('components.notification')

<div class="h3 mb-0">Competency Framework</div>

<ul class="nav nav-pills my-4">
  <li class="nav-item"><a class="nav-link" href="{{ route('competency.dictionary.index') }}">Kamus Kompetensi</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('competency.positions.index') }}">Profil Jabatan</a></li>
  <li class="nav-item"><a class="nav-link" href="{{ route('competency.assessments.index') }}">Penilaian</a></li>
  <li class="nav-item"><a class="nav-link active" href="{{ route('competency.gap') }}">Analisis Gap</a></li>
</ul>

<div class="card mb-3">
  <div class="card-header font-weight-bold">Filter</div>
  <div class="card-body py-3">
    <form method="GET" class="form-row align-items-end mb-0">
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Perusahaan</label>
        <select name="company_id" class="form-control form-control-sm">
          @foreach($companies as $c)<option value="{{ $c->id }}" @selected($companyId == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-group col-md-3 mb-0">
        <label class="small font-weight-bold">Departemen</label>
        <select name="department_id" class="form-control form-control-sm">
          <option value="">-- Semua --</option>
          @foreach($departments as $d)<option value="{{ $d->id }}" @selected($departmentId == $d->id)>{{ $d->name }}</option>@endforeach
        </select>
      </div>
      <div class="form-group col-md-3 mb-0">
        <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
        <a href="{{ route('competency.gap.pdf', ['company_id' => $companyId, 'department_id' => $departmentId]) }}"
           class="btn btn-outline-danger btn-sm ml-1"><i class="gd-file mr-1"></i> Download PDF</a>
      </div>
    </form>
  </div>
</div>

<div class="row mb-4">
  <div class="col-md-3 mb-3">
    <div class="card h-100" style="border-left:4px solid #0F2A4A"><div class="card-body py-3">
      <div class="small text-muted font-weight-bold mb-1">Karyawan Dianalisis</div>
      <div class="h4 mb-0 font-weight-bold">{{ $data['employees_total'] }}</div>
    </div></div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="card h-100" style="border-left:4px solid #dc2626"><div class="card-body py-3">
      <div class="small text-muted font-weight-bold mb-1">Karyawan dengan Gap</div>
      <div class="h4 mb-0 font-weight-bold">{{ $data['employees_w_gap'] }}</div>
    </div></div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="card h-100" style="border-left:4px solid #d97706"><div class="card-body py-3">
      <div class="small text-muted font-weight-bold mb-1">Total Gap Kompetensi</div>
      <div class="h4 mb-0 font-weight-bold">{{ $data['gap_count'] }}</div>
    </div></div>
  </div>
</div>

@if(!empty($data['per_competency']))
<div class="card mb-4">
  <div class="card-header font-weight-bold">Kompetensi Paling Sering Gap</div>
  <div class="card-body p-0">
    <table class="table table-sm mb-0">
      <thead class="thead-light"><tr><th>Kompetensi</th><th class="text-center">Jml Karyawan Gap</th></tr></thead>
      <tbody>
        @foreach($data['per_competency'] as $name => $count)
        <tr><td>{{ $name }}</td><td class="text-center">{{ $count }}</td></tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

<div class="card mb-4">
  <div class="card-header font-weight-bold">Detail Gap per Karyawan</div>
  <div class="card-body p-0">
    @if(empty($data['rows']))
      <p class="text-center text-muted py-4">Tidak ada jabatan dengan profil kompetensi pada filter ini. Isi dulu di menu Profil Jabatan.</p>
    @else
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light">
          <tr><th>Karyawan</th><th>Departemen</th><th>Kompetensi</th><th class="text-center">Wajib</th><th class="text-center">Aktual</th><th class="text-center">Gap</th><th>Rekomendasi Training</th></tr>
        </thead>
        <tbody>
          @foreach($data['rows'] as $r)
          <tr class="{{ $r['gap'] > 0 ? 'table-warning' : '' }}">
            <td>{{ $r['employee'] }}</td>
            <td class="small">{{ $r['department'] }}</td>
            <td>{{ $r['competency'] }}</td>
            <td class="text-center">{{ $r['required'] }}</td>
            <td class="text-center">{{ $r['actual'] ?? '—' }}</td>
            <td class="text-center font-weight-bold {{ $r['gap'] > 0 ? 'text-danger' : 'text-success' }}">{{ $r['gap'] }}</td>
            <td class="small">
              @if($r['gap'] > 0 && !empty($r['training']))
                {{ implode(', ', array_slice($r['training'], 0, 3)) }}
                <a href="{{ route('training.participants.index') }}" class="text-muted">(atur)</a>
              @elseif($r['gap'] > 0)
                <span class="text-muted">Belum ada program training kategori "{{ $r['category'] ?? '-' }}"</span>
              @else
                <span class="text-success">Terpenuhi</span>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
</div>
@endsection
